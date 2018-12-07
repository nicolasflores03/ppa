<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('year_budget','version','status','reference_no');
$jsonProjectInfo = $crudapp->readRecord2($conn,$data,"R5_PROJECT_VERSION",$condition);
echo $jsonProjectInfo;
?>