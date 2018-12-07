<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$tb = "";
$data =array();
$tb = "R5_VIEW_PROJECT_LINES_APPROVED"; 
$data =array('milestone','available');
$crudapp = new crudClass();
$column = $crudapp->readRecord($conn,$data,$tb,"milestoneID",$id);

echo $column;
?>