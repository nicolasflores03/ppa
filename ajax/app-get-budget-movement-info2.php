<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id','app_id','ORG_CODE','FR_MRC_CODE','Source_Department','TO_MRC_CODE','fr_code','Source','to_code','Destination','Amount','year_budget','type','status','fr_cost_center','reason','fr_table','to_table');
  
$jsonInfo = $crudapp->readRecord2($conn,$data,"R5_VIEW_BUDGET_MOVEMENT",$condition);


//Get Responsible
$obj=json_decode($jsonInfo,true);
$status = trim($obj['status'],' ');
$FR_MRC_CODE = $obj['FR_MRC_CODE'];
$TO_MRC_CODE = $obj['TO_MRC_CODE'];
$ORG_CODE = $obj['ORG_CODE'];
$condition2 = '';
$jsonFN = '';

if ($status == 'Created'){
$condition2 = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$TO_MRC_CODE' AND DH = '1'";
}else if ($status == 'For Review'){
$condition2 = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$FR_MRC_CODE' AND DH = '1'";
}else if ($status == 'Endorsed'){
$condition2 = "WHERE ORG_CODE = '$ORG_CODE' AND FI = '1'";
}

if ($condition2 != ""){
$data2 =array('USR_DESC'); 
$jsonResponsibleInfo = $crudapp->readRecord2($conn,$data2,"R5_VIEW_RESPONSIBLE",$condition2);


$obj2=json_decode($jsonResponsibleInfo,true);

$objempty = empty($obj2);
	if ($objempty == 1){
	echo $jsonInfo;
	}else{
		$jsonFN = json_encode(array_merge(json_decode($jsonInfo,true),json_decode($jsonResponsibleInfo,true)));
	
		echo $jsonFN;
	}
}else{
echo $jsonInfo;
}


?>