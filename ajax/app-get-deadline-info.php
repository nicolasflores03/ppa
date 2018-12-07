<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id','month','date','year','budget_year','isActive', 'Q1', 'Q2', 'Q3', 'Q4');
$jsonDeadlineInfo = $crudapp->readRecord2($conn,$data,"R5_DEADLINE_MAINTENANCE",$condition);
echo $jsonDeadlineInfo;
?>
