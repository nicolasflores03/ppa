<?php
$user = $_GET['login'];
$year = date("Y"); 
header("Location:app-budget-movement-approval.php?login=".$user."&year=".$year);
?>