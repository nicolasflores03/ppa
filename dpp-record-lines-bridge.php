<?php
$year = $_GET['year'];
$reference_no = $_GET['reference_no'];
$version = $_GET['version'];
$user = $_GET['login'];

//Include external Files
include("include/connect.php");
include("class/crud.php");
include("class/object.php");

//Generate Object
$crudapp = new crudClass();
$filterapp = new filterClass();


//GET status Based on reference_no,dept,org,year
$dppfilter = "year_budget = '$year' AND reference_no = '$reference_no' AND version = $version";
$dppcolumn = $crudapp->readColumn($conn,"R5_DPP_VERSION");
$dppinfo = $crudapp->listTable($conn,"R5_DPP_VERSION",$dppcolumn,$dppfilter);
$status = $dppinfo[0]['status'];
$remarks = $dppinfo[0]['remarks'];
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
var status = "<?php echo $status; ?>";
var user = "<?php echo $user; ?>";
var year = "<?php echo $year; ?>";
var version = "<?php echo $version; ?>";
var reference_no = "<?php echo $reference_no; ?>";
	if (status == "Revision Request"){
	//Recreate DPP with a new version?
	}else{
	alert("MNOT");
	window.location = "dpp-record-lines.php?login="+user+"&year="+year+"&version="+version+"&reference_no="+reference_no;
	}
});
</script>
</head>
<body>
</body>
</html>  
