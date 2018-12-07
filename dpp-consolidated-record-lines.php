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
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$version = $_GET['version'];
$reference_no = @$_GET['reference_no'];

//GET USER INFO
$userfilter = "USR_CODE = '$user'";
$usercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
$userinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$usercolumn,$userfilter);
$mrccode = $userinfo[0]['MRC_CODE'];
$orgcode = $userinfo[0]['ORG_CODE'];

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
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&reference_no=".$reference_no."&version=".$version; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">Annual Procurement Plan</div></div>
<div class="actionBar">
	<div class="divText">
	<img src="images/toolbar_previous.png" name="back_tmp" id="back_tmp" align="absmiddle">
	<img src="images/toolbar_save.png" name="save_tmp" id="save_tmp" align="absmiddle">
	<input type="button" class="bold" name="endorsement_tmp" id="endorsement_tmp" value=" For Endorsement ">
		<div class="hidden">
			<input type="submit" class="bold" name="back" id="back" value=" Back ">
			<input type="submit" class="bold" name="save" id="save" value=" Save ">
			<input type="submit" class="bold" name="endorsement" id="endorsement" value=" For Endorsement ">
		</div>
	</div>
</div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField"><input type="hidden" class="field" name="ref_no" id="ref_no" spellcheck="false" tabindex="1" value= "<?php echo $reference_no;?>"><input type="text" class="field" name="organization" id="organization" spellcheck="false" tabindex="1" value= "<?php echo $userinfo[0]['ORG_DESC'];?>" readonly><input type="hidden" class="field" name="ORG_CODE" id="ORG_CODE" spellcheck="false" tabindex="1" value= "<?php echo $userinfo[0]['ORG_CODE'];?>"></td>			
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
			<td class="textField"><input type="text" class="field" name="department" id="department" spellcheck="false" tabindex="1" value= "<?php echo $userinfo[0]['MRC_DESC'];?>" readonly><input type="hidden" class="field" name="MRC_CODE" id="MRC_CODE" spellcheck="false" tabindex="1" value= "<?php echo $userinfo[0]['MRC_CODE'];?>"></td>				
		</tr>
		<tr>
			<td class="textLabel">Cost Center:</td>
			<td class="textField"><input type="text" class="field" name="cost_center" id="cost_center" spellcheck="false" tabindex="1" readonly></td>				
		</tr>
	</tbody>
</table>
</div>
<div class="headerText">Annual Procurement Plan Details</div>
</div>
</form>
</body>
</html>  
