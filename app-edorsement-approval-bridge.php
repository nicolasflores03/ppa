<?php
$user = $_GET['login'];
$password = $_GET['password'];
$year = date("Y"); 
header("Location:app-edorsement-approval-item.php?login=".$user."&password=".$password."&year=".$year);
?>