<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$crudapp = new crudClass();
$data =array('year_budget','ORG_CODE','version','status');
$column = $crudapp->readRecord($conn,$data,"R5_APP_VERSION","app_id",$id);
echo $column;
?>