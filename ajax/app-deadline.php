<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$budget_year=$_GET["budget_year"]; 
$condition = "WHERE budget_year = '$budget_year' AND isActive = '1'";
$crudapp = new crudClass();
$data =array('id','month','date','year','budget_year','isActive', 'Q1', 'Q2', 'Q3', 'Q4');
$jsonDeadlineInfo = $crudapp->readRecord2($conn,$data,"R5_DEADLINE_MAINTENANCE",$condition);
echo $jsonDeadlineInfo == "" ? "0" : $jsonDeadlineInfo;
?>