<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$crudapp = new crudClass();
$id=$_GET["id"]; 
$reference_no=$_GET["reference_no"]; 
$version=$_GET["version"]; 
$table1=$_GET["obj1"]; 
$table2=$_GET["obj2"];
$updatedBy=$_GET["updatedBy"];
$today = date("m/d/Y H:i");	
//Insert Record to Audit
$auditData = array("record_id"=>$id,"updatedBy"=>$updatedBy,"updatedAt"=>$today,"table_name"=>$table1,"update_type"=>"Delete");	
$audit = $crudapp->insertRecord($conn,$auditData,"R5_CUSTOM_AUDIT_APP_LINES");
		
$data = array('id'=>$id,'reference_no'=>$reference_no,'version'=>$version);
$delResult = $crudapp->deleteRecord2($conn,$table1,$table2,$data);
echo $delResult;
?>