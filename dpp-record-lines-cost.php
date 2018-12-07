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

$cndCost = "id IN (SELECT rowid FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE reference_no = '$reference_no' AND version = '$version') AND reference_no = '$reference_no' AND version = '$version'";

//COST BASE
$column2 = $crudapp->readColumn($conn,"R5_VIEW_COSTBASE_LINES");
$record_id2 = $crudapp->readID($conn,"R5_EAM_DPP_COSTBASE_LINES");
$record_id2 = $record_id2 + 1;
$requiredField2 = array('id','code','description','type','budget_amount','classification','Jan','Feb','Mar','Apr','may','Jun','Jul','Aug','Sept','Oct','Nov','Dec');
$column2 = array_intersect($column2,$requiredField2);
$listView2 = $crudapp->listTable($conn,"R5_VIEW_COSTBASE_LINES",$column2,$cndCost);
$tableView2 = $filterapp->filterViewURL($conn,$column2,$listView2,$filter,"id");

//costbase
if (isset($_POST['searchCost'])){
$fieldname = $_POST['fieldnameCost'];
$value = $_POST['valueCost'];
$type = $_POST['typeCost'];
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
		$tableView = $filterapp->filterViewURL($conn,$column2,$listView2,$filter,"id");
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

$condition = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE' AND year_budget = '$year' AND version = $version AND cost_center = '$cost_center' AND (status = 'For Endorsement' OR status = 'Endorsed')";

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


//COST BASED
if (isset($_POST['submit_cost'])){
$ref_no = $_POST['ref_no'];
$id = @$_POST['id_cost'];
$CMD_CODE = $_POST['CMD_CODE'];
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
//$year_budget = $_POST['year_budget'];
$description = $_POST['description_cost'];
$code = $_POST['code'];
$budget_amount = $_POST['budget_amount_cost'];
$budget_amount = str_replace(",","",$budget_amount);
$classification_cost = $_POST['classification_cost'];
$category_cost = $_POST['category_cost'];
$january = $_POST['january_cost'];
$february = $_POST['february_cost'];
$march =  $_POST['march_cost'];
$april =  $_POST['april_cost'];
$may =  $_POST['may_cost'];
$june = $_POST['june_cost'];
$july = $_POST['july_cost'];
$august = $_POST['august_cost'];
$september = $_POST['september_cost'];
$october = $_POST['october_cost'];
$november = $_POST['november_cost'];
$december = $_POST['december_cost'];
$type = $_POST['type'];
$unit_cost = $_POST['unit_cost'];
$unit_cost = str_replace(",","",$unit_cost);


//Check if same FI > 1
$cnd3 = "WHERE code = '$code' AND reference_no = '$ref_no' AND version = '$version'";
$Ctr = $crudapp->matchRecord2($conn,"R5_VIEW_COSTBASE_LINES",'id',$cnd3);
if ($Ctr > 0 && $id == ""){
$errorMessage .= 'Item already exist for this budget year.\n\n';
$errorFlag = true;
}

//Validation
if ($CMD_CODE == ""){
$errorMessage .= 'Please enter a Commodity code.\n\n';
$errorFlag = true;
}

if ($budget_amount == ""){
$errorMessage .= 'Please enter a Budget Amount.\n\n';
$errorFlag = true;
}

if (!is_numeric($budget_amount)){
$errorMessage .= 'Budget Amount must be numeric characters only.\n\n';
$errorFlag = true;
}


if (!is_numeric ($january) || !is_numeric ($february) || !is_numeric ($march) || !is_numeric ($april) || !is_numeric ($may) || !is_numeric ($june) || !is_numeric ($july) || !is_numeric ($august) || !is_numeric ($september) || !is_numeric ($october) || !is_numeric ($november) || !is_numeric ($december)){
$errorMessage .= 'Budget Month must be numeric characters only.\n\n';
$errorFlag = true;
}

$today = date("m/d/Y H:i");	
	if(!$errorFlag){
		$data = array("record_id"=>$record_id2,"id"=>$record_id2,"CMD_CODE"=>$CMD_CODE,"description"=>$description,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"classification"=>$classification_cost,"category"=>$category_cost,"saveFlag"=>0,"version"=>1,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user,"code"=>$code,"type"=>$type,"unit_cost"=>$unit_cost);	
		$data2 = array("id"=>$record_id2,"january"=>$january,"february"=>$february,
		"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
		"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
		$data5 = array("reference_no"=>$ref_no,"rowid"=>$record_id2,"version"=>$version);	
		
		$table = "R5_EAM_DPP_COSTBASE_LINES";
		$table2 = "R5_REF_COSTBASE_BUDGET_MONTH";
		$table3 = "R5_EAM_DPP_COSTBASE_BRIDGE";
		
		if($id != ""){
		//Check if it has previous version
		$checkLineItemStatus = $crudapp->getLineVersionInfo($conn,$table,$id);
			if ($checkLineItemStatus > 0){
				$recVersion = $crudapp->readVersion($conn,"R5_EAM_DPP_COSTBASE_LINES","id = '$id'");
				$recVersion = $recVersion + 1;
				$dataNew = array("record_id"=>$id,"id"=>$record_id2,"CMD_CODE"=>$CMD_CODE,"description"=>$description,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"classification"=>$classification_cost,"category"=>$category_cost,"saveFlag"=>0,"version"=>$recVersion,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user,"code"=>$code,"type"=>$type,"unit_cost"=>$unit_cost);	
				$resultNew = $crudapp->insertRecord($conn,$dataNew,$table);
				$result2New = $crudapp->insertRecord($conn,$data2,$table2);
				$cnd = "reference_no = '$ref_no' AND rowid = '$id' AND version =$version";
				$result3New = $crudapp->updateRecord2($conn,$data5,$table3,$cnd);
			}else{
				$data3 = array("CMD_CODE"=>$CMD_CODE,"description"=>$description,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"classification"=>$classification_cost,"category"=>$category_cost,"saveFlag"=>0,"updatedAt"=>$today,"updatedBy"=>$user,"code"=>$code,"type"=>$type,"unit_cost"=>$unit_cost);	
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

			//if( $result == 1 && $result2 == 1) {
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
.budget_amount{
text-align:right;
padding-right: 20px;
}
.description, .type{
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
$('#id_cost').val(id);
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
	 var CMD_CODE = json['CMD_CODE'];
	 CMD_CODE = CMD_CODE.replace(/ /g,'');
	 var CMD_DESC = json['CMD_DESC'];
	 var budget_amount = json['Budget_Amount'];
	 var io_number = json['io_number'];
	 var description = json['Description'];
	 var code = json['code'];
	 var classification = json['Classification'];
	 var category = json['category'];
	 var type = json['type'];
	 var unit_cost = json['unit_cost'];
	 
	 //category = category.trim();
	 category = category.replace(/ /g,'');
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
	 
	 $('#CMD_CODE').val(CMD_CODE);
	  $('#costCommodity').val(CMD_DESC);
	 $('#io_number').val(io_number);
	 $('#budget_amount_cost').val(budget_amount);
	 $('#description_cost').val(description);
	 $('#code').val(code);
	 $('#classification_cost').val(classification);
	 $('#category_cost').val(category);
	 $('#categoryDisp').val(category);
	 $('#january_cost').val(jan);
	 $('#february_cost').val(feb);
	 $('#march_cost').val(mar);
	 $('#april_cost').val(apr);
	 $('#may_cost').val(may);
	 $('#june_cost').val(jun);
	 $('#july_cost').val(jul);
	 $('#august_cost').val(aug);
	 $('#september_cost').val(sept);
	 $('#october_cost').val(oct);
	 $('#november_cost').val(nov);
	 $('#december_cost').val(dec);
	 $('#type').val(type);	
		$('#unit_cost').val(unit_cost);		 
	 getGLInfo(CMD_CODE);
    }
  }
xmlhttp.open("GET","ajax/app-get-costbase-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

//R5COMMODITIES TABLE
function valideopenerform2(obj){
	var description_cost = $('#description_cost').val();
	var id_cost = $('#id_cost').val();
	var code = $('#code').val();
	var budget_amount_cost = $('#budget_amount_cost').val();
	var classification_cost = $('#classification_cost').val();
	var CMD_CODE = $('#CMD_CODE').val();
	var costCommodity = $('#costCommodity').val();
	costCommodity = costCommodity.replace(/&/g, '%26');
	var january = $('#january_cost').val();
	var february = $('#february_cost').val();
	var march = $('#march_cost').val();
	var april = $('#april_cost').val();
	var may = $('#may_cost').val();
	var june = $('#june_cost').val();
	var july = $('#july_cost').val();
	var august = $('#august_cost').val();
	var september = $('#september_cost').val();
	var october = $('#october_cost').val();
	var november = $('#november_cost').val();
	var december = $('#december_cost').val();	
		var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
var popup= window.open('popup4.php?hash='+text+'&obj='+obj+'&budget_amount_cost='+budget_amount_cost+'&description_cost='+description_cost+'&costCommodity='+costCommodity+'&classification_cost='+classification_cost+''
+'&january='+january+'&february='+february+'&march='+march+'&april='+april+'&may='+may+'&june='+june+'&july='+july+''
+'&august='+august+'&september='+september+'&october='+october+'&november='+november+'&december='+december+'&code='+code+'&CMD_CODE='+CMD_CODE+'&id_cost='+id_cost+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}

//R5COMMODITIES TABLE
function valideopenerform3(obj){
	var description_cost = $('#description_cost').val();
	var code = $('#code').val();
	var id_cost = $('#id_cost').val();
	var budget_amount_cost = $('#budget_amount_cost').val();
	var classification_cost = $('#classification_cost').val();
	//var category_cost = $('#category_cost').val();
	var costCommodity = $('#costCommodity').val();
	var january = $('#january_cost').val();
	var february = $('#february_cost').val();
	var march = $('#march_cost').val();
	var april = $('#april_cost').val();
	var may = $('#may_cost').val();
	var june = $('#june_cost').val();
	var july = $('#july_cost').val();
	var august = $('#august_cost').val();
	var september = $('#september_cost').val();
	var october = $('#october_cost').val();
	var november = $('#november_cost').val();
	var december = $('#december_cost').val();
	var unit_cost = $('#unit_cost').val();
	var type = $('#type').val();

		var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
var popup= window.open('popup3.php?hash='+text+'&obj='+obj+'&budget_amount_cost='+budget_amount_cost+'&description_cost='+description_cost+'&costCommodity='+costCommodity+'&classification_cost='+classification_cost+''
+'&january='+january+'&february='+february+'&march='+march+'&april='+april+'&may='+may+'&june='+june+'&july='+july+''
+'&august='+august+'&september='+september+'&october='+october+'&november='+november+'&december='+december+'&code='+code+'&id_cost='+id_cost+'&type='+type+'&unit_cost='+unit_cost+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}

function getTotalCost(){
	var jan = $('#january_cost').val();
		var feb = $('#february_cost').val();
		var mar = $('#march_cost').val();
		var apr = $('#april_cost').val();
		var may = $('#may_cost').val();
		var jun = $('#june_cost').val();
		var jul = $('#july_cost').val();
		var aug = $('#august_cost').val();
		var sept = $('#september_cost').val();
		var oct = $('#october_cost').val();
		var nov = $('#november_cost').val();
		var dec = $('#december_cost').val();
		var unit_cost = $('#unit_cost').val();
		
		var qty = parseFloat(jan) + parseFloat(feb) + parseFloat(mar) + parseFloat(apr) + 
		parseFloat(may) + parseFloat(jun) + parseFloat(jul) + parseFloat(aug) + parseFloat(sept) + 
		parseFloat(oct) + parseFloat(nov) + parseFloat( dec);
		//alert($('#unit_cost_tmp').val());
		
		unit_cost = unit_cost.replace(/,/g, '');
		
		var total_cost = parseFloat(qty) * parseFloat(unit_cost);
		total_cost = round2(total_cost,2);
		$("#budget_amount_cost").val(total_cost);
}

function getItemInfo(){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var costCommodity = json['CMD_DESC'];
	 var classification = json['PAR_UDFCHAR07'];
	 var unit_cost = json['PAR_BASEPRICE'];
	 var gl = json['gl'];
	 var gl_description = json['gl_description'];
	 var PAR_COMMODITY = json['PAR_COMMODITY'];
	 
	 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 //alert(unit_cost);
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo substr($cost_center, 2);?>';
	 
	 if(unit_cost == null){
	 unit_cost = "0.00";
	 }
	 
	 
	 if (unit_cost > 0){
	   $('#unit_cost').attr('readonly', 'true'); // mark it as read only
	 }else{
	   $('#unit_cost').removeAttr('readonly'); // mark it as read only	 
	 }
	 
	 
	 $('#unit_cost').val(unit_cost);
	 //$('#unit_cost_tmp').val(unit_cost);
	 $('#costCommodity').val(costCommodity);
	 $('#CMD_CODE').val(PAR_COMMODITY);
	 getGLInfo(PAR_COMMODITY);
	 $('#classification_cost').val(classification);
	 $('#gl_code').val(gl);
	 $('#gl_description').val(gl_description);
	 $('#io_number').val(io_number);
    }
  }
xmlhttp.open("GET","ajax/app-record-create-cost.php?hash="+text+"&code="+code,true);
xmlhttp.send();
}


function getItemInfo2(){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var PAR_DESC = json['PAR_DESC'];
	 
	 $('#description_cost').val(PAR_DESC);
    }
  }
xmlhttp.open("GET","ajax/app-record-create-cost.php?hash="+text+"&code="+code,true);
xmlhttp.send();
}

function getGLInfo(CMD_CODE){
//alert(CMD_CODE);
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
	 var gl_code = json['GL_Code'];
	 var gl_description = json['GL_Description'];
	 var category = json['category'];
	 category = category.replace(/ /g, '');
	 $('#gl_code').val(gl_code);
	 $('#gl_description').val(gl_description);
	 $('#category_cost').val(category);
	 $('#categoryDisp').val(category);	 
	 
	 getIONumber();
    }
  }
xmlhttp.open("GET","ajax/app-get-gl-info2.php?hash="+text+"&CMD_CODE="+CMD_CODE,true);
xmlhttp.send();
}

function getIONumber(){
 var gl = $('#gl_code').val();
 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo substr($cost_center, 2);?>';
	$('#io_number').val(io_number);	
}


function deleteRecord(id){
	var version = "<?php echo $version; ?>";
	var reference_no = "<?php echo $reference_no; ?>";
	var updatedBy = "<?php echo $user; ?>";
	
var obj = 'R5_EAM_DPP_COSTBASE_LINES';
var obj2 = 'R5_EAM_DPP_COSTBASE_BRIDGE';
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
	
   /* var counter = 0;
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
	
	$("#budget_amount_cost").change(function() {
		$('#unit_cost').val("0.00");
		$('#january_cost').val("0");
		$('#february_cost').val("0");
		$('#march_cost').val("0");
		$('#april_cost').val("0");
		$('#may_cost').val("0");
		$('#june_cost').val("0");
		$('#july_cost').val("0");
		$('#august_cost').val("0");
		$('#september_cost').val("0");
		$('#october_cost').val("0");
		$('#november_cost').val("0");
		$('#december_cost').val("0");
	});
	
	$("#unit_cost").change(function() {
		$('#budget_amount_cost').val("0.00");
		getTotalCost();
	});
	
	$("#tabURL").click(function() {
		window.location = "dpp-record-lines-item.php?login="+user+"&year="+year+"&reference_no="+reference_no+"&version="+version;
	});
	
	$("#newRecord").click(function() {
		window.location = "dpp-record-lines-cost.php?login="+user+"&year="+year+"&reference_no="+reference_no+"&version="+version;
	});
	
	
	$("#save_tmp").click(function() {
	$(this).attr("disabled", true);
	var expired = "<?php echo $expired; ?>";
	if(expired < 1 || (expired > 0 && version > 1)){
		var r=confirm("Are you sure you want to save this APP?");
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
	var expired = "<?php echo $expired; ?>";
	if(expired < 1){
		var r=confirm("Are you sure you want to create a new APP?");
		if (r==true){
				window.location = "create-dpp-version.php?login="+user;
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
					var r=confirm("Do you want to save unsave items before proceeding to your endorsement?");
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
					var r=confirm("Do you want to save unsave items before proceeding on submission?");
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
		$(this).attr("disabled", false);
			//$("#endorsement").click();
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
			<input type="submit" class="bold" name="revise" id="revise" value=" Revision Request ">
			<input type="submit" class="bold" name="endorsement" id="endorsement" value=" For Endorsement ">
			<input type="submit" class="bold" name="submitted" id="submitted" value=" Submit ">
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
<input type="button" class="tabs" id="tabURL" name="ITEM-BASE" value=" ITEM-BASED ">
<input type="button" class="tabs selected" name="COST-BASE" value=" COST-BASED ">
<input type="button" class="tabs" name="NEW-RECORD" id="newRecord" value=" New Record ">
</div>
  	<div class="filters">
	<table width="34%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
		<tr>
			<td>
				<?php 
				//Render them in drop down box	
				$selection = "";
				$selection .= "<select name='fieldnameCost' id='fieldnameCost'>";
				$selection .= "<option value=''>-- Please select --</option>";
				$requiredFilter = array('Code','Description','Classification');
				$column = array_intersect($column2,$requiredFilter);
				
				foreach($column as $fieldName){
					$fieldNameVal = str_replace('_', ' ', $fieldName);
					$selection .= "<option name='". $fieldName . "' value='" .$fieldName . "'>". $fieldNameVal . "</option>";
				}
				
				$selection .= "</select>";
				echo $selection;
				?>
				<select name="typeCost" id="typeCost">
					<option name="role" value="sw">Starts With</option>
					<option name="role" value="ew">Ends With</option>
					<option name="role" value="eq">Equals</option>
					<option name="role" value="co">Contains</option>
				</select>
				<input type="text" name="valueCost" id="valueCost" maxlength="50" tabindex="3">		
				<input type="submit" class="bold" name="searchCost" id="search" value=" Run ">&nbsp;&nbsp;
			</td>
		</tr>
	</table>
	</div>
		<?php
			echo $tableView2;
		?>	
	<!--APP Section-->
	<a name="FormAnchor"></a>
	<div class="formDiv">
	<div class="headerText">Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
		<tbody>
			<tr>		
				<td class="textLabel">Cost-Based Item: <i class="required">*</i></td>
				<td class="textField">
				<input type="text" class="fieldLookUp" name="description_cost" id="description_cost" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="code" id="code" spellcheck="false" tabindex="1" readonly><button name="cmd" onclick="valideopenerform2('R5_VIEW_SERVICE_UOM_INFO')">...</button>		
				<input type="hidden" class="field" name="id_cost" id="id_cost" spellcheck="false" tabindex="1">			
				</td>				
				<td class="textLabel">IO Number:</td>
				<td class="textField" colspan="3"><input type="text" class="field" name="io_number" id="io_number" spellcheck="false" tabindex="1" readonly></td>
			</tr>
			<tr>		
				<td class="textLabel">Type:</td>
				<td class="textField">
					<input type="text" class="field" name="type" id="type" spellcheck="false" tabindex="1">
				</td>	
				
				<td class="textLabel">Service Type: <i class="required">*</i></td>
				<td class="textField">	
					<input type="text" class="fieldLookUp" name="costCommodity" id="costCommodity" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="CMD_CODE" id="CMD_CODE" spellcheck="false" tabindex="1" readonly><button name="cmd" onclick="valideopenerform3('R5_VIEW_COMMODITIES')">...</button>	
				</td>						
			</tr>
			<tr>	

				<td class="textLabel">GL Code: <i class="required">*</i></td>
				<td class="textField">
					<input type="text" class="field" name="gl_code" id="gl_code" spellcheck="false" tabindex="1" readonly>						
				</td>
				
			<td class="textLabel">Budget Amount: <i class="required">*</i></td>
				<td class="textField"><input type="text" class="field" name="budget_amount_cost" id="budget_amount_cost" spellcheck="false" tabindex="1" onkeypress="return numbersonly(this, event)" onblur="round(this,2);"></td>			
			</tr>
			<tr>
			<td class="textLabel">GL Description:</td>
			<td class="textField"><input type="text" class="field" name="gl_description" id="gl_description" spellcheck="false" tabindex="1" readonly></td>
							
				<td class="textLabel">Category: <i class="required">*</i></td>
				<td class="textField">
						<input type="hidden" class="field" name="category_cost" id="category_cost" spellcheck="false" tabindex="1">
						<input type="text" class="field" name="categoryDisp" id="categoryDisp" spellcheck="false" tabindex="1" readonly>
				</td>				
			</tr>
			<tr>

				<td class="textLabel">Classification:</td>
				<td class="textField">
					<select name="classification_cost" id="classification_cost">
						<option value=''>-- Please select --</option>
						<option name="classification" value="FDC">FDC</option>
						<option name="classification" value="Proprietary">Proprietary</option>
						<option name="classification" value="Non-Proprietary">Non-Proprietary</option>
					</select>
				</td>
			
				<td class="textLabel">Unit Cost</td>
				<td class="textField">
				<input type="text" class="field" name="unit_cost" id="unit_cost" spellcheck="false" tabindex="1" value="0.00" onkeypress="return numbersonly(this, event)" onblur="round(this,2);">
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
		<td><input type="text" class="scheduleField" name="january_cost" id="january_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="february_cost" id="february_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="march_cost" id="march_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="april_cost" id="april_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="may_cost" id="may_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="june_cost" id="june_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="july_cost" id="july_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="august_cost" id="august_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="september_cost" id="september_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="october_cost" id="october_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="november_cost" id="november_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="december_cost" id="december_cost" spellcheck="false" tabindex="1" value="0"></td>
	</tr>
	</table>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit_cost" id="submit" value=" Save ">&nbsp;&nbsp;
		<input type="button" value=" Clear " Onclick="cancel(this.form)">&nbsp;&nbsp;
	</div>
	</div>
	
</div>
</form>
</body>
</html>  
