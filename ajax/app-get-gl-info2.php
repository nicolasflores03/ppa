<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$CMD_CODE=$_GET["CMD_CODE"];
$crudapp = new crudClass();
$condition = "WHERE CMD_CODE LIKE '%$CMD_CODE%'";
$data =array('id', 'GL_Code','CMD_CODE','CMD_DESC','GL_Description','category');
$jsonProjectInfo = $crudapp->readRecord2($conn,$data,"R5_VIEW_COMMODITIES",$condition);
echo $jsonProjectInfo;
?>
