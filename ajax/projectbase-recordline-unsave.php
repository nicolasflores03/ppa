<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$reference_no=$_GET["reference_no"];
$version=$_GET["version"]; 
$crudapp = new crudClass();
$unsave = $crudapp->unsaveProjectApp($conn,$reference_no,$version); 
echo $unsave;
?>