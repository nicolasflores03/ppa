<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$code=$_GET["code"]; 
$crudapp = new crudClass();
$data =array('PAR_CODE', 'PAR_DESC', 'PAR_BASEPRICE','PAR_LASTPRICE', 'PAR_UDFCHAR07', 'CMD_DESC', 'UOM_CODE', 'UOM_DESC','gl','gl_description','PAR_COMMODITY');
$column = $crudapp->readRecord($conn,$data,"R5_VIEW_SERVICE_UOM_INFO","PAR_CODE",$code);

echo $column;
?>