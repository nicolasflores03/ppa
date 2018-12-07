<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");

//phpinfo();
//Generate Object
$crudapp = new crudClass();
$message = "Good Day!\nAdobo Resthouse is now open!\n\nThanks,";

$crudapp->sentEmail($conn,"adoboresthouse@gmail.com","bbmanalaysay@gmail.com","Adobo Resthouse 2014",$message);
	
?>
