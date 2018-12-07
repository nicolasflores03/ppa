<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$crudapp = new crudClass();
$data =array('year_budget','version','reference_no','MRC_CODE','ORG_CODE','cost_center');
$column = $crudapp->readRecord($conn,$data,"R5_DPP_VERSION","id",$id);
echo $column;
?>