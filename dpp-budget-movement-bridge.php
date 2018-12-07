<?php
$user = $_GET['login'];
$year = date("Y"); 
header("Location:dpp-budget-movement.php?login=".$user."&year=".$year);
?>