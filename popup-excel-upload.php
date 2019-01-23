<?php
include("include/connect.php");
include("class/crud.php");

//VARIABLES
$errorFlag = false;
$errorMessage = "";

$crudapp = new crudClass();

//Item
if (isset($_FILES["item-based-file"])){ 

	$random_str = substr(md5(mt_rand()), 0, 7);
	$filepath_tmp = $_FILES["item-based-file"]["tmp_name"];
	$name = $_FILES["item-based-file"]["name"];
	//$ext = end((explode(".", $name)));
	$new_name = $random_str . $name;
	$filepath = "upload/".$new_name;
	if(move_uploaded_file($filepath_tmp,$filepath)){
		
		//$new_name;
		require('library/spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
		require('library/spreadsheet-reader-master/SpreadsheetReader.php');
		// $StartMem = memory_get_usage();
		try
		{
			$Spreadsheet = new SpreadsheetReader($filepath);
			// $BaseMem = memory_get_usage();

			$Sheets = $Spreadsheet -> Sheets();
			foreach ($Sheets as $Index => $Name)
		{
			echo '---------------------------------'.PHP_EOL;
			echo '*** Sheet '.$Name.' ***'.PHP_EOL;
			echo '---------------------------------'.PHP_EOL;

			$Time = microtime(true);

			$Spreadsheet -> ChangeSheet($Index);

			foreach ($Spreadsheet as $Key => $Row)
			{
				echo $Key . "<br/>";
				
				
				// if ($Row)
				// {
				// 	print_r($Row);
				// }
				// else
				// {
				// 	var_dump($Row);
				// }
		
			}
	
		}
			exit();
		

		} catch(Exception $e){
			echo $e->getMessage();
		}
	
	}
}


//ITEM BASED
if (false){
	$ref_no = $_POST['ref_no'];
	$ORG_CODE = $_POST['ORG_CODE'];
	$MRC_CODE = $_POST['MRC_CODE'];
	$quantity = $_POST['quantity_val'];

	$CUR_CODE_VAL = @$_POST['CUR_CODE_VAL'];


	$code = $_POST['code'];
	$CUR_CODE = $_POST['CUR_CODE'];
	$january = $_POST['january'];
	$february = $_POST['february'];
	$march =  $_POST['march'];
	$april =  $_POST['april'];
	$may =  $_POST['may'];
	$june = $_POST['june'];
	$july = $_POST['july'];
	$august = $_POST['august'];
	$september = $_POST['september'];
	$october = $_POST['october'];
	$november = $_POST['november'];
	$december = $_POST['december'];

	$unit_cost = 1;
	$rate = "";
	$foreign_cost = 0;
	$available = 0.00;
	
	
	if($CUR_CODE != "PHP" && $CUR_CODE != ""){
	$today = date("m/d/Y H:i");	
	$rate = $crudapp->checkRate($conn,"R5EXCHRATES","CRR_CURR = '$CUR_CODE' AND '$today' between CRR_START and CRR_END ORDER BY CRR_END DESC");
	}
	
	//Validation
	if ($code == ""){
	$errorMessage .= 'Please select an Item.\n\n';
	$errorFlag = true;
	}
	
	//Check if same FI > 1
	$cnd3 = "WHERE code = '$code' AND reference_no = '$ref_no' AND version = '$version'";
	$Ctr = $crudapp->matchRecord2($conn,"R5_VIEW_ITEMBASE_LINES",'id',$cnd3);
	if ($Ctr > 0 && $id == ""){
		$errorMessage .= 'Item already exist for this budget year.\n\n';
		$errorFlag = true;
	}
	
	//Validation
	if ($CUR_CODE != "PHP" && $CUR_CODE != " " && $rate == "none"){
		$errorMessage .= 'Please update exchange rate for the selected currency code.\n\n';
		$errorFlag = true;
	}else if ($CUR_CODE != "PHP" && $CUR_CODE != "" && $rate != "none"){
		$foreign_cost = $unit_cost;
		$available = $quantity * ($unit_cost / $rate);
		$unit_cost = $unit_cost / $rate;
	}else{
		$available = $quantity * $unit_cost;
		$foreign_cost = $unit_cost;
	}
	
	$q1_total_cost = ($january + $february + $march) * $unit_cost;
	$q2_total_cost = ($april + $may + $june) * $unit_cost;
	$q3_total_cost = ($july + $august + $september) * $unit_cost;
	$q4_total_cost = ($october + $november + $december) * $unit_cost;
	
	if ($code == ""){
	$errorMessage .= 'Please select an Item.\n\n';
	$errorFlag = true;
	}
	
	if ($CUR_CODE_VAL == ""){
	$errorMessage .= 'Please enter a Currency Code.\n\n';
	$errorFlag = true;
	}
	
		if (!is_numeric ($unit_cost)){
			$errorMessage .= 'Unit Cost must be numeric characters only.\n\n';
			$errorFlag = true;
		}
	
		if (!is_numeric ($january) || !is_numeric ($february) || !is_numeric ($march) || !is_numeric ($april) || !is_numeric ($may) || !is_numeric ($june) || !is_numeric ($july) || !is_numeric ($august) || !is_numeric ($september) || !is_numeric ($october) || !is_numeric ($november) || !is_numeric ($december)){
			$errorMessage .= 'Budget Month must be numeric characters only.\n\n';
			$errorFlag = true;
		}
	
		$today = date("m/d/Y H:i");	
		if(!$errorFlag){
			$data = array("record_id"=>$record_id,"id"=>$record_id,"code"=>$code,"quantity"=>$quantity,"available"=>$available,"total_cost"=>$available,"unit_cost"=>$unit_cost,"saveFlag"=>1,"version"=>1,"foreign_curr"=>$CUR_CODE_VAL,"foreign_cost"=>$foreign_cost,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);	
			$data2 = array("id"=>$record_id,"january"=>$january,"february"=>$february,
			"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
			"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
			$data5 = array("reference_no"=>$reference_no,"rowid"=>$record_id,"version"=>$version);	
			// $data6 = array("id"=>$record_id, "q1_total_cost"=>$q1_total_cost, "q2_total_cost"=>$q2_total_cost, "q3_total_cost"=>$q3_total_cost, "q4_total_cost"=>$q4_total_cost, "createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
			$data6 = array("id"=>$record_id, "createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
			
			for($q = 1; $q <= 4; $q++) {
				$data6["q" . $q . "_total_cost"] = ${"q".$q."_total_cost"};
				$data6["q" . $q . "_adjustments"] = 0;
				$data6["q" . $q . "_available"] =  ${"q".$q."_total_cost"};
				$data6["q" . $q . "_reserved"] = 0;
				$data6["q" . $q . "_allocated"] = 0;
				$data6["q" . $q . "_paid"] = 0;
			}

			$table = "R5_EAM_DPP_ITEMBASE_LINES";
			$table2 = "R5_REF_ITEMBASE_BUDGET_MONTH";
			$table3 = "R5_EAM_DPP_ITEMBASE_BRIDGE";
			$table4 = "R5_REF_ITEMBASE_BUDGET_QUARTERLY";
			
			// $result  = $crudapp->insertRecord($conn,$data,$table);
			// $result2 = $crudapp->insertRecord($conn,$data2,$table2);
			// $result3 = $crudapp->insertRecord($conn,$data5,$table3);
			// $result4 = $crudapp->insertRecord($conn,$data6,$table4);
			

			// header("Location:" . $_SERVER['PHP_SELF'] . "?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=Record has been successfully inserted!#FormAnchor");
		}

		if( $result == 1 && $result2 == 1 && $result3 == 1) {
			// sqlsrv_commit( $conn );
			//echo "Transaction committed.<br />";
		} else {
			// sqlsrv_rollback( $conn );
			// echo "Transaction rolled back.<br />";
		}

	}
?>


<html> 
<head> 
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
<title>Opener</title> 
<script src="js/jquery.min.js"></script>
<script type='text/javascript'>  
	$(document).ready(function(){
		$("#submit_form").click(function(e){
			if($("#item-based-file").val() == "") {
				alert("Please Select File to Upload.");
				return false;
			} else {
				$("form#form_uploader").submit();
			}
		});
	});

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
<style>
	.alert {
		padding: 20px;
		background-color: #f44336;
		color: white;
		opacity: 0.83;
		transition: opacity 0.6s;
		margin-bottom: 15px;
	}
</style>
</head> 
<body> 
<div class="filters"></div>

	<!--Start of FORM-->
	<div class="headerText">Budget Upload</div>
	<form id="form_uploader" name="form_uploader" action="<?php echo $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
		
		<table width="100%" cellspacing="0" cellpadding="0" border="1" class="listpop">
			<tr style="cursor: auto">
				<td>Item-Based</td>
				<td></td>
				<td style="text-align:center; padding-left: 0px;">
					<button name="btn-item-based" onclick="uploadFile('item'); return false;">...</button>
					<input type="file" name="item-based-file" id="item-based-file"  onchange="fileChange(this);" class="hidden"/>
				</td>
			</tr>

		</table>
	</form>
	<div class="actionButtonCenter">
		<input type="button" class="bold" name="submit_form" id="submit_form" value=" Save ">&nbsp;&nbsp;
		<input type="button" value=" Clear " onclick="cancel(this.form)">&nbsp;&nbsp;
	</div>
	<div style="margin: auto; width: 50%; padding: 10px;">
		<div class="alert" style="display:none;">
			<h3>Error!</h3> 
			<strong>Row 2</strong> 
			<p>-dsadad</p>
		</div>
	</div>

</body> 
</html>