<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$crudapp = new crudClass();
$id=$_GET["id"]; 
$obj=$_GET["obj"]; 
$deltable = $obj;

	
if ($deltable == "R5_DPP_VERSION" || $deltable == "R5_APP_VERSION"||$deltable == "R5_EAM_APP_PROJECTBASE_LINES" ||$deltable == "R5_PROJECT_VERSION" ){ 
$updatedBy=$_GET["updatedBy"];
$today = date("m/d/Y H:i");	
//Insert Record to Audit
$auditData = array("record_id"=>$id,"updatedBy"=>$updatedBy,"updatedAt"=>$today,"table_name"=>$deltable,"update_type"=>"Delete");	
$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_APP_LINES");
}

$delResult = $crudapp->deleteRecord($conn,$deltable,"id",$id);
echo $delResult;
?>