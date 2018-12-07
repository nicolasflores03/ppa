<?php
$user = $_GET['login'];
$year = date("Y"); 
header("Location:dpp-budget-movement-project.php?login=".$user."&year=".$year);
?>