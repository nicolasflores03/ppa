<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id','app_id','ORG_CODE','fr_code','Source','to_code','Destination','Amount','year_budget','type','status','project_id','project_description');
  
$jsonInfo = $crudapp->readRecord2($conn,$data,"R5_VIEW_BUDGET_MOVEMENT_PROJECT",$condition);
echo $jsonInfo;
?>