<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$crudapp = new crudClass();
$id=$_GET["id"]; 
$obj=$_GET["obj"]; 
$deltable = $obj;
$delResult = $crudapp->deleteRecord3($conn,$deltable,"id",$id);
echo $delResult;
?>