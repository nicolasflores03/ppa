<?php
$user = $_GET['login'];
$year = date("Y"); 
header("Location:app-budget-movement-approval-cost.php?login=".$user."&year=".$year);
?>