<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id', 'GL_Code','CMD_CODE','CMD_DESC','GL_Description','category','isActive');
$jsonProjectInfo = $crudapp->readRecord2($conn,$data,"R5_VIEW_COMMODITIES_ALL",$condition);
echo $jsonProjectInfo;
?>
