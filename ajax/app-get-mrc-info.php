<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id', 'cost_center','MRC_CODE','MRC_DESC');
$jsonProjectInfo = $crudapp->readRecord2($conn,$data,"R5_VIEW_COSTCENTER",$condition);
echo $jsonProjectInfo;
?>
