<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('project_code', 'description');
$jsonProjectInfo = $crudapp->readRecord2($conn,$data,"R5_EAM_APP_PROJECTBASE_LINES",$condition);
echo $jsonProjectInfo;
?>
