<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$crudapp = new crudClass();
$data =array('Code','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec','unit_cost','available');
$column = $crudapp->readRecord($conn,$data,"R5_VIEW_ITEMBASE_LINES","id",$id);

echo $column;
?>