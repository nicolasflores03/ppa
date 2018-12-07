<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$budget_year=$_GET["budget_year"]; 
$condition = "WHERE budget_year = '$budget_year' AND isActive = '1'";
$crudapp = new crudClass();
$count = $crudapp->matchRecord2($conn,'R5_DEADLINE_MAINTENANCE','id',$condition);
echo $count;
?>