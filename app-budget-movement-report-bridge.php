<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");

//Generate Object
$crudapp = new crudClass();

$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$password = $_GET['password'];
$year = date("Y"); 
$usercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
$userinfocnd = "USR_CODE LIKE '$user'";
$userinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$usercolumn,$userinfocnd);

$mrccode = $userinfo[0]['MRC_CODE'];
$orgcode = $userinfo[0]['ORG_CODE'];

header("Location:http://eamqas.fdcutilities.local:8080/crn/cgi-bin/mod_cognos.dll?b_action=cognosViewer&ui.action=run&ui.object=%2fcontent%2ffolder%5b%40name%3d%27DS_MP_1%27%5d%2freport%5b%40name%3d%27REALLOCATION_SUPPLEMENT%27%5d&ui.name=REALLOCATION_SUPPLEMENT&run.outputFormat=PDF&run.prompt=true&CAMUsername=R5&CAMPassword=R5&p_Department=$mrccode&p_Org=$orgcode&p_UName=$user");
?>