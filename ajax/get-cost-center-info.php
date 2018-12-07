<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"]; 
$crudapp = new crudClass();
$tbname = "R5COSTCODES";
$tbfield = "CST_CODE";
$option = $crudapp->optionValue3($conn,$tbname,$tbfield,"WHERE CST_CLASS = '$id' AND CST_NOTUSED LIKE '-'");

	
		return $option;	
?>