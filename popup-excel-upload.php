<?php
include("include/connect.php");
include("class/crud.php");

//VARIABLES
$errorFlag = false;
$errorMessage = "";

$crudapp = new crudClass();
$uniqid = uniqid(); 

$ref_no = @$_GET['reference_no'];
$version = @$_GET['version'];
$year = @$_GET['year'];
$user = $_GET['login'];
$cost_center = @$_GET['cost_center'];

$login = $user;
$updateSession = $crudapp->updateSession($conn,$user);

$ORG_CODE = @$_GET['ORG_CODE'];
$MRC_CODE = @$_GET['MRC_CODE'];
$reference_no = $ref_no;
$success = isset($_GET['success']) ? $_GET['success'] : false;
$error = isset($_GET['error']) ? $_GET['error'] : false;
$url = $_SERVER['PHP_SELF'] . "?login=" . $login . "&year=" . $year . "&version=" . $version;
$save_file_path = "";	

//Item
if (isset($_FILES["item-based-file"])){ 
	if ( sqlsrv_begin_transaction( $conn ) === false ) {
		die( print_r( sqlsrv_errors(), true ));
	}

	$random_str = substr(md5(mt_rand()), 0, 7);
	$filepath_tmp = $_FILES["item-based-file"]["tmp_name"];
	$name = $_FILES["item-based-file"]["name"];
	//$ext = end((explode(".", $name)));
	$new_name = $random_str . $name;
	$filepath = "upload/" . $new_name;
	
	if(move_uploaded_file($filepath_tmp,$filepath)){

		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);

		require_once '/library/PHPExcel/PHPExcel.php';
		require_once '/library/PHPExcel/PHPExcel/IOFactory.php';

		try
		{
			$excel2 = PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $excel2->load($filepath); 
			//unlink($filepath);
			$objPHPExcel->setActiveSheetIndex(0);
			$worksheet = $objPHPExcel->getActiveSheet();
			$budget_data = array();
			$row_number = 0;
			$record_items = array();
			$item_codes = array();
			$last_val = "";
			$all_data_error_flag = false;
			$errorMessage = array();
			foreach ($worksheet->getRowIterator() as $key => $_rows)
			{
				$rows = $_rows->getCellIterator();
				$rows->setIterateOnlyExistingCells(FALSE); // This loops through all cells,

				$tmp = array();
				$row_data = array();
				if($key > 1){
					$errorFlag = false;
					$has_data = false;
					$tmp_errorMessage = "";
					foreach($rows as $col => $row){
						$_value = trim($row->getCalculatedValue());
						if( $_value != ""){
							$has_data= true;
						}
						
						if ($col == "A" ) {

							if(in_array( $_value, $item_codes )){
								$tmp_errorMessage .= 'Duplicate item code in this excel file. ';
								$errorFlag = true;
							}

							if($_value == "" || !isValidItemCode($conn,$crudapp,$_value)){
								$tmp_errorMessage .= 'Item code not found. Please check Materials -> Items for more details.';
								$errorFlag = true;
							} else if (checkIfItemExist($conn, $crudapp, $_value, $ref_no, $version)) { 
								$tmp_errorMessage .= 'Item already exist for this budget year. ';
								$errorFlag = true;
							} else {
								array_push($item_codes, $_value);
							}
						} 

						if ($col == "B" ) {
							$rate = "";
							$CUR_CODE = strtoupper(trim($_value));
							$rate = "";

							if($CUR_CODE == "" ){
								$tmp_errorMessage .= 'Empty currency code. ';
								$errorFlag = true;
							} else {
								if($CUR_CODE != "PHP" ){
									$today = date("m/d/Y H:i");	
									$rate = $crudapp->checkRate($conn,"R5EXCHRATES","CRR_CURR = '$CUR_CODE' AND '$today' between CRR_START and CRR_END ORDER BY CRR_END DESC");
									
									if ($rate == "none") { 
										$tmp_errorMessage .= 'Please update exchange rate for this currency code. ';
										$errorFlag = true;
									} 
								}
							}		
						} 

						$row_data[] = $_value;
					}

					if($has_data){
						$tmp["code"] = $row_data[0];
						$tmp["CUR_CODE"] = $row_data[1];
						$tmp["january"] = $row_data[2];
						$tmp["february"] = $row_data[3];
						$tmp["march"] = $row_data[4];
						$tmp["april"] = $row_data[5];
						$tmp["may"] = $row_data[6];
						$tmp["june"] = $row_data[7];
						$tmp["july"] = $row_data[8];
						$tmp["august"] = $row_data[9];
						$tmp["september"] = $row_data[10];
						$tmp["october"] = $row_data[11];
						$tmp["november"] = $row_data[12];
						$tmp["december"] = $row_data[13];

						array_push($record_items, $tmp);

						if($errorFlag){
							$errorMessage[$key] =  $tmp_errorMessage ;
							$all_data_error_flag = true;
						}

					} else{
						break;
					}
				}
			}

			if($all_data_error_flag){
				//$objPHPExcel = new PHPExcel("");
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(100);

				$objPHPExcel->getActiveSheet()->getStyle('O1')->getFill()
				->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()->setARGB('cc3300');
				$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->getColor()->setRGB('FFFFFF');

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14,  1, "Remarks");
				foreach ($errorMessage as $indx => $msg){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $indx, $msg);
				}
			
				// // Redirect output to a clients web browser (Excel2007)
				// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				// header('Content-Disposition: attachment;filename="import_fail_' . $name . '"');
				// header('Cache-Control: max-age=0');
				// // If you're serving to IE 9, then the following may be needed
				// header('Cache-Control: max-age=1');

				// // If you're serving to IE over SSL, then the following may be needed
				// header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				// header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				// header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				// header ('Pragma: public'); // HTTP/1.0

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				ob_end_clean();
				$milliseconds = round(microtime(true) * 1000);
				$save_file_path = 'upload/import_fail_' . $milliseconds . "_" . $name;
				$objWriter->save($save_file_path);
				$error = true;
				sqlsrv_rollback( $conn );
			} else {
				// $ref_no = $_POST['ref_no'];
				// $ORG_CODE = $_POST['ORG_CODE'];
				// $MRC_CODE = $_POST['MRC_CODE'];
				// $quantity = 0;
				$data = array();
				$data2 = array();
				$data3 = array();
				$data4 = array();


				//ITEMBASE
				$column = $crudapp->readColumn($conn,"R5_VIEW_ITEMBASE_LINES");
				$record_id = $crudapp->readID($conn,"R5_EAM_DPP_ITEMBASE_LINES");
				$record_id = $record_id + 1;

				foreach($record_items as $ri) {
					
					// $CUR_CODE_VAL = $ri['CUR_CODE_VAL'];
					$code = $ri['code'];
					$CUR_CODE = $ri['CUR_CODE'];
					$january = $ri['january'];
					$february = $ri['february'];
					$march =  $ri['march'];
					$april =  $ri['april'];
					$may =  $ri['may'];
					$june = $ri['june'];
					$july = $ri['july'];
					$august = $ri['august'];
					$september = $ri['september'];
					$october = $ri['october'];
					$november = $ri['november'];
					$december = $ri['december'];	
					
					
					$item_based_data = $ri;

					$unit_cost = 1;
					$rate = 1;
					$foreign_cost = 0;
					$available = 0.00;

					if($CUR_CODE != "PHP"){
						$today = date("m/d/Y H:i");	
						$rate = $crudapp->checkRate($conn,"R5EXCHRATES","CRR_CURR = '$CUR_CODE' AND '$today' between CRR_START and CRR_END ORDER BY CRR_END DESC");
					}

					$q1_total_cost = ($january + $february + $march);
					$q2_total_cost = ($april + $may + $june);
					$q3_total_cost = ($july + $august + $september);
					$q4_total_cost = ($october + $november + $december);

					$quantity = $q1_total_cost + $q2_total_cost + $q3_total_cost + $q4_total_cost;

					if ($CUR_CODE != "PHP" && $rate != "none"){
						$foreign_cost = $unit_cost;
						$available = $quantity * ($unit_cost / $rate);
						$unit_cost = $unit_cost / $rate;
					} else {
						$available = $quantity * $unit_cost;
						$foreign_cost = $unit_cost;
					}

					$q1_total_cost = ($january + $february + $march) * $unit_cost;
					$q2_total_cost = ($april + $may + $june) * $unit_cost;
					$q3_total_cost = ($july + $august + $september) * $unit_cost;
					$q4_total_cost = ($october + $november + $december) * $unit_cost;

					$CUR_CODE_VAL = $CUR_CODE; 
					
					$today = date("m/d/Y H:i");	

					$_data = array("record_id"=>$record_id,"id"=>$record_id,"code"=>$code,"quantity"=>$quantity,"available"=>$available,"total_cost"=>$available,"unit_cost"=>$unit_cost,"saveFlag"=>1,"version"=>1,"foreign_curr"=>$CUR_CODE_VAL,"foreign_cost"=>$foreign_cost,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);	
				
					$_data2 = array("id"=>$record_id,"january"=>$january,"february"=>$february,
					"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
					"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
					
					$_data3 = array("reference_no"=>$reference_no,"rowid"=>$record_id,"version"=>$version);	
					$_data4 = array("id"=>$record_id, "createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
					
					for($q = 1; $q <= 4; $q++) {
						$_data4["q" . $q . "_total_cost"] = ${"q".$q."_total_cost"};
						$_data4["q" . $q . "_adjustments"] = 0;
						$_data4["q" . $q . "_available"] =  ${"q".$q."_total_cost"};
						$_data4["q" . $q . "_reserved"] = 0;
						$_data4["q" . $q . "_allocated"] = 0;
						$_data4["q" . $q . "_paid"] = 0;
					}

					array_push($data, $_data);
					array_push($data2, $_data2);
					array_push($data3, $_data3);
					array_push($data4, $_data4);
					$record_id++;
				}

			   
				$table = "R5_EAM_DPP_ITEMBASE_LINES";
				$table2 = "R5_REF_ITEMBASE_BUDGET_MONTH";
				$table3 = "R5_EAM_DPP_ITEMBASE_BRIDGE";
				$table4 = "R5_REF_ITEMBASE_BUDGET_QUARTERLY";

				$result  = $crudapp->insertRecordBatch($conn,$data,$table);
				$result2 = $crudapp->insertRecordBatch($conn,$data2,$table2);
				$result3 = $crudapp->insertRecordBatch($conn,$data3,$table3);
				$result4 = $crudapp->insertRecordBatch($conn,$data4,$table4);

				if( $result == 1 && $result2 == 1 && $result3 == 1 && $result4 == 1) {
					//sqlsrv_commit( $conn );
					$condition = " ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE' AND year_budget = '$year' AND cost_center = '$cost_center' ";
					$dppcolumn = $crudapp->readColumn($conn,"R5_DPP_VERSION");
					$endorsementCtr = $crudapp->listTable($conn,"R5_DPP_VERSION", $dppcolumn,$condition);
					$result_update_status = false;
					
					if (isset($endorsementCtr[0]['id']) ) {
						$result_update_status = $crudapp->updateRecord($conn, array('status' => 'For Endorsement'), "R5_DPP_VERSION","id", $endorsementCtr[0]['id']);
					} 

					$data = array($ORG_CODE,$MRC_CODE,$year,$reference_no,$version);
						$endorse = $crudapp->endorseApp($conn,$data);
						
						$today = date("m/d/Y H:i");	
						$auditData = array("reference_no"=>$reference_no,"version"=>$version,"updatedBy"=>$user,"updatedAt"=>$today,"status_from"=>"Submitted","status_to"=>"For Endorsement");	
						$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_DPP");
								
							if( $endorse == 1 && $result_update_status ) {
								sqlsrv_commit( $conn );
								//SEND EMAIL
								$emailfilter = "id = 1";
								$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
								$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
								$subject = @$emailinfo[0]['subject'];
								$body = @$emailinfo[0]['body'];

								$content = "This is to inform you that you have a pending for review items on your Department Annual Procurement Plan as of $today";
								$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>Department: $MRC_CODE<br>Reference #: $reference_no<br>Version: $version<br>";
								
								$body = str_replace("\$content",$content,$body);
								
								//EMAIL Receiver
								$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$MRC_CODE' AND ORG_CODE = '$ORG_CODE' AND FI = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE'";
								$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
								$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
								$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
								$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
							}

					// echo "Import success.<br />";
					$success = true;
					//header('Location: '. $url . "&success=true");
				} else {
					sqlsrv_rollback( $conn );
					// echo "Transaction rolled back.<br />";
					header('Location: '. $url . "&error=true");
				}
			}
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}
}

function checkIfItemExist($conn,$crudapp,$code, $ref_no, $version){
	//Check if same FI > 1
	$errorFlag = false;
	$cnd3 = "WHERE code = '$code' AND reference_no = '$ref_no' AND version = '$version'";
	$Ctr = $crudapp->matchRecord2($conn,"R5_VIEW_ITEMBASE_LINES",'id',$cnd3);
	if ($Ctr > 0){
		// $errorMessage .= 'Item already exist for this budget year.\n\n';
		$errorFlag = true;
	}

	return $errorFlag;
}

function isValidItemCode($conn,$crudapp,$code){
	$errorFlag = false;
	$tableName = "R5_VIEW_PARTS_UOM_INFO";
	$cnd = "WHERE PAR_NOTUSED NOT LIKE '+' AND PAR_CODE = '". $code ."'";
	
	$data = $crudapp->listRefRecordActive($conn,$tableName,$cnd);
	if (count($data) > 0){
		$errorFlag = true;
	} 
	return $errorFlag;
}
?>
<html> 
<head> 
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
<title>Opener</title> 
<script src="js/jquery.min.js"></script>
<script type='text/javascript'>  
	<?php if($success) { ?>
		opener.reloadPage("&res=pass&msg=You have successfully imported new item(s)!");
		self.close()
	<?php } ?>
	var progressStarted = false;

	$(document).ready(function() {
		$("form#form_uploader").submit(function(){
			return on_submit_form();
		});
	});
	function on_submit_form() {
		$(".isa_error").css("display","none");
		$(".actionButtonCenter input[type=submit]").prop("disabled", true);
		if($("#item-based-file").val() == "") {
				alert("Please Select File to Upload.");
				$(".actionButtonCenter input[type=submit]").prop("disabled", false);
				
				return false;
		} else {
			// window.setTimeout("getProgress()", 500);
			$('#iframe-progress').attr('src','iframe/iframe_progress_bar.php?id=<?php echo $uniqid; ?>&'+new Date()).show();
			// $("form#form_uploader").submit();
			return true;
		}
	
	}

	function uploadFile(type) {
		if(type == "cost") {
			$("#cost-based-file").trigger('click');
		} else {
			$("#item-based-file").trigger('click');
		}
	}
	
	function fileChange(e) {
		var filename = $(e).val();
		var fileNameIndex = filename.lastIndexOf("\\") + 1;
		var filename = filename.substr(fileNameIndex);
		$(e).closest("tr").find("td:eq(1)").html(filename);
	}
</script>
</head> 
<body> 
<div class="filters"></div>
	<div class="isa_success" style="<?php echo $success ? 'display: block;' : 'display: none;'; ?>">Record(s) has been successfully inserted!</div>
	<div class="isa_error" style="<?php echo $error ? 'display: block;' : 'display: none;'; ?>">Record(s) has not been inserted! <a href="<?php echo $save_file_path;?>">Please click this link for more details</a>.</div>
	<!--Start of FORM-->
	<div class="headerText">Budget Upload</div>
	<form id="form_uploader" name="form_uploader" action="<?php echo $_SERVER['PHP_SELF']?>?login=<?php echo $login;?>&MRC_CODE=<?php echo $MRC_CODE ?>&ORG_CODE=<?php echo $ORG_CODE ?>&year=<?php echo $year?>&version=<?php echo $version?>&reference_no=<?php echo $ref_no?>&cost_center=<?php echo $cost_center; ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="UPLOAD_IDENTIFIER" id="progress_key" value="<?php echo $uniqid;?>" /> 
		<table width="100%" cellspacing="0" cellpadding="0" border="1" class="listpop-progress">
			<tr >
				<td>Item-Based</td>
				<td class="file-name-td"></td>
				<td style="text-align:center; padding-left: 0px;">
					<button name="btn-item-based" style="cursor: pointer;" onclick="uploadFile('item'); return false;">...</button>
					<input type="file" name="item-based-file" id="item-based-file"  onchange="fileChange(this);" class="hidden"/>
				</td>
			</tr>
			<!-- <tr id="tr_progress">
				<td>Uploading Progress</td>
				<td colspan="2">
					<div id="progress">
						<div id="bar" style="width:0%">0%</div>
					</div>
				</td>
			</tr> -->
		</table>

		<iframe id="iframe-progress" name="iframe-progress" frameborder="0" border="0" scrolling="no" scrollbar="no" style="width: 100%; height: 51px; display:none;"></iframe>
		<div class="actionButtonCenter">
			<input type="submit" class="bold" name="submit_form" id="submit_form" value=" Save ">&nbsp;&nbsp;
			<input type="button" value=" Close " onclick="self.close();">&nbsp;&nbsp;
		</div>
	</form>
</body> 
</html>