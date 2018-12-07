<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$crudapp = new crudClass();
$data =array('code','CMD_CODE','CMD_DESC','Budget_Amount','Description','Classification','category','io_number','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec','type','unit_cost');
$column = $crudapp->readRecord($conn,$data,"R5_VIEW_COSTBASE_LINES","id",$id);

echo $column;
?>