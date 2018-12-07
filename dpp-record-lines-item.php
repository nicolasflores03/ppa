<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");
include("class/object.php");

/* Begin the transaction. */
if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

//Generate Object
$crudapp = new crudClass();
$filterapp = new filterClass();				
//VARIABLES
$errorFlag = false;
$errorMessage = "";
$year = $_GET['year'];
$lastDigit = substr($year, -1); 
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$version = $_GET['version'];
$reference_no = @$_GET['reference_no'];
$msg = @$_GET['msg'];
$res = @$_GET['res'];

//GET BUDGET DEADLINE
$deadlinefilter = "budget_year = '$year' AND isActive = '1'";
$deadlinecolumn = $crudapp->readColumn($conn,"R5_DEADLINE_MAINTENANCE");
$deadlineinfo = $crudapp->listTable($conn,"R5_DEADLINE_MAINTENANCE",$deadlinecolumn,$deadlinefilter);
$deadlinemonth = @$deadlineinfo[0]['month'];
$deadlinedate = @$deadlineinfo[0]['date'];
$deadlineyear = @$deadlineinfo[0]['year'];

$expiration = $deadlinemonth."/".$deadlinedate."/".$deadlineyear;
$expiration_orig = str_replace(" ","",$expiration);
//echo $expiration;
$expiration = date("m/d/Y", strtotime($expiration_orig));
//Check if current date is greater than or equal the expiration date
$today = date("m/d/Y");

$today = new DateTime($today);
$expiration = new DateTime($expiration);

//GET status Based on reference_no,dept,org,year
$dppfilter = "year_budget = '$year' AND reference_no = '$reference_no' AND version = $version";
$dppcolumn = $crudapp->readColumn($conn,"R5_VIEW_DPP_VERSION");
$dppinfo = $crudapp->listTable($conn,"R5_VIEW_DPP_VERSION",$dppcolumn,$dppfilter);
$status = $dppinfo[0]['status'];
$remarks = $dppinfo[0]['remarks'];
$cost_center = $dppinfo[0]['cost_center'];
$orgcode = $dppinfo[0]['ORG_CODE'];
$mrccode = $dppinfo[0]['MRC_CODE'];


//GET SAM
$samfilter = "USR_CODE = '$user' AND MRC_CODE = '$mrccode' AND ORG_CODE = '$orgcode'";
$samcolumn = $crudapp->readColumn($conn,"R5_CUSTOM_SAM");
$saminfo = $crudapp->listTable($conn,"R5_CUSTOM_SAM",$samcolumn,$samfilter);
$bo = @$saminfo[0]['BO'];
$fi = @$saminfo[0]['FI'];
$dh = @$saminfo[0]['DH'];
$cfo =@$saminfo[0]['CFO'];


$expired = 0;
$stats = str_replace(" ","",$status);
if ($today > $expiration && $stats == "Unfinish"){
$expired = 1;
}else{
$expired = 0;
}


$filter = array();
$cndItem = "id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = '$reference_no' AND version = '$version') AND reference_no = '$reference_no' AND version = '$version'";

//ITEMBASE
$column = $crudapp->readColumn($conn,"R5_VIEW_ITEMBASE_LINES");
$record_id = $crudapp->readID($conn,"R5_EAM_DPP_ITEMBASE_LINES");
$record_id = $record_id + 1;


$versionExist = $crudapp->checkRecordExist($conn,"R5_DPP_VERSION","ORG_CODE = '$orgcode' AND MRC_CODE = '$mrccode' AND year_budget = '$year'");

$requiredField = array('id','code','Description','GL_Code','UOM','QTY','Foreign_Currency','foreign_cost','unit_cost','total_cost','Classification','Jan','Feb','Mar','Apr','may','Jun','Jul','Aug','Sept','Oct','Nov','Dec');
$column = array_intersect($column,$requiredField);
$listView = $crudapp->listTable($conn,"R5_VIEW_ITEMBASE_LINES",$column,$cndItem);
$tableView = $filterapp->filterViewURL($conn,$column,$listView,$filter,"id");


//Item
if (isset($_POST['searchItem'])){
$fieldname = $_POST['fieldnameItem'];
$value = $_POST['valueItem'];
$type = $_POST['typeItem'];
	//Form Validation
	if ($fieldname == ""){
	$errorMessage .= 'Please select a fieldname.\n\n';
	$errorFlag = true;
	}
	
	if ($value == ""){
	$errorMessage .= 'Please select a value.\n\n';
	$errorFlag = true;
	}

	if(!$errorFlag){
		$filter = array($fieldname,$type,$value);
		$tableView = $filterapp->filterViewURL($conn,$column,$listView,$filter,"id");
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}	
}


if (isset($_POST['save'])){
$ref_no = $_POST['ref_no'];
$save = $crudapp->saveApp($conn,$ref_no,$version);
	if( $save == 1) {
		sqlsrv_commit( $conn );
		//echo "Transaction committed.<br />";
	} else {
		sqlsrv_rollback( $conn );
		//echo "Transaction rolled back.<br />";
	}
	header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=You have successfully saved the records!");
}

/*if (isset($_POST['back'])){
$back = $crudapp->backApp($conn,$reference_no,$version);
	if($back == 1) {
		sqlsrv_commit( $conn );
		//echo "Transaction committed.<br />";
	} else {
		sqlsrv_rollback( $conn );
		//echo "Transaction rolled back.<br />";
	}
	header("Location:dpp-version-list.php?login=".$user);
}*/


if (isset($_POST['copy'])){
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
$reference_no = $_POST['ref_no'];
$createCopy = $crudapp->createCopy($conn,$ORG_CODE,$MRC_CODE,$reference_no,$year,$version,$cost_center,$user);
sqlsrv_commit( $conn );
header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$createCopy);
}

if (isset($_POST['endorsement'])){
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
$reference_no = $_POST['ref_no'];

$condition = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE' AND year_budget = '$year' AND cost_center = '$cost_center' AND (status = 'For Endorsement' OR status = 'Endorsed' OR status = 'Approved')";

//check if other endorsement exist
$endorsementCtr = $crudapp->matchRecord2($conn,"R5_DPP_VERSION",'id',$condition);

	if ($endorsementCtr < 1){
		$data = array($ORG_CODE,$MRC_CODE,$year,$reference_no,$version);
		$endorse = $crudapp->endorseApp($conn,$data);
		
		$today = date("m/d/Y H:i");	
		$auditData = array("reference_no"=>$reference_no,"version"=>$version,"updatedBy"=>$user,"updatedAt"=>$today,"status_from"=>"Submitted","status_to"=>"For Endorsement");	
		$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_DPP");
				
			if( $endorse == 1) {
				sqlsrv_commit( $conn );
				//echo "Transaction committed.<br />";
				
								
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
				
				
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=You have successfully submitted this APP for endorsement!");
			} else {
				sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=fail&msg=Transaction rolled back!");
			}
	}else{
	$errorMessage ="You have an active endoresed APP!";
	echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}
}

if (isset($_POST['submitted'])){
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
$reference_no = $_POST['ref_no'];

$condition = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE' AND year_budget = '$year' AND cost_center = '$cost_center' AND (status = 'For Endorsement' OR status = 'Endorsed' OR status = 'Approved')";

//check if other endorsement exist
$endorsementCtr = $crudapp->matchRecord2($conn,"R5_DPP_VERSION",'id',$condition);

	if ($endorsementCtr < 1){
		$data = array($ORG_CODE,$MRC_CODE,$year,$reference_no,$version);
		$endorse = $crudapp->submitApp($conn,$data);
		
		$today = date("m/d/Y H:i");	
		$auditData = array("reference_no"=>$reference_no,"version"=>$version,"updatedBy"=>$user,"updatedAt"=>$today,"status_from"=>"Unfinish","status_to"=>"Submitted");	
		$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_DPP");
				
			if( $endorse == 1) {
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
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$MRC_CODE' AND ORG_CODE = '$ORG_CODE' AND DH = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
				//echo "Transaction committed.<br />";
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=You have successfully submitted this APP for review!");
			} else {
				sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=fail&msg=Transaction rolled back!");
			}
	}else{
	$errorMessage ="You have an active endoresed APP!";
	echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}
}


if (isset($_POST['revise'])){
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
$reference_no = $_POST['ref_no'];

		$data = array($ORG_CODE,$MRC_CODE,$year,$reference_no,$version);
		$revised = $crudapp->reviseApp($conn,$data);
		
		$today = date("m/d/Y H:i");	
		$auditData = array("reference_no"=>$reference_no,"version"=>$version,"updatedBy"=>$user,"updatedAt"=>$today,"status_from"=>"Submitted","status_to"=>"Revision Request");	
		$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_DPP");
				
			if( $revised == 1) {
				sqlsrv_commit( $conn );				
				
				//SEND EMAIL
				$emailfilter = "id = 1";
				$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
				$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
				$subject = @$emailinfo[0]['subject'];
				$body = @$emailinfo[0]['body'];
				
				$content = "This is to inform you that your Department Annual Procurement Plan has been returned for revision as of $today<br>";
				$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>Department: $MRC_CODE<br>Reference #: $reference_no<br>Version: $version<br>";
				$body = str_replace("\$content",$content,$body);

				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$MRC_CODE' AND ORG_CODE = '$ORG_CODE' AND DH = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);


				//SEND EMAIL TO BUDGET OFFICER
				//SEND EMAIL
				$emailbudgetfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$MRC_CODE' AND ORG_CODE = '$ORG_CODE' AND BO = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE'";
				$emailbudgetcolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$emailbudetinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$emailbudgetcolumn,$emailbudgetfilter);
				
				foreach ($emailbudetinfo as $budgetinfo){
				//echo $budgetinfo['PER_EMAILADDRESS']."<br>";
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$budgetinfo['PER_EMAILADDRESS'],$subject,$body);
				}
				
				//echo "Transaction committed.<br />";
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=You have successfully submitted this APP for revision!");
			} else {
				sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=fail&msg=Transaction rolled back!");
			}
}

//ITEM BASED
if (isset($_POST['submit'])){
$ref_no = $_POST['ref_no'];
$id = $_POST['id'];
$code = $_POST['code'];
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
$CUR_CODE = @$_POST['CUR_CODE'];
$CUR_CODE_VAL = @$_POST['CUR_CODE_VAL'];
$quantity = $_POST['quantity_val'];
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
$unit_cost = $_POST['unit_cost'];
$unit_cost = str_replace(",","",$unit_cost);
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

if ($code == ""){
$errorMessage .= 'Please select an Item.\n\n';
$errorFlag = true;
}


if ($unit_cost == ""){
$errorMessage .= 'Please enter a Unit Cost.\n\n';
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
		$data = array("record_id"=>$record_id,"id"=>$record_id,"code"=>$code,"quantity"=>$quantity,"available"=>$available,"total_cost"=>$available,"unit_cost"=>$unit_cost,"saveFlag"=>0,"version"=>1,"foreign_curr"=>$CUR_CODE_VAL,"foreign_cost"=>$foreign_cost,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);	
		$data2 = array("id"=>$record_id,"january"=>$january,"february"=>$february,
		"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
		"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
		$data5 = array("reference_no"=>$reference_no,"rowid"=>$record_id,"version"=>$version);	
		
		$table = "R5_EAM_DPP_ITEMBASE_LINES";
		$table2 = "R5_REF_ITEMBASE_BUDGET_MONTH";
		$table3 = "R5_EAM_DPP_ITEMBASE_BRIDGE";
		if($id != ""){
		//Check if it has previous version
		$checkLineItemStatus = $crudapp->getLineVersionInfo($conn,$table,$id);
			if ($checkLineItemStatus > 0){
				$recVersion = $crudapp->readVersion($conn,"R5_EAM_DPP_ITEMBASE_LINES","id = '$id'");
				$recVersion = $recVersion + 1;
				$dataNew = array("record_id"=>$id,"id"=>$record_id,"code"=>$code,"quantity"=>$quantity,"available"=>$available,"total_cost"=>$available,"unit_cost"=>$unit_cost,"saveFlag"=>0,"version"=>$recVersion,"foreign_curr"=>$CUR_CODE_VAL,"foreign_cost"=>$foreign_cost,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);	
				$resultNew = $crudapp->insertRecord($conn,$dataNew,$table);
				$result2New = $crudapp->insertRecord($conn,$data2,$table2);
				$cnd = "reference_no = '$ref_no' AND rowid = '$id' AND version =$version";
				$result3New = $crudapp->updateRecord2($conn,$data5,$table3,$cnd);
			}else{
				$data3 = array("code"=>$code,"quantity"=>$quantity,"available"=>$available,"total_cost"=>$available,"unit_cost"=>$unit_cost,"saveFlag"=>0,"foreign_curr"=>$CUR_CODE_VAL,"foreign_cost"=>$foreign_cost,"updatedAt"=>$today,"updatedBy"=>$user);	
				$data4 = array("january"=>$january,"february"=>$february,
				"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
				"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"updatedAt"=>$today,"updatedBy"=>$user);
				
				$result = $crudapp->updateRecord($conn,$data3,$table,"id",$id);
				$result2 = $crudapp->updateRecord($conn,$data4,$table2,"id",$id);
			}
		
		//Insert Record to Audit by Benjie Manalaysay 3/28/2016
		$auditData = array("record_id"=>$id,"updatedBy"=>$user,"updatedAt"=>$today,"table_name"=>$table,"update_type"=>"Edit");	
		$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_APP_LINES");
		
		}else{
			$result = $crudapp->insertRecord($conn,$data,$table);
			$result2 = $crudapp->insertRecord($conn,$data2,$table2);
			$result3 = $crudapp->insertRecord($conn,$data5,$table3);
		}
		
			//if( $result == 1 && $result2 == 1 && $result3 == 1) {
				sqlsrv_commit( $conn );
				//echo "Transaction committed.<br />";
			//} else {
				//sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
			//}
			header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=Record has been successfully inserted!#FormAnchor");
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}	
}
?>


<!DOCTYPE html>
<html>
<title>Infor Eam</title>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<script src="js/jquery.min.js">
</script>
<script src="js/string-util.js">
</script>
<style>
.Total_Cost, .Unit_Cost{
text-align:right;
padding-right: 20px;
}
.Description{
text-align:left;
padding-left: 20px;
}
</style>
<script>

function cancel(oForm) {
    
  var elements = oForm.elements; 
    
  oForm.reset();

  for(i=0; i<elements.length; i++) {
      
	field_type = elements[i].type.toLowerCase();
	
	switch(field_type) {
	
		case "text": 
		case "password": 
		case "textarea":
	        case "hidden":	
			$(this).prop("defaultValue");
			//elements[i].value = ""; 
			break;
        
		case "radio":
		case "checkbox":
  			if (elements[i].checked) {
   				elements[i].checked = false; 
			}
			break;

		case "select-one":
		case "select-multi":
            		elements[i].selectedIndex = "";
			break;

		default: 
			break;
	}
    }
}

function isAmountWithinAllowedPrecision2(number) {

		num = number.toString().replace(/\$|\,/g,'');
	
		if (num.indexOf('.') > 0) {
			var parts = num.toString().split('.');
			parts[0].length; 		
			if (parts[0].length > 12) {
				return false;
			} else {
				return true;
			}
		} else {
			if (num.length > 12) {
				return false;
			} else {
				return true;
			}
		}	
}

function round2(number,dec) {
var result = 0.00;
    if (isAmountWithinAllowedPrecision2(number)) {
		num = number.toString().replace(/\$|\,/g,'');
		
		if (num.indexOf('.') > 0) {
			var parts = num.toString().split('.');
			parts[0].length; 		
			if (parts[0].length > 12) {
				num = parts[0].substring(0,12) +'.'+ parts[1];
			} 
		} else {
			if (num.length > 12) {
				num = num.substring(0,12);
			}
		}
		
		if(isNaN(num))
		num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		cents = num%100;
		num = Math.floor(num/100).toString();
		if(cents<10)
		cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
		num = num.substring(0,num.length-(4*i+3))+','+
		num.substring(num.length-(4*i+3));
		result = (((sign)?'':'-')+ num + '.' + cents);	
		return result;
	} else {
		return result;
	}	
}

function onclickEvent(id){
$('#id').val(id);
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	 var json = $.parseJSON(xmlhttp.responseText);
	 var code = json['Code'];
	 var jan = json['Jan'];
	 var feb = json['Feb'];
	 var mar = json['Mar'];
	 var apr = json['Apr'];
	 var may = json['May'];
	 var jun = json['Jun'];
	 var jul = json['Jul'];
	 var aug = json['Aug'];
	 var sept = json['Sept'];
	 var oct = json['Oct'];
	 var nov = json['Nov'];
	 var dec = json['Dec'];
	 var unit_cost = json['unit_cost'];
     var quantity = jan+feb+mar+apr+may+jun+jul+aug+sept+oct+nov+dec;
	 
	 $('#code').val(code);
	 $('#quantity').val(quantity);
	 $('#january').val(jan);
	 $('#february').val(feb);
	 $('#march').val(mar);
	 $('#april').val(apr);
	 $('#may').val(may);
	 $('#june').val(jun);
	 $('#july').val(jul);
	 $('#august').val(aug);
	 $('#september').val(sept);
	 $('#october').val(oct);
	 $('#november').val(nov);
	 $('#december').val(dec);	
	 $('#unit_cost').val(unit_cost);	
	
	 getItemInfo2(unit_cost);
    }
  }
xmlhttp.open("GET","ajax/app-get-itembase-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}



//EAM TABLE
function valideopenerform2(obj){	

	var code = $('#code').val();
	var quantity = $('#quantity').val();
	var cost = $('#cost').val();
	var item_val = $('#item_val').val();
	var january = $('#january').val();
	var february = $('#february').val();
	var march = $('#march').val();
	var april = $('#april').val();
	var may = $('#may').val();
	var june = $('#june').val();
	var july = $('#july').val();
	var august = $('#august').val();
	var september = $('#september').val();
	var october = $('#october').val();
	var november = $('#november').val();
	var december = $('#december').val();	

			var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
		
var popup= window.open('popup2.php?hash='+text+'&obj='+obj+'&code=&item_val=&quantity='+quantity+'&cost='+cost+''
+'&january='+january+'&february='+february+'&march='+march+'&april='+april+'&may='+may+'&june='+june+'&july='+july+''
+'&august='+august+'&september='+september+'&october='+october+'&november='+november+'&december='+december+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}



function getItemInfo(){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
//code = code.replace(/ /g, '');
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var unit_cost = json['PAR_BASEPRICE'];
	 //Protect Unit Cost if base price is greater than 0
	 var description = json['PAR_DESC'];
	 var itemtype = json['CMD_DESC'];classification
	 var classification = json['PAR_UDFCHAR07'];
	 var PAR_LASTPRICE = json['PAR_LASTPRICE'];
	 var uom = json['UOM_DESC'];
	 var gl = json['gl'];
	 var gl_description = json['gl_description'];
	 
	 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo substr($cost_center, 2);?>';
	 
	 $('#unit_cost').val(unit_cost);
	 if (unit_cost > 0){
	   $('#unit_cost').attr('readonly', 'true'); // mark it as read only
	   $('#CUR_CODE').val('PHP');
	   $('#CUR_CODE').attr('disabled', 'true'); // mark it as read only
	 }else{
	   $('#unit_cost').removeAttr('readonly'); // mark it as read only
	   $('#CUR_CODE').val('PHP');
	   $('#unit_cost').val('0.00');
	   $('#CUR_CODE').removeAttr('disabled'); // mark it as read only	 
	 }
	 //$('#unit_cost_val').val(unit_cost);
	 $('#description').val(description);
	 $('#PAR_LASTPRICE').val(PAR_LASTPRICE);
	 $('#itemtype').val(itemtype);
	 $('#classification').val(classification);
	 $('#uom').val(uom);
	 $('#itemGL').val(gl);
	 $('#gl_description').val(gl_description);
	 $('#io_number').val(io_number);
	 getTotalCost();
    }
  }
xmlhttp.open("GET","ajax/app-record-create.php?hash="+text+"&code="+code,true);
xmlhttp.send();
}


function getItemInfo2(unit_cost){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
//code = code.replace(/ /g, '');
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var unit_cost2 = json['PAR_BASEPRICE'];
	 //Protect Unit Cost if base price is greater than 0
	 var description = json['PAR_DESC'];
	 var itemtype = json['CMD_DESC'];classification
	 var classification = json['PAR_UDFCHAR07'];
	 var PAR_LASTPRICE = json['PAR_LASTPRICE'];
	 var uom = json['UOM_DESC'];
	 var gl = json['gl'];
	 var gl_description = json['gl_description'];
	 
	 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo substr($cost_center, 2);?>';
	 
	 $('#unit_cost').val(unit_cost);
	 $('#CUR_CODE').val('PHP');
	 if (unit_cost2 > 0){
	   $('#unit_cost').attr('readonly', 'true'); // mark it as read only
	     $('#CUR_CODE').attr('disabled', 'true'); // mark it as read only
	 }else if (unit_cost2 == null){
	   $('#unit_cost').removeAttr( "readonly" ); // mark it as read only
	   $('#CUR_CODE').removeAttr( "disabled" ); // mark it as read only
	 }
	
	 //$('#unit_cost_val').val(unit_cost);
	 $('#description').val(description);
	 $('#PAR_LASTPRICE').val(PAR_LASTPRICE);
	 $('#itemtype').val(itemtype);
	 $('#classification').val(classification);
	 $('#uom').val(uom);
	 $('#itemGL').val(gl);
	 $('#gl_description').val(gl_description);
	 $('#io_number').val(io_number);
	 getTotalCost();
    }
  }
xmlhttp.open("GET","ajax/app-record-create.php?hash="+text+"&code="+code,true);
xmlhttp.send();
}

function getTotalCost(){
	var jan = $('#january').val();
		var feb = $('#february').val();
		var mar = $('#march').val();
		var apr = $('#april').val();
		var may = $('#may').val();
		var jun = $('#june').val();
		var jul = $('#july').val();
		var aug = $('#august').val();
		var sept = $('#september').val();
		var oct = $('#october').val();
		var nov = $('#november').val();
		var dec = $('#december').val();
		
		var qty = parseFloat(jan) + parseFloat(feb) + parseFloat(mar) + parseFloat(apr) + 
		parseFloat(may) + parseFloat(jun) + parseFloat(jul) + parseFloat(aug) + parseFloat(sept) + 
		parseFloat(oct) + parseFloat(nov) + parseFloat( dec);
		
		$("#quantity").val(qty);
		$("#quantity_val").val(qty);
		var unit_cost = $('#unit_cost').val();
		unit_cost = unit_cost.replace(/,/g, '');
		var total_cost = parseFloat($('#quantity').val()) * parseFloat(unit_cost);		
		total_cost = round2(total_cost,2);
		$("#cost").val(total_cost);
}


function deleteRecord(id){
	var version = "<?php echo $version; ?>";
	var reference_no = "<?php echo $reference_no; ?>";
	var updatedBy = "<?php echo $user; ?>";
var obj = 'R5_EAM_DPP_ITEMBASE_LINES';
var obj2 = 'R5_EAM_DPP_ITEMBASE_BRIDGE';
var r=confirm("Are you sure you want to delete this record?");
var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	if (r==true){
		for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		var xmlhttp2=new XMLHttpRequest();
		xmlhttp2.onreadystatechange=function()
		{
		if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
			{
				var result = xmlhttp2.responseText;
				alert('Record has been deleted successfully!');
				location.reload();
			}
		}
		xmlhttp2.open("GET","ajax/app-delete-record-item-cost.php?hash="+text+"&id="+id+"&obj1="+obj+"&obj2="+obj2+"&reference_no="+reference_no+"&version="+version+"&updatedBy="+updatedBy,true);
		xmlhttp2.send();
	}
}

$(document).ready(function(){

//Error Message
var res = "<?php echo @$res;?>";
$('.isa_info').show();
if(res !=""){
	if (res == "pass"){
		$('.isa_success').show();
		$('.isa_error').hide();
	}else {
		$('.isa_error').show();
		$('.isa_success').hide();
	}
}
	

	//Checks the status of DPP and disable uneccessary buttons
	var status = "<?php echo $status; ?>";
	var version = "<?php echo $version; ?>";
	var user = "<?php echo $user; ?>";
	var year = "<?php echo $year; ?>";
	var reference_no = "<?php echo $reference_no; ?>";
	var bo = parseInt("<?php echo $bo; ?>");
	var fi = parseInt("<?php echo $fi; ?>");
	var dh = parseInt("<?php echo $dh; ?>");
	var cfo = parseInt("<?php echo $cfo; ?>");
	
	$('#submitted_tmp').hide();
	$('#endorsement_tmp').hide();
	
//Hide Endorsement Button
if(bo > 0){
	$('#submitted_tmp').show();
}
if(dh > 0){
	$('#endorsement_tmp').show();
}else{
$('#revise_tmp').hide();
}
	
//IF DEADLINE
var expired = "<?php echo $expired; ?>";
if(expired > 0 && version < 2){
	$('#endorsement_tmp').attr("disabled", true); 
	$('#submitted_tmp').attr("disabled", true); 
	$('#save_tmp').hide();
	$('.actionButtonCenter').hide(); 
	$('#copy_tmp').hide();
	$('#new_tmp').hide();
	$('#revise_tmp').hide(); 
}else{	
	status = status.replace(/ /g,'');
	if(status=="Submitted"){
		$('#endorsement_tmp').attr("disabled", false);
		$('#submitted_tmp').attr("disabled", true);
		$('#save_tmp').hide();
		$('.actionButtonCenter').hide();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.deleteButton').hide();
	}else if(status=="ForEndorsement"){
		$('#endorsement_tmp').attr("disabled", true);
		$('#submitted_tmp').attr("disabled", true);
		$('#save_tmp').hide();
		$('.actionButtonCenter').hide();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.deleteButton').hide();
		$('#revise_tmp').hide();
	}else if(status=="Endorsed"){
		$('#endorsement_tmp').attr("disabled", true);
		$('#submitted_tmp').attr("disabled", true);
		$('#save_tmp').hide();
		$('.actionButtonCenter').hide();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.deleteButton').hide();
		$('#revise_tmp').hide();
	}else if(status=="RevisionRequest"){
		$('#endorsement_tmp').hide();
		$('#submitted_tmp').hide();
		$('#save_tmp').hide();
		$('.actionButtonCenter').hide();
		$('#copy_tmp').show();
		$('#new_tmp').show();
		$('.deleteButton').hide();
		$('#revise_tmp').hide();
	}else if(status=="Approved"){
		$('#endorsement_tmp').hide();
		$('#submitted_tmp').hide();
		$('#save_tmp').hide();
		$('.actionButtonCenter').hide();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.deleteButton').hide();
		$('#revise_tmp').hide();
	}else if(status=="ForRevision"){
		$('#endorsement_tmp').attr("disabled", true);
		$('#submitted_tmp').attr("disabled", false);
		$('.actionButtonCenter').show();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('#revise_tmp').hide();
	}else{
		$('#endorsement_tmp').attr("disabled", true);
		$('#submitted_tmp').attr("disabled", false);
		$('.actionButtonCenter').show();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('#revise_tmp').hide();
	}
}
	
	$("#year_budget").val(year);
	
	/*var counter = 0;
    $(".list th").each(function(){
        var width = $('.list tr:last td:eq(' + counter + ')').width();
        $(".NewHeader tr").append(this);
        this.width = width;
        counter++;
    });*/
	
		//Save Button
	$("#back_tmp").click(function() {
		var r=confirm("Are you sure you want to go back to the main menu? \n\nNote: Please make sure to save first before leaving this page or else your updates on the APP will not be reflected.");
		if (r==true){
				$("#back").click();
		}
	});
	
		//Save Button
	$("#tabURL").click(function() {
		window.location = "dpp-record-lines-cost.php?login="+user+"&year="+year+"&reference_no="+reference_no+"&version="+version;
	});
	
	$("#newRecord").click(function() {
		window.location = "dpp-record-lines-item.php?login="+user+"&year="+year+"&reference_no="+reference_no+"&version="+version;
	});
	
	$("#save_tmp").click(function() {
	$(this).attr("disabled", true);
	var expired = "<?php echo $expired; ?>";
	if(expired < 1 || (expired > 0 && version > 1)){
		var r=confirm("Are you sure you want to save records on this APP?");
		if (r==true){
				$("#save").click();
		}else{
		$(this).attr("disabled", false);
		}
	}else{
	alert("Budget time expired!");
	}
	});
	
	$("#new_tmp").click(function() {
	$(this).attr("disabled", true);
	var expired = "<?php echo $expired; ?>";
	if(expired < 1){
		var r=confirm("Are you sure you want to create a new APP?");
		if (r==true){
				window.location = "create-dpp-version.php?login="+user;
		}else{
		$(this).attr("disabled", false);
		}
	}else{
	alert("Budget time expired!");
	}
	});
	
	$("#copy_tmp").click(function() {
	$(this).attr("disabled", true);
	var expired = "<?php echo $expired; ?>";
	if(expired < 1 || (expired > 0 && version > 1)){
		var r=confirm("Are you sure you want to create a new APP?");
		if (r==true){
				$("#copy").click();
		}else{
		$(this).attr("disabled", false);
		}
	}else{
	alert("Budget time expired!");
	}
	});
	
	$("#endorsement_tmp").click(function() {
	$(this).attr("disabled", true);
	//var expired = "<?php echo $expired; ?>";
	//if(expired < 1){
	//HASH - To random string that will reload pages with ajax call
	var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		var ref_no = $('#ref_no').val();
		var unsave = 0;
		//getUnsaved
		var xmlhttp=new XMLHttpRequest();
		xmlhttp.onreadystatechange=function()
		{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
			unsave = xmlhttp.responseText;
				if(unsave < 1){
				var r=confirm("Are you sure you want to endorse this APP?");
					if (r==true){
							$("#endorsement").click();
					}else{
					$(this).attr("disabled", false);
					}
				}else{
					var r=confirm("Are you sure you want to endorse this APP?");
					if (r==true){
						var xmlhttp1=new XMLHttpRequest();
						xmlhttp1.onreadystatechange=function()
						{
						if (xmlhttp1.readyState==4 && xmlhttp1.status==200)
							{
							var save = xmlhttp1.responseText;
								if(save == 1){
									$("#endorsement").click();
								}
							}
						}
						xmlhttp1.open("GET","ajax/dpp-recordline-save.php?hash="+text+"&reference_no="+ref_no+"&version="+version,true);
						xmlhttp1.send();
					}else{
						//$("#endorsement").click();
						$(this).attr("disabled", false);			
					}
				}
			}
		}
		xmlhttp.open("GET","ajax/dpp-recordline-unsave.php?hash="+text+"&reference_no="+ref_no+"&version="+version,true);
		xmlhttp.send();
	//}else{
	//alert("Budget time expired!");
	//}
	});
	
	
	$("#submitted_tmp").click(function() {
	$(this).attr("disabled", true);
	var expired = "<?php echo $expired; ?>";
	if(expired < 1 || (expired > 0 && version > 1)){
	//HASH - To random string that will reload pages with ajax call
	var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		var ref_no = $('#ref_no').val();
		var unsave = 0;
		//getUnsaved
		var xmlhttp=new XMLHttpRequest();
		xmlhttp.onreadystatechange=function()
		{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
			unsave = xmlhttp.responseText;
				if(unsave < 1){
				var r=confirm("Are you sure you want to Submit this APP?");
					if (r==true){
							$("#submitted").click();
					}else{
					$(this).attr("disabled", false);
					}
				}else{
					var r=confirm("Are you sure you want to Submit this APP?");
					if (r==true){
						var xmlhttp1=new XMLHttpRequest();
						xmlhttp1.onreadystatechange=function()
						{
						if (xmlhttp1.readyState==4 && xmlhttp1.status==200)
							{
							var save = xmlhttp1.responseText;
								if(save == 1){
									$("#submitted").click();
								}
							}
						}
						xmlhttp1.open("GET","ajax/dpp-recordline-save.php?hash="+text+"&reference_no="+ref_no+"&version="+version,true);
						xmlhttp1.send();
					}else{
						//$("#endorsement").click();
						$(this).attr("disabled", false);	
					}
				}
			}
		}
		xmlhttp.open("GET","ajax/dpp-recordline-unsave.php?hash="+text+"&reference_no="+ref_no+"&version="+version,true);
		xmlhttp.send();
	}else{
	alert("Budget time expired!");
	}
	});
	
	
	$("#revise_tmp").click(function() {
	$(this).attr("disabled", true);
	 	var r=confirm("Are you sure you want to revise this APP?");
		if (r==true){
			$("#revise").click();
		}else{
			//$("#endorsement").click();
			$(this).attr("disabled", false);
		}
	});
	
	$(".scheduleField").change(function() {
		var sched = $(this).val();
		if(!isNaN(sched) && sched != ""){
		getTotalCost();
		}else{
		$(this).val("0");
		alert("Value must be numeric.");
		}
	}); 
	
	$("#CUR_CODE").change(function() {
		var curr_code = $(this).val();
		$("#CUR_CODE_VAL").val(curr_code);
	});

	$("#unit_cost").change(function() {
		getTotalCost();
	});
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."#FormAnchor"; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">Annual Procurement Plan</div></div>
<div class="isa_info"><b>Deadline of budget submission is on: </b><?php echo $expiration_orig; ?></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="actionBar">
	<div class="divText">
	<!--<img src="images/toolbar_previous.png" name="back_tmp" id="back_tmp" align="absmiddle">-->
	<!--<img src="images/toolbar_save.png" name="save_tmp" id="save_tmp" align="absmiddle">-->
	<input type="button" class="bold" name="endorsement_tmp" id="endorsement_tmp" value=" For Endorsement ">
	<input type="button" class="bold" name="revise_tmp" id="revise_tmp" value=" Revision Request ">
	<input type="button" class="bold" name="submitted_tmp" id="submitted_tmp" value=" Submit ">
	<input type="button" class="bold" name="copy_tmp" id="copy_tmp" value=" Create a Copy from this APP ">
	<input type="button" class="bold" name="new_tmp" id="new_tmp" value=" Create New APP ">
		<div class="hidden">
			<!--<input type="submit" class="bold" name="back" id="back" value=" Back ">-->
			<input type="submit" class="bold" name="save" id="save" value=" Save ">
			<input type="submit" class="bold" name="submitted" id="submitted" value=" Submit ">
			<input type="submit" class="bold" name="revise" id="revise" value=" Revision Request ">
			<input type="submit" class="bold" name="endorsement" id="endorsement" value=" For Endorsement ">
			<input type="submit" class="bold" name="copy" id="copy" value=" Create a Copy from this APP ">
		</div>
	</div>
</div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField"><input type="hidden" class="field" name="ref_no" id="ref_no" spellcheck="false" tabindex="1" value= "<?php echo $reference_no;?>"><input type="text" class="field" name="organization" id="organization" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['ORG_DESC'];?>" readonly><input type="hidden" class="field" name="ORG_CODE" id="ORG_CODE" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['ORG_CODE'];?>"></td>			
			<td class="textLabel">Year Budget:</td>
			<td class="textField">
				<select name="year_budget" id="year_budget" readonly>
					<option value="">-- Please select --</option>
					<option value="2014">2014</option>
					<option value="2015">2015</option>
					<option value="2016">2016</option>
					<option value="2017">2017</option>
					<option value="2018">2018</option>
					<option value="2019">2019</option>
					<option value="2020">2020</option>
					<option value="2021">2021</option>
					<option value="2022">2022</option>
					<option value="2023">2023</option>
					<option value="2024">2024</option>
					<option value="2025">2025</option>
					<option value="2026">2026</option>
					<option value="2027">2027</option>
					<option value="2028">2028</option>
					<option value="2029">2029</option>
					<option value="2030">2030</option>
					<option value="2031">2031</option>
					<option value="2032">2032</option>
					<option value="2033">2033</option>
					<option value="2034">2034</option>
					<option value="2035">2035</option>
					<option value="2036">2036</option>
					<option value="2037">2037</option>
					<option value="2038">2038</option>
					<option value="2039">2039</option>
					<option value="2040">2040</option>
				</select>
			</td>				
		</tr>
		<tr>
			<td class="textLabel">Department:</td>
			<td class="textField"><input type="text" class="field" name="department" id="department" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['MRC_DESC'];?>" readonly><input type="hidden" class="field" name="MRC_CODE" id="MRC_CODE" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['MRC_CODE'];?>"></td>				
			<td class="textLabel">Remarks:</td>
			<td class="textField"><textarea name="remarks" cols="50" readonly><?php echo $remarks;?></textarea></td>				
		</tr>
		<tr>
			<td class="textLabel">Cost Center:</td>
			<td class="textField"><input type="text" class="field" name="cost_center" id="cost_center" spellcheck="false" tabindex="1" value="<?php echo $cost_center; ?>" readonly></td>				
		</tr>
	</tbody>
</table>
</div>
<div class="headerText">Annual Procurement Plan Details</div>
<div class="headerText">
<input type="button" class="tabs selected" name="ITEM-BASE" value=" ITEM-BASED ">
<input type="button" class="tabs" name="COST-BASE" id="tabURL" value=" COST-BASED ">
<input type="button" class="tabs" name="NEW-RECORD" id="newRecord" value=" New Record ">
</div>
	<div class="filters">
	<table width="34%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
		<tr>
			<td>
				<?php 
				//Render them in drop down box	
				$selection = "";
				$selection .= "<select name='fieldnameItem' id='fieldnameItem'>";
				$selection .= "<option value=''>-- Please select --</option>";
				$requiredFilter = array('Code','Description','UOM','QTY','Unit_Cost','Total_Cost','Classification');
				$column = array_intersect($column,$requiredFilter);
				
				foreach($column as $fieldName){
					$fieldNameVal = str_replace('_', ' ', $fieldName);
					$selection .= "<option name='". $fieldName . "' value='" .$fieldName . "'>". $fieldNameVal . "</option>";
				}
				
				$selection .= "</select>";
				echo $selection;
				?>
				<select name="typeItem" id="typeItem">
					<option name="role" value="sw">Starts With</option>
					<option name="role" value="ew">Ends With</option>
					<option name="role" value="eq">Equals</option>
					<option name="role" value="co">Contains</option>
				</select>
				<input type="text" name="valueItem" id="valueItem" maxlength="50" tabindex="3">		
				<input type="submit" class="bold" name="searchItem" id="search" value=" Run ">&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	</div>
		<?php
			echo $tableView;
		?>
	<!--APP Section-->
	<a name="FormAnchor"></a>
	<div class="formDiv">
	<div class="headerText">Item Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
		<tbody>
			<tr>
				<td class="textLabel">Item Code: <i class="required">*</i></td>
				<td class="textField"><input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="code" id="code" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="item_val" id="item_val" spellcheck="false" tabindex="1" readonly><button name="ItemCode" onclick="valideopenerform2('R5_VIEW_PARTS_UOM_INFO')">...</button></td>			
				
				<td class="textLabel">Currency Code</td>
				<td class="textField">
						<?php 
						$tbname = "dbo.R5CURRENCIES";
						$tbfield = "CUR_CODE";
						//$tbfield2 = "CUR_DESC";
						$crudapp->optionValue2($conn,$tbname,$tbfield,"");
						?>
						
						<input type="hidden" class="field" name="CUR_CODE_VAL" id="CUR_CODE_VAL" spellcheck="false" tabindex="1" value="PHP">
				</td>	
			</tr>
			<tr>
				<td class="textLabel">Item Description: </td>
				<td class="textField"><input type="text" class="field" name="description" id="description" spellcheck="false" tabindex="1" readonly></td>				
				<td class="textLabel">Unit Cost:</td>
				<td class="textField" colspan="3"><input type="text" class="field" name="unit_cost" id="unit_cost" spellcheck="false" tabindex="1" value="0.00" onkeypress="return numbersonly(this, event)" onblur="round(this,2);">
				</td>				
			</tr>
			<tr>
				<td class="textLabel">Commodity:</td>
				<td class="textField">
				<input type="text" class="field" name="itemtype" id="itemtype" spellcheck="false" tabindex="1" readonly></td>								
				<td class="textLabel">Unit of Measure:</td>
				<td class="textField"><input type="text" class="field" name="uom" id="uom" spellcheck="false" tabindex="1" readonly></td>						
			</tr>
			<tr>
				
				<td class="textLabel">GL Account:</td>
				<td class="textField">
					<input type="text" class="field" name="itemGL" id="itemGL" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textLabel">Quantity:</td>
				<td class="textField"><input type="text" class="field" name="quantity" id="quantity" spellcheck="false" tabindex="1" value="0" readonly><input type="hidden" class="field" name="quantity_val" id="quantity_val" spellcheck="false" tabindex="1" value="0"></td>						
			</tr>
			<tr>
				
				<td class="textLabel">GL Description:</td>
				<td class="textField">
					<input type="text" class="field" name="gl_description" id="gl_description" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textLabel">Total Cost: </td>
				<td class="textField"><input type="text" class="field" name="cost" id="cost" spellcheck="false" tabindex="1" readonly></td>								
			</tr>
			<tr>
				
				<td class="textLabel">Item Classification:</td>
				<td class="textField">
					<input type="text" class="field" name="classification" id="classification" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textLabel">Last Price:</td>
				<td class="textField">
				<input type="text" class="field" name="PAR_LASTPRICE" id="PAR_LASTPRICE" spellcheck="false" tabindex="1" readonly>
					
				</td>	
			</tr>
			<tr>
				
				<td class="textLabel">IO Number:</td>
				<td class="textField">
						<input type="text" class="field" name="io_number" id="io_number" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textLabel"></td>
				<td class="textField">
				</td>	
			</tr>
		</tbody>
	</table>
	<!--DATE of NEED-->
	
	<table border="1" class="schedule">
	<tr>
		<th>Jan</th>
		<th>Feb</th>
		<th>Mar</th>
		<th>Apr</th>
		<th>May</th>
		<th>Jun</th>
		<th>Jul</th>
		<th>Aug</th>
		<th>Sept</th>
		<th>Oct</th>
		<th>Nov</th>
		<th>Dec</th>
	</tr>
	<tr>
		<td><input type="text" class="scheduleField" name="january" id="january" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="february" id="february" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="march" id="march" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="april" id="april" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="may" id="may" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="june" id="june" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="july" id="july" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="august" id="august" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="september" id="september" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="october" id="october" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="november" id="november" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="december" id="december" spellcheck="false" tabindex="1" value="0"></td>
	</tr>
	</table>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit" id="submit" value=" Save ">&nbsp;&nbsp;
		<input type="button" value=" Clear " Onclick="cancel(this.form)">&nbsp;&nbsp;
	</div>
	</div>
</div>
</form>
</body>
</html>  
