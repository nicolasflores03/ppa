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
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$ORG_CODE = @$_GET['ORG_CODE'];
$yr = @$_GET['yr'];

//VARIABLES
$errorFlag = false;
$errorMessage = "";

//ITEM BASED
if ($user !=""){
$year = date("Y"); 

	if ($yr != ""){
	$year = $yr;
	}else{
	$year = $year;
	}

	if (isset($_POST['continue'])){
	$year_budget = $_POST['year_budget'];
	$ORG_CODE = $_POST['ORG_CODE'];
	
	$reference_no = $crudapp->readREF($conn,"R5_PROJECT_VERSION");
	$reference_no = $reference_no + 1;

//GET BUDGET DEADLINE
$deadlinefilter = "budget_year = '$year_budget' AND isActive = '1'";
$deadlinecolumn = $crudapp->readColumn($conn,"R5_DEADLINE_MAINTENANCE");
$deadlineinfo = $crudapp->listTable($conn,"R5_DEADLINE_MAINTENANCE",$deadlinecolumn,$deadlinefilter);
$deadlinemonth = @$deadlineinfo[0]['month'];
$deadlinedate = @$deadlineinfo[0]['date'];
$deadlineyear = @$deadlineinfo[0]['year'];

$nodeadline = 0;
$expired = 0;
if ($deadlinemonth != ""){
$expiration = $deadlinemonth."/".$deadlinedate."/".$deadlineyear;
$expiration_orig = str_replace(" ","",$expiration);
//echo $expiration;
$expiration = date("m/d/Y", strtotime($expiration_orig));
//Check if current date is greater than or equal the expiration date
$today = date("m/d/Y");

$today = new DateTime($today);
$expiration = new DateTime($expiration);

if ($today > $expiration){
$expired = 1;
}else{
$expired = 0;
}
}else{
$nodeadline = 1;
}

	
//Validation
if ($ORG_CODE == ""){
$errorMessage .= 'Please select Organization.\n\n';
$errorFlag = true;
}
		
//Validation
if ($year_budget == ""){
$errorMessage .= 'Please select a year budget.\n\n';
$errorFlag = true;
}

//Validation
if ($expired > 0){
$errorMessage .= 'Budget time expired!.\n\n';
$errorFlag = true;
}

//Validation
if ($nodeadline > 0){
$errorMessage .= 'No Deadline Set for this budget year!.\n\n';
$errorFlag = true;
}

if(!$errorFlag){	
		
	$version = 1;
		$today = date("m/d/Y H:i");	
		$data = array("reference_no"=>$reference_no,"ORG_CODE"=>$ORG_CODE,"year_budget"=>$year_budget,"status"=>"Unfinish","version"=>$version,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);	
		$table = "R5_PROJECT_VERSION";
		
			$result = $crudapp->insertRecord($conn,$data,$table);
		
			if( $result == 1) {
				sqlsrv_commit( $conn );
				//echo "Transaction committed.<br />";
			} else {
				sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
			}
			header("Location:app-project-lines.php?login=".$user."&year=".$year_budget."&reference_no=".$reference_no."&version=".$version);
}else{
	echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
}
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
 <link rel="stylesheet" href="css/jquery-ui.css">
<script src="js/jquery.min.js">
</script>
<script src="js/jquery-ui.js">
</script>
<script>
$(document).ready(function(){
	$("#year_budget").val("<?php echo $year;?>");
	$("#ORG_CODE").val("<?php echo $ORG_CODE;?>");
	
	var orgCount = $('#ORG_CODE option').size();
	
var org = $("#ORG_CODE").val();
if (org == ""){	
	if (orgCount == 2){
		$("#ORG_CODE")[0].selectedIndex=1;
	}
}

	
	$("#continue_tmp").click(function() {
		var r=confirm("Are you sure you want to create a new verson of project APP?");
		if (r==true){
				$("#continue").click();
		}
	});
	

	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();;
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&yr=$yr&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
	$("#year_budget").change(function() {
		var yr = $(this).val();;
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&ORG_CODE=$ORG_CODE&yr="; ?>'+yr; 
	});
});
</script>
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&yr=".$year."&ORG_CODE=".$ORG_CODE?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">APP Project List</div></div>
<div class="actionBar">
	<div class="divText">
	<!--<img src="images/toolbar_previous.png" name="back_tmp" id="back_tmp" align="absmiddle">-->
		<input type="button" class="bold" name="continue_tmp" id="continue_tmp" value=" Continue ">
		<div class="hidden">
			<input type="submit" class="bold" name="back" id="back" value=" Back ">
			<input type="submit" class="bold" name="continue" id="continue" value=" continue ">
		</div>
	</div>
</div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField">
			<input type="hidden" class="field" name="ref_no" id="ref_no" spellcheck="false" tabindex="1" value= "<?php echo $reference_no;?>">
					<?php 
						$tbname = "R5_VIEW_USERINFO";
						$tbfield = "DISTINCT(ORG_CODE)";
						$tbfield2 = "ORG_DESC";
						$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user'");
					?>
			</td>			
		</tr>
		<tr>
			<td class="textLabel">Year Budget:</td>
				<td class="textField">
					<select name="year_budget" id="year_budget">
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
	</tbody>
</table>
</body>
</html>