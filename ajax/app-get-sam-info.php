<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id', 'ORG_CODE','USR_CODE','MRC_CODE','BO','DH','FI','CFO');
$jsonProjectInfo = $crudapp->readRecord2($conn,$data,"R5_CUSTOM_SAM",$condition);
echo $jsonProjectInfo;
?>
