<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$table=$_GET["table"]; 
$tb = "";
$data =array();
if($table == "IB"){
$tb = "R5_VIEW_ITEMBASE_LINES"; 
$data =array('Code','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec','unit_cost','available','Description');
}else{
$tb = "R5_VIEW_COSTBASE_LINES";
$data =array('code','CMD_CODE','CMD_DESC','Budget_Amount','description','Classification','category','io_number','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec','type');
}
$crudapp = new crudClass();
$column = $crudapp->readRecord($conn,$data,$tb,"id",$id);

echo $column;
?>