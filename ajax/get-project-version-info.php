<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$crudapp = new crudClass();
$data =array('year_budget','version','status','reference_no');
$column = $crudapp->readRecord($conn,$data,"R5_PROJECT_VERSION","id",$id);
echo $column;
?>