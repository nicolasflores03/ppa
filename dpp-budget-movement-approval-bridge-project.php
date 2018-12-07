<?php
$user = $_GET['login'];
$year = date("Y"); 
header("Location:app-budget-movement-project-approval.php?login=".$user."&year=".$year);
?>