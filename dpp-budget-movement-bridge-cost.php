<?php
$user = $_GET['login'];
$year = date("Y"); 
header("Location:dpp-budget-movement-cost.php?login=".$user."&year=".$year);
?>