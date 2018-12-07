<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$action=$_GET["action"]; 
$user=$_GET["user"]; 
$crudapp = new crudClass();
$update = $crudapp->updateAppStatus($conn,$id,$action,$user);

echo $update;
?>