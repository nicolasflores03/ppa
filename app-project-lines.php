<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");
include("class/object.php");


//Generate Object
$crudapp = new crudClass();
$filterapp = new filterClass();

//VARIABLES
$errorFlag = false;
$errorMessage = "";
$year = $_GET['year'];
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$version = $_GET['version'];
$reference_no = @$_GET['reference_no'];

//GET SAM
$samfilter = "USR_CODE = '$user'";
$samcolumn = $crudapp->readColumn($conn,"R5_CUSTOM_SAM");
$saminfo = $crudapp->listTable($conn,"R5_CUSTOM_SAM",$samcolumn,$samfilter);
$bo = @$saminfo[0]['BO'];
$fi = @$saminfo[0]['FI'];
$dh = @$saminfo[0]['DH'];
$cfo =@$saminfo[0]['CFO'];


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
$dppcolumn = $crudapp->readColumn($conn,"R5_VIEW_PROJECT_VERSION");
$dppinfo = $crudapp->listTable($conn,"R5_VIEW_PROJECT_VERSION",$dppcolumn,$dppfilter);
$status = $dppinfo[0]['status'];
$remarks = $dppinfo[0]['remarks'];
$orgcode = $dppinfo[0]['ORG_CODE'];

$expired = 0;
$stats = str_replace(" ","",$status);
if ($today > $expiration && $stats == "Unfinish"){
$expired = 1;
}else{
$expired = 0;
}


$filter = array();
$cnd = "id IN (SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = '$reference_no' AND version = '$version')";

$column = $crudapp->readColumn($conn,"R5_EAM_APP_PROJECTBASE_LINES");
$project_code = $crudapp->readID($conn,"R5_EAM_APP_PROJECTBASE_LINES");
$project_code = $project_code + 1;
$requiredField = array('project_code','description','budget_amount','id');
$column = array_intersect($column,$requiredField);
$listView = $crudapp->listTable($conn,"R5_EAM_APP_PROJECTBASE_LINES",$column,$cnd);
$tableView = $filterapp->filterViewURL($conn,$column,$listView,$filter,"id");

if (isset($_POST['search'])){
$fieldname = $_POST['fieldname'];
$value = $_POST['value'];
$type = $_POST['type'];
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

//Save Project to APP
if (isset($_POST['save'])){
$reference_no = $_POST['ref_no'];
$save = $crudapp->saveProjectApp($conn,$reference_no,$version);

	header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=You have successfully saved the records!");
}

//Back Project APP
if (isset($_POST['back'])){
$ORG_CODE = $_POST['ORG_CODE'];
//$year_budget = $_POST['year_budget'];
$back = $crudapp->backProjApp($conn,$ORG_CODE,$year);

	header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version);
}

//COPY Project APP
if (isset($_POST['copy'])){
$ORG_CODE = $_POST['ORG_CODE'];
$reference_no = $_POST['ref_no'];
$createCopy = $crudapp->createProjectCopy($conn,$ORG_CODE,$reference_no,$year,$version,$user);
sqlsrv_commit( $conn );
header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$createCopy);
}

//Endorsed Project APP
if (isset($_POST['endorsement'])){
$ORG_CODE = $_POST['ORG_CODE'];
//$year_budget = $_POST['year_budget'];
$ref_no = $_POST['ref_no'];
$data = array($ORG_CODE,$year,$ref_no,$version);
$endorse = $crudapp->endorseProjectApp($conn,$data);

	header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=You have successfully endorsed this APP!");
}

//Insert milestone
if (isset($_POST['submit'])){
$id = $_POST['id'];
$ref_no = $_POST['ref_no'];
$project_code = $_POST['project_code'];
$description = $_POST['description'];


//Check if same FI > 1
$cnd3 = "WHERE project_code = '$project_code' AND reference_no = '$ref_no'";
$Ctr = $crudapp->matchRecord2($conn,"R5_VIEW_PROJECT_LINES",'rowid',$cnd3);
if ($Ctr > 0 && $id == ""){
$errorMessage .= 'Project already exist for this budget year.\n\n';
$errorFlag = true;
}



//Validation
if ($project_code == ""){
$errorMessage .= 'Please select a Project Code.\n\n';
$errorFlag = true;
}


if(!$errorFlag){

$condition = "WHERE project_code LIKE '$project_code' AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = '$ref_no' AND version = '$version')";
//echo $condition;
//Check if Project Exist
$projectCtr = $crudapp->matchRecord2($conn,"R5_EAM_APP_PROJECTBASE_LINES","project_code",$condition);

$table = "R5_EAM_APP_PROJECTBASE_LINES";
$tableBridge = "R5_EAM_DPP_PROJECTBASE_BRIDGE";

//Project Base ID
$record_id = $crudapp->readID($conn,"R5_EAM_APP_PROJECTBASE_LINES");
$record_id = $record_id + 1;

$data = array("id"=>$record_id,"project_code"=>$project_code,"description"=>$description,"saveFlag"=>0,"version"=>1);
$dataUpdate = array("project_code"=>$project_code,"description"=>$description,"saveFlag"=>0);	
$dataBridge = array("rowid"=>$record_id,"reference_no"=>$reference_no,"version"=>$version);	

	if ($id != ""){
		//Insert Record to Audit by Benjie Manalaysay 3/28/2016
		$today2 = date("m/d/Y H:i");	
		$auditData = array("record_id"=>$id,"updatedBy"=>$user,"updatedAt"=>$today2,"table_name"=>$table,"update_type"=>"Edit");	
		$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_APP_LINES");
		
		
		if ($projectCtr > 0){

		}
		
		//Check if it has previous version
		$checkLineItemStatus = $crudapp->getLineVersionInfo($conn,$table,$id);
		if ($checkLineItemStatus > 0){
			$recVersion = $crudapp->readVersion($conn,"R5_EAM_APP_PROJECTBASE_LINES","id = '$id'");
			$recVersion = $recVersion + 1;
			$dataNew = array("id"=>$record_id,"project_code"=>$project_code,"description"=>$description,"saveFlag"=>0,"version"=>$recVersion);
			$resultNew = $crudapp->insertRecord($conn,$dataNew,$table);
			$cnd = "reference_no = '$ref_no' AND rowid = '$id' AND version =$version";
			$result3New = $crudapp->updateRecord2($conn,$dataBridge,$tableBridge,$cnd);
			
			//Update Milestone
			if($resultNew){
			$result2 = 0;
				$table2 = "R5_EAM_APP_PROJECTBASE_MILESTONE";
				if (@$_POST['milestone']){
					for ( $i=0;$i<count($_POST['milestone']);$i++) {
						$milestone = $_POST['milestone'][$i];
						$Jan = $_POST['Jan'][$i];
						$Jan = str_replace(",","",$Jan);
						$Feb = $_POST['Feb'][$i];
						$Feb = str_replace(",","",$Feb);
						$Mar = $_POST['Mar'][$i];
						$Mar = str_replace(",","",$Mar);
						$Apr = $_POST['Apr'][$i];
						$Apr = str_replace(",","",$Apr);
						$May = $_POST['May'][$i];
						$May = str_replace(",","",$May);
						$Jun = $_POST['Jun'][$i];
						$Jun = str_replace(",","",$Jun);
						$Jul = $_POST['Jul'][$i];
						$Jul = str_replace(",","",$Jul);
						$Aug = $_POST['Aug'][$i];
						$Aug = str_replace(",","",$Aug);
						$Sep = $_POST['Sep'][$i];
						$Sep = str_replace(",","",$Sep);
						$Oct = $_POST['Oct'][$i];
						$Oct = str_replace(",","",$Oct);
						$Nov = $_POST['Nov'][$i];
						$Nov = str_replace(",","",$Nov);
						$Dec = $_POST['Dec'][$i];
						$Dec = str_replace(",","",$Dec);
						$budget_amount = $Jan + $Feb + $Mar + $Apr + $May + $Jun + $Jul + $Aug + $Sep + $Oct + $Nov + $Dec;
						$data2 = array("id"=>$record_id,"milestone"=>$milestone,"Jan"=>$Jan,"Feb"=>$Feb,"Mar"=>$Mar,"Apr"=>$Apr,"May"=>$May,"Jun"=>$Jun,"Jul"=>$Jul,"Aug"=>$Aug,"Sep"=>$Sep,"Oct"=>$Oct,"Nov"=>$Nov,"Dec"=>$Dec,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"saveFlag"=>1);	
						$result2 = $crudapp->insertRecord($conn,$data2,$table2);
					}	
				}
				
				if (@$_POST['updatemilestone']){
					for ( $i=0;$i<count($_POST['updatemilestone']);$i++) {
						$milestoneID = $_POST['updateid'][$i];
						$milestone = $_POST['updatemilestone'][$i];
						$Jan = $_POST['updateJan'][$i];
						$Jan = str_replace(",","",$Jan);
						$Feb = $_POST['updateFeb'][$i];
						$Feb = str_replace(",","",$Feb);
						$Mar = $_POST['updateMar'][$i];
						$Mar = str_replace(",","",$Mar);
						$Apr = $_POST['updateApr'][$i];
						$Apr = str_replace(",","",$Apr);
						$May = $_POST['updateMay'][$i];
						$May = str_replace(",","",$May);
						$Jun = $_POST['updateJun'][$i];
						$Jun = str_replace(",","",$Jun);
						$Jul = $_POST['updateJul'][$i];
						$Jul = str_replace(",","",$Jul);
						$Aug = $_POST['updateAug'][$i];
						$Aug = str_replace(",","",$Aug);
						$Sep = $_POST['updateSep'][$i];
						$Sep = str_replace(",","",$Sep);
						$Oct = $_POST['updateOct'][$i];
						$Oct = str_replace(",","",$Oct);
						$Nov = $_POST['updateNov'][$i];
						$Nov = str_replace(",","",$Nov);
						$Dec = $_POST['updateDec'][$i];
						$Dec = str_replace(",","",$Dec);
						$budget_amount = $Jan + $Feb + $Mar + $Apr + $May + $Jun + $Jul + $Aug + $Sep + $Oct + $Nov + $Dec;
						$data2 = array("id"=>$record_id,"milestone"=>$milestone,"Jan"=>$Jan,"Feb"=>$Feb,"Mar"=>$Mar,"Apr"=>$Apr,"May"=>$May,"Jun"=>$Jun,"Jul"=>$Jul,"Aug"=>$Aug,"Sep"=>$Sep,"Oct"=>$Oct,"Nov"=>$Nov,"Dec"=>$Dec,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"saveFlag"=>1);	
						$result3 = $crudapp->insertRecord($conn,$data2,$table2,"milestoneID",$milestoneID);
					}	
				}
				
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=Record has been successfully inserted!");
			}
			
		}else{
			$result = $crudapp->updateRecord($conn,$dataUpdate,$table,"id",$id);
			
			//Update Milestone
			if($result){
			$result2 = 0;
				$table2 = "R5_EAM_APP_PROJECTBASE_MILESTONE";
				if (@$_POST['milestone']){
					for ( $i=0;$i<count($_POST['milestone']);$i++) {
						$milestone = $_POST['milestone'][$i];
						$Jan = $_POST['Jan'][$i];
						$Jan = str_replace(",","",$Jan);
						$Feb = $_POST['Feb'][$i];
						$Feb = str_replace(",","",$Feb);
						$Mar = $_POST['Mar'][$i];
						$Mar = str_replace(",","",$Mar);
						$Apr = $_POST['Apr'][$i];
						$Apr = str_replace(",","",$Apr);
						$May = $_POST['May'][$i];
						$May = str_replace(",","",$May);
						$Jun = $_POST['Jun'][$i];
						$Jun = str_replace(",","",$Jun);
						$Jul = $_POST['Jul'][$i];
						$Jul = str_replace(",","",$Jul);
						$Aug = $_POST['Aug'][$i];
						$Aug = str_replace(",","",$Aug);
						$Sep = $_POST['Sep'][$i];
						$Sep = str_replace(",","",$Sep);
						$Oct = $_POST['Oct'][$i];
						$Oct = str_replace(",","",$Oct);
						$Nov = $_POST['Nov'][$i];
						$Nov = str_replace(",","",$Nov);
						$Dec = $_POST['Dec'][$i];
						$Dec = str_replace(",","",$Dec);
						$budget_amount = $Jan + $Feb + $Mar + $Apr + $May + $Jun + $Jul + $Aug + $Sep + $Oct + $Nov + $Dec;
						$data2 = array("id"=>$id,"milestone"=>$milestone,"Jan"=>$Jan,"Feb"=>$Feb,"Mar"=>$Mar,"Apr"=>$Apr,"May"=>$May,"Jun"=>$Jun,"Jul"=>$Jul,"Aug"=>$Aug,"Sep"=>$Sep,"Oct"=>$Oct,"Nov"=>$Nov,"Dec"=>$Dec,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"saveFlag"=>1);	
						$result2 = $crudapp->insertRecord($conn,$data2,$table2);
					}	
				}
				
				if (@$_POST['updatemilestone']){
					for ( $i=0;$i<count($_POST['updatemilestone']);$i++) {
						$milestoneID = $_POST['updateid'][$i];
						$milestone = $_POST['updatemilestone'][$i];
						$Jan = $_POST['updateJan'][$i];
						$Jan = str_replace(",","",$Jan);
						$Feb = $_POST['updateFeb'][$i];
						$Feb = str_replace(",","",$Feb);
						$Mar = $_POST['updateMar'][$i];
						$Mar = str_replace(",","",$Mar);
						$Apr = $_POST['updateApr'][$i];
						$Apr = str_replace(",","",$Apr);
						$May = $_POST['updateMay'][$i];
						$May = str_replace(",","",$May);
						$Jun = $_POST['updateJun'][$i];
						$Jun = str_replace(",","",$Jun);
						$Jul = $_POST['updateJul'][$i];
						$Jul = str_replace(",","",$Jul);
						$Aug = $_POST['updateAug'][$i];
						$Aug = str_replace(",","",$Aug);
						$Sep = $_POST['updateSep'][$i];
						$Sep = str_replace(",","",$Sep);
						$Oct = $_POST['updateOct'][$i];
						$Oct = str_replace(",","",$Oct);
						$Nov = $_POST['updateNov'][$i];
						$Nov = str_replace(",","",$Nov);
						$Dec = $_POST['updateDec'][$i];
						$Dec = str_replace(",","",$Dec);
						$budget_amount = $Jan + $Feb + $Mar + $Apr + $May + $Jun + $Jul + $Aug + $Sep + $Oct + $Nov + $Dec;
						$data2 = array("id"=>$id,"milestone"=>$milestone,"Jan"=>$Jan,"Feb"=>$Feb,"Mar"=>$Mar,"Apr"=>$Apr,"May"=>$May,"Jun"=>$Jun,"Jul"=>$Jul,"Aug"=>$Aug,"Sep"=>$Sep,"Oct"=>$Oct,"Nov"=>$Nov,"Dec"=>$Dec,"budget_amount"=>$budget_amount,"available"=>$budget_amount);	
						$result3 = $crudapp->updateRecord($conn,$data2,$table2,"milestoneID",$milestoneID);
					}	
				}
				
				header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=Record has been successfully Updated!");
			}
		}
	}else{
		$result = $crudapp->insertRecord($conn,$data,$table);	
		$resultBridge = $crudapp->insertRecord($conn,$dataBridge,$tableBridge);	
	//Insert Milestone
	if($result){
	$result2 = 0;
		$table2 = "R5_EAM_APP_PROJECTBASE_MILESTONE";
		if (@$_POST['milestone']){
			for ( $i=0;$i<count($_POST['milestone']);$i++) {
				$milestone = $_POST['milestone'][$i];
				$Jan = $_POST['Jan'][$i];
				$Jan = str_replace(",","",$Jan);
				$Feb = $_POST['Feb'][$i];
				$Feb = str_replace(",","",$Feb);
				$Mar = $_POST['Mar'][$i];
				$Mar = str_replace(",","",$Mar);
				$Apr = $_POST['Apr'][$i];
				$Apr = str_replace(",","",$Apr);
				$May = $_POST['May'][$i];
				$May = str_replace(",","",$May);
				$Jun = $_POST['Jun'][$i];
				$Jun = str_replace(",","",$Jun);
				$Jul = $_POST['Jul'][$i];
				$Jul = str_replace(",","",$Jul);
				$Aug = $_POST['Aug'][$i];
				$Aug = str_replace(",","",$Aug);
				$Sep = $_POST['Sep'][$i];
				$Sep = str_replace(",","",$Sep);
				$Oct = $_POST['Oct'][$i];
				$Oct = str_replace(",","",$Oct);
				$Nov = $_POST['Nov'][$i];
				$Nov = str_replace(",","",$Nov);
				$Dec = $_POST['Dec'][$i];
				$Dec = str_replace(",","",$Dec);
				$budget_amount = $Jan + $Feb + $Mar + $Apr + $May + $Jun + $Jul + $Aug + $Sep + $Oct + $Nov + $Dec;
				$data2 = array("id"=>$record_id,"milestone"=>$milestone,"Jan"=>$Jan,"Feb"=>$Feb,"Mar"=>$Mar,"Apr"=>$Apr,"May"=>$May,"Jun"=>$Jun,"Jul"=>$Jul,"Aug"=>$Aug,"Sep"=>$Sep,"Oct"=>$Oct,"Nov"=>$Nov,"Dec"=>$Dec,"budget_amount"=>$budget_amount,"available"=>$budget_amount,"saveFlag"=>1);	
				$result2 = $crudapp->insertRecord($conn,$data2,$table2);
			}	
		}
		header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."&res=pass&msg=Record has been successfully inserted!#FormAnchor");
	}
	}
	
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
.description{
text-align:left;
padding-left: 20px;
}
.itemType {
width: 1195px;
overflow-x: scroll;
}
.scheduleField {
	width: 100px;
}
</style>
<script>
function onclickEvent(id){
$("#id").val(id);
getProjectInfo(id);
getMilestoneInfo(id);
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
	alert("here3");
		return result;
	}	
}

//EAM TABLE
function valideopenerform2(obj){	

	var project_code = $('#project_code').val();
	var org_code = $('#ORG_CODE').val();
	var description = $('#description').val();
	var id = $('#id').val();

			var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
		
var popup= window.open('popup-project.php?hash='+text+'&obj='+obj+'&project_code='+project_code+'&description='+description+'&id='+id+'&org_code='+org_code+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}

function getProjectInfo(id){
//HASH
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
	 var project_code = json['project_code'];
	 var description = json['description'];
	 $('#project_code').val(project_code);
	 $('#description').val(description);
    }
  }
xmlhttp.open("GET","ajax/app-get-project-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

function getMilestoneInfo(id){
//HASH
var reference_no = "<?php echo $reference_no; ?>";
var version = "<?php echo $version; ?>";
var status = "<?php echo $status; ?>";
var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
$('#projectmilestone').html("<tr><th>Milestone</th>"+
			"<th>Jan</th>"+
			"<th>Feb</th>"+
			"<th>Mar</th>"+
			"<th>Apr</th>"+
			"<th>May</th>"+
			"<th>Jun</th>"+
			"<th>Jul</th>"+
			"<th>Aug</th>"+
			"<th>Sept</th>"+
			"<th>Oct</th>"+
			"<th>Nov</th>"+
			"<th>Dec</th>"+
			"<th>Total</th><th>Action</th></tr><tr class='apps'>"+
			"<td><b>Overall Total</b></td>"+
			"<td class='JanTot'>0.00</td>"+
			"<td class='FebTot'>0.00</td>"+
			"<td class='MarTot'>0.00</td>"+
			"<td class='AprTot'>0.00</td>"+
			"<td class='MayTot'>0.00</td>"+
			"<td class='JunTot'>0.00</td>"+
			"<td class='JulTot'>0.00</td>"+
			"<td class='AugTot'>0.00</td>"+
			"<td class='SepTot'>0.00</td>"+
			"<td class='OctTot'>0.00</td>"+
			"<td class='NovTot'>0.00</td>"+
			"<td class='DecTot'>0.00</td>"+
			"<td class='overallTot'>0.00</td>"+
			"<td></td>"+
		"</tr>");
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	 $('.apps').before(xmlhttp.responseText);
	 getTotalupdate();
    }
  }
xmlhttp.open("GET","ajax/app-get-milestone-info.php?hash="+text+"&id="+id+"&status="+status,true);
xmlhttp.send();
}

function remCFupdate(id){
var updatedBy = "<?php echo $user; ?>";
//HASH
var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
$(".tr_"+id).remove();
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	 var result = xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","ajax/app-delete-project-milestone.php?hash="+text+"&id="+id+"&updatedBy="+updatedBy,true);
xmlhttp.send();
}


function deleteRecord(id){
var updatedBy = "<?php echo $user; ?>";
var obj = 'R5_EAM_APP_PROJECTBASE_LINES';
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
				alert('Record has been deleted successfully!');
				location.reload();
			}
		}
		xmlhttp2.open("GET","ajax/app-delete-record.php?hash="+text+"&id="+id+"&obj="+obj+"&updatedBy="+updatedBy,true);
		xmlhttp2.send();
	}
}

function getTotal(id){	



var name = $(id).attr('name');

var jan = /jan/i.test(name) // case insensitive
var feb = /feb/i.test(name) // case insensitive
var mar = /mar/i.test(name) // case insensitive
var apr = /apr/i.test(name) // case insensitive
var may = /may/i.test(name) // case insensitive
var jun = /jun/i.test(name) // case insensitive
var jul = /jul/i.test(name) // case insensitive
var aug = /aug/i.test(name) // case insensitive
var sep = /sep/i.test(name) // case insensitive
var oct = /oct/i.test(name) // case insensitive
var nov = /nov/i.test(name) // case insensitive
var dec = /dec/i.test(name) // case insensitive

//alert(jan+"-"+feb);
var monthTot = "";
if (jan){
monthTot = ".JanTot";
}else if (feb){
monthTot = ".FebTot";
}else if (mar){
monthTot = ".MarTot";
}else if (apr){
monthTot = ".AprTot";
}else if (may){
monthTot = ".MayTot";
}else if (jun){
monthTot = ".JunTot";
}else if (jul){
monthTot = ".JulTot";
}else if (aug){
monthTot = ".AugTot";
}else if (sep){
monthTot = ".SepTot";
}else if (oct){
monthTot = ".OctTot";
}else if (nov){
monthTot = ".NovTot";
}else if (dec){
monthTot = ".DecTot";
}

//alert(monthTot);
var total = 0;
var totalCreate = 0;
var totalUpdate = 0;
var initTotal = 0;
var initTotal2 = 0;
$("input[name='"+name+"']").each(function() {
	initTotal = $(this).val()
	initTotal = initTotal.replace(/,/g, '');
	totalCreate += parseFloat(initTotal);
});
$("input[name='update"+name+"']").each(function() {
	initTotal2 = $(this).val()
	initTotal2 = initTotal2.replace(/,/g, '');
	totalUpdate += parseFloat(initTotal2);
});
	total = totalCreate + totalUpdate;
	total = round2(total,2);
	//alert("RESULT:"+round2(total,2));
	$(monthTot).html(total);
var myclass = $(id).attr('class').split(' ')[1];
var num_id = $(id).attr('class').match(/\d+/)[0]
var total = 0;
var initTotal = 0;
	$("."+myclass).each(function(){
		initTotal = $(this).val()
		initTotal = initTotal.replace(/,/g, '');
		total += parseFloat(initTotal);
	});
	total = round2(total,2);
	if (myclass.indexOf("update") >= 0){
	$('.updatetotal'+num_id).val(total);
	}else{
	$('.total'+num_id).val(total);
	}
	
//Set Overall Total
	var month1 = $(".JanTot").text();
	month1 = month1.replace(/,/g, '');
	var month2 = $(".FebTot").text();
	month2 = month2.replace(/,/g, '');
	var month3 = $(".MarTot").text();
	month3 = month3.replace(/,/g, '');
	var month4 = $(".AprTot").text();
	month4 = month4.replace(/,/g, '');
	var month5 = $(".MayTot").text();
	month5 = month5.replace(/,/g, '');
	var month6 = $(".JunTot").text();
	month6 = month6.replace(/,/g, '');
	var month7 = $(".JulTot").text();
	month7 = month7.replace(/,/g, '');
	var month8 = $(".AugTot").text();
	month8 = month8.replace(/,/g, '');
	var month9 = $(".SepTot").text();
	month9 = month9.replace(/,/g, '');
	var month10 = $(".OctTot").text();
	month10 = month10.replace(/,/g, '');
	var month11 = $(".NovTot").text();
	month11 = month11.replace(/,/g, '');
	var month12 = $(".DecTot").text();
	month12 = month12.replace(/,/g, '');
	
	var overall = parseFloat(month1) + parseFloat(month2) + parseFloat(month3) + parseFloat(month4) + parseFloat(month5) + parseFloat(month6) + parseFloat(month7) + parseFloat(month8) + parseFloat(month9) + parseFloat(month10) + parseFloat(month11) + parseFloat(month12)
	overall = round2(overall,2);
	$('.overallTot').html(overall);
	
	
}


function getTotalupdate(){	
var month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',];
	for (var i in month) {
	var total = 0;
	var totalUpdate = 0;
	var initTotal2 = 0;
		$("input[name='update"+month[i]+"[]']").each(function() {
			initTotal2 = $(this).val()
			initTotal2 = initTotal2.replace(/,/g, '');
			totalUpdate += parseFloat(initTotal2);
		});
		
		total = totalUpdate;
		total = round2(total,2);
		$("."+month[i]+"Tot").html(total);
	}
	
//Set Overall Total
	var month1 = $(".JanTot").text();
	month1 = month1.replace(/,/g, '');
	var month2 = $(".FebTot").text();
	month2 = month2.replace(/,/g, '');
	var month3 = $(".MarTot").text();
	month3 = month3.replace(/,/g, '');
	var month4 = $(".AprTot").text();
	month4 = month4.replace(/,/g, '');
	var month5 = $(".MayTot").text();
	month5 = month5.replace(/,/g, '');
	var month6 = $(".JunTot").text();
	month6 = month6.replace(/,/g, '');
	var month7 = $(".JulTot").text();
	month7 = month7.replace(/,/g, '');
	var month8 = $(".AugTot").text();
	month8 = month8.replace(/,/g, '');
	var month9 = $(".SepTot").text();
	month9 = month9.replace(/,/g, '');
	var month10 = $(".OctTot").text();
	month10 = month10.replace(/,/g, '');
	var month11 = $(".NovTot").text();
	month11 = month11.replace(/,/g, '');
	var month12 = $(".DecTot").text();
	month12 = month12.replace(/,/g, '');
	
	var overall = parseFloat(month1) + parseFloat(month2) + parseFloat(month3) + parseFloat(month4) + parseFloat(month5) + parseFloat(month6) + parseFloat(month7) + parseFloat(month8) + parseFloat(month9) + parseFloat(month10) + parseFloat(month11) + parseFloat(month12)
	overall = round2(overall,2);
	$('.overallTot').html(overall);	
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

	var yr = "<?php echo $year;?>";
	$("#year_budget").val(yr);
	var status = "<?php echo $status; ?>";
	var version = "<?php echo $version; ?>";
	var user = "<?php echo $user; ?>";
	var year = "<?php echo $year; ?>";
	var reference_no = "<?php echo $reference_no; ?>";
	var bo = parseInt("<?php echo $bo; ?>");
	var fi = parseInt("<?php echo $fi; ?>");
	var dh = parseInt("<?php echo $dh; ?>");
	var cfo = parseInt("<?php echo $cfo; ?>");
	
	$('#endorsement_tmp').hide();
	
if(dh > 0){
	$('#endorsement_tmp').show();
}
	
//IF DEADLINE
var expired = "<?php echo $expired; ?>";
if(expired > 0 && version < 2){
	$('#endorsement_tmp').attr("disabled", true);
	$('.actionButtonCenter').hide();
	$('#copy_tmp').hide();
	$('#new_tmp').hide();
	$('.add_field').hide();
}else{	
	//status = status.trim();
	status = status.replace(/ /g,'');
	if(status=="ForEndorsement"){
		$('#endorsement_tmp').attr("disabled", true);
		$('.actionButtonCenter').hide();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.add_field').hide();
		$('.deleteButton').hide();
	}else if(status=="Endorsed"){
		$('#endorsement_tmp').attr("disabled", true);
		$('.actionButtonCenter').hide();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.add_field').hide();
		$('.deleteButton').hide();
	}else if(status=="RevisionRequest"){
		$('#endorsement_tmp').hide();
		$('#save_tmp').hide();
		$('.actionButtonCenter').hide();
		$('#copy_tmp').show();
		$('#new_tmp').show();
		$('.add_field').hide();
		$('.deleteButton').hide();
	}else{
		$('#endorsement_tmp').attr("disabled", false);
		$('.actionButtonCenter').show();
		$('#copy_tmp').hide();
		$('#new_tmp').hide();
		$('.add_field').show();
	}
}
		
	//Append Fields
	var ctr = 1;
	$('span.add_field').click(function(){
	var a = "<?php 
						$tbname = "R5PROJBUDCODES";
						$tbfield = "PBC";
						$crudapp->optionValueMilestone($conn);
		?>";
		$('.apps').before("<tr>"+
		"<td>"+a+"</td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Jan[]' id='january' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Feb[]' id='february' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Mar[]' id='march' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Apr[]' id='april' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='May[]' id='may' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Jun[]' id='june' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Jul[]' id='july' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Aug[]' id='august' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Sep[]' id='september' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Oct[]' id='october' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Nov[]' id='november' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField scheduleField"+ctr+"' name='Dec[]' id='december' spellcheck='false' tabindex='1' value='0.00' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>"+
		"<td><input type='text' class='scheduleField total"+ctr+"' name='total' id='total' spellcheck='false' tabindex='1' value='0.00' disabled></td><td><a href='javascript:void(0);' class='remCF'>Remove</a></td>"+
	"</tr>");
		ctr++;
	});
	
	$("#projectmilestone").on('click','.remCF',function(){
       $(this).parent().parent().remove();
    });
	
	
    var counter = 0;
    $(".list th").each(function(){
        var width = $('.list tr:last td:eq(' + counter + ')').width();
        $(".NewHeader tr").append(this);
        this.width = width;
        counter++;
    });
	//Save Button
	$("#back_tmp").click(function() {
		var r=confirm("Are you sure you want to go back to the main menu? \n\nNote: Please make sure to save first before leaving this page or else your updates on the APP will not be reflected.");
		if (r==true){
				$("#back").click();
		}
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
		var r=confirm("Are you sure you want to create a new Project APP?");
		if (r==true){
				window.location = "create-project-version.php?login="+user;
		}
	}else{
	alert("Budget time expired!");
	}
	});
	
	
	$("#newRecord").click(function() {
		window.location = "app-project-lines.php?login="+user+"&year="+year+"&reference_no="+reference_no+"&version="+version;
	});

	$("#copy_tmp").click(function() {
	$(this).attr("disabled", true);
	var expired = "<?php echo $expired; ?>";
	if(expired < 1 || (expired > 0 && version > 1)){
		var r=confirm("Are you sure you want to create a new Project APP?");
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
	
		var ref_no = $('#ref_no').val();
		var version = "<?php echo $version; ?>";
		var unsave = 0;
		//getUnsaved
		//HASH
		var text = "";
		var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
		for( var i=0; i < 5; i++ ){
		text += possible.charAt(Math.floor(Math.random() * possible.length));
		}	
		
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
							xmlhttp1.open("GET","ajax/projectbase-recordline-save.php?hash="+text+"&reference_no="+ref_no+"&version="+version,true);
							xmlhttp1.send();
						}else{
						$(this).attr("disabled", false);
							//$("#endorsement").click();
						}
					}
				}
			}
			xmlhttp.open("GET","ajax/projectbase-recordline-unsave.php?hash="+text+"&reference_no="+ref_no+"&version="+version,true);
			xmlhttp.send();
		//}else{
		//alert("Budget time expired!");
		//}
	});
	
	$(".scheduleField").change(function() {
		getTotalCost();
	});
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version."#FormAnchor"; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">APP Project List</div></div>
<div class="isa_info"><b>Deadline of budget submission is on: </b><?php echo $expiration_orig; ?></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="actionBar">
	<div class="divText">
		<!--<img src="images/toolbar_previous.png" name="back_tmp" id="back_tmp" align="absmiddle">-->
		<!--<img src="images/toolbar_save.png" name="save_tmp" id="save_tmp" align="absmiddle">-->
		<input type="button" class="bold" name="endorsement_tmp" id="endorsement_tmp" value=" For Endorsement ">
		<input type="button" class="bold" name="copy_tmp" id="copy_tmp" value=" Create a Copy from this Project APP ">
		<input type="button" class="bold" name="new_tmp" id="new_tmp" value=" Create New Project APP ">
		<div class="hidden">
			<input type="submit" class="bold" name="back" id="back" value=" Back ">
			<input type="submit" class="bold" name="save" id="save" value=" Save ">
			<input type="submit" class="bold" name="endorsement" id="endorsement" value=" For Endorsement ">
		    <input type="submit" class="bold" name="copy" id="copy" value=" Create a Copy from this Project APP ">
		</div>
	</div>
</div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organitzation:</td>
			<td class="textField"><input type="hidden" class="field" name="ref_no" id="ref_no" spellcheck="false" tabindex="1" value= "<?php echo $reference_no;?>"><input type="text" class="field" name="organization" id="organization" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['ORG_DESC'];?>" readonly><input type="hidden" class="field" name="ORG_CODE" id="ORG_CODE" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['ORG_CODE'];?>"></td>			
		</tr>
		<tr>		
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
			<!--<td class="textLabel">Cost Center:</td>
			<td class="textField"><input type="text" class="field" name="cost_center" id="cost_center" spellcheck="false" tabindex="1" disabled></td>-->				
			<td class="textLabel">Remarks:</td>
			<td class="textField"><textarea name="remarks" cols="50" readonly><?php echo $remarks;?></textarea></td>				
		</tr>
	</tbody>
</table>
</div>
<div class="headerText">Annual Procurement Plan Details</div>

<div class="headerText">
<input type="button" class="tabs" name="NEW-RECORD" id="newRecord" value=" New Record ">
</div>

<div class="filters">
  <table width="33%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
	<tr>
		<td>
			<?php 
			//Render them in drop down box	
			$selection = "";
			$selection .= "<select name='fieldname' id='fieldname'>";
			$selection .= "<option value=''>-- Please select --</option>";
			$requiredFilter = array('project_code','ORG_CODE','year_budget');
			$column = array_intersect($column,$requiredFilter);
			
			foreach($column as $fieldName){
				$fieldNameVal = str_replace('_', ' ', $fieldName);
				$selection .= "<option name='". $fieldName . "' value='" .$fieldName . "'>". $fieldNameVal . "</option>";
			}
			
			$selection .= "</select>";
			echo $selection;
			?>
			<select name="type" id="type">
				<option name="role" value="sw">Starts With</option>
				<option name="role" value="ew">Ends With</option>
				<option name="role" value="eq">Equals</option>
				<option name="role" value="co">Contains</option>
			</select>
			<input type="text" name="value" id="value" maxlength="50" tabindex="3">		
			<input type="submit" class="bold" name="search" id="search" value=" Run ">&nbsp;&nbsp;
		</td>
	</tr>
  </table>
</div>
	<?php
		echo $tableView;
	?>	
<!--PROJECT MILESTONE-->
<a name="FormAnchor"></a>
<div class="formDiv2">
<div class="headerText">Project Details</div>
<table class="procurement" border="0" cellspacing="5px" width="100%">
	<tbody>			
		<tr>
			<td class="textLabel">Project Code: <i class="required">*</i></td>
			<td class="textField">
			<input type="text" class="fieldLookUp" name="project_code" id="project_code" spellcheck="false" tabindex="1" readonly><button name="projectCode" onclick="valideopenerform2('R5PROJECTS')">...</button>			
			<input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1">
			</td>					
			<td class="textLabel">Description:</td>
			<td class="textField"><input type="text" class="field" name="description" id="description" spellcheck="false" tabindex="1" readonly></td>	
		</tr>
	</tbody>
</table>
<!--DATE of NEED-->
<div class="itemType">
	<!--<table id="projectmilestone" border="1" cellspacing="5px" width="100%" style="margin:0 auto;">
		<tr>
			<th width="40%">Milestone</th>
			<th width="40%">Amount</th>
			<th width="20%">Action</th>
		</tr>
	</table>-->
	
	
	<table border="1" class="schedule" id="projectmilestone">
		<tr>
			<th>Milestone</th>
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
			<th>Total</th>
			<th>Action</th>
		</tr>		
		<tr class="apps">
			<td><b>Overall Total</b></td>
			<td class="JanTot">0.00</td>
			<td class="FebTot">0.00</td>
			<td class="MarTot">0.00</td>
			<td class="AprTot">0.00</td>
			<td class="MayTot">0.00</td>
			<td class="JunTot">0.00</td>
			<td class="JulTot">0.00</td>
			<td class="AugTot">0.00</td>
			<td class="SepTot">0.00</td>
			<td class="OctTot">0.00</td>
			<td class="NovTot">0.00</td>
			<td class="DecTot">0.00</td>
			<td class="overallTot">0.00</td>
			<td></td>
		</tr>
	</table>
</div>
<p style="margin-left:5px;"><span class="url add_field">Add Milestone</span></p>
<!--Action Button-->
<div class="actionButtonCenter">
	<input type="submit" class="bold" name="submit" id="submit" value=" Submit ">&nbsp;&nbsp;
</div>
<div class="hidden">
	<input type="submit" class="bold" name="delete_milestone" id="delete_milestone" value=" Delete Milestone ">&nbsp;&nbsp;
</div>
</div>

</div>
</form>
<div class="sample"></div>
</body>
</html>  
