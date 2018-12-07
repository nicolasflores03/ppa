<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id','app_id','ORG_CODE','FR_MRC_CODE','Source_Department','TO_MRC_CODE','fr_code','Source','to_code','Destination','Amount','year_budget','type','status','fr_cost_center','reason');
  
$jsonInfo = $crudapp->readRecord2($conn,$data,"R5_VIEW_BUDGET_MOVEMENT_COST",$condition);
echo $jsonInfo;
?>