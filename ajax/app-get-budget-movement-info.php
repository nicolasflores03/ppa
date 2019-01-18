<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$crudapp = new crudClass();
$condition = "WHERE id LIKE '$id'";
$data =array('id','app_id','ORG_CODE','FR_MRC_CODE','Source_Department','TO_MRC_CODE','fr_code','Source','to_code','Destination','Amount','year_budget','type','status','remarks','cost_center','fr_cost_center','reason','fr_table','to_table','to_quarter', 'fr_quarter', 'to_org_code');
  
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
	if ($objempty != 1){
		$jsonFN = json_encode(array_merge(json_decode($jsonInfo,true),json_decode($jsonResponsibleInfo,true)));
	
		$jsonInfo = $jsonFN;
	}
}

$amount_from = array("total_available" => 0.00,
					"q1_available" => 0.00,
					"q2_available" => 0.00,
					"q3_available" => 0.00,
					"q4_available" => 0.00);

$amount_to = array("total_available" => 0.00,
			"q1_available" => 0.00,
			"q2_available" => 0.00,
			"q3_available" => 0.00,
			"q4_available" => 0.00);

$objects = json_decode($jsonInfo,true);


if($objects['status'] == "Revision Request") {
	$year = $objects['year_budget'];
	$department_id = $objects['FR_MRC_CODE'];
	$costcenterfr = $objects['fr_cost_center'];
	$mrccode = $objects['TO_MRC_CODE'];
	$cost_center = $objects['cost_center'];
	$fr_code = $objects['fr_code'];
	$to_code = $objects['to_code'];
	$from_data = array();
		$movementType = $objects['type'];
		$source_quarter = isset($objects['fr_quarter']) ? $objects['fr_quarter'] : '';
		$destination_quarter = isset($objects['to_quarter']) ?  $objects['to_quarter'] : '';
		$to_org_code = isset($objects['to_org_code']) ?  $objects['to_org_code'] : '';

		if($movementType == "reallocation"){
			$cnd = "year_budget = '$year' AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$department_id' AND status = 'Approved' AND cost_center = '$costcenterfr' AND rowid = '$fr_code'";
			$from_data = $crudapp->readRecord3($conn,"R5_BUDGET_REALLOCATION_LOOKUP",$cnd);
			if(count($from_data) > 0) {
				$amount_from["total_available"] = $from_data[0]["available"];
				$amount_from["q1_available"] = $from_data[0]["q1_available"];
				$amount_from["q2_available"] = $from_data[0]["q2_available"];
				$amount_from["q3_available"] = $from_data[0]["q3_available"];
				$amount_from["q4_available"] = $from_data[0]["q4_available"];
			}
		} 
		
		$cnd_to = "year_budget = '$year' AND ORG_CODE = '$to_org_code' AND MRC_CODE = '$mrccode' AND status = 'Approved' AND cost_center = '$cost_center' AND rowid = '$to_code'";
		$to_data = $crudapp->readRecord3($conn,"R5_BUDGET_REALLOCATION_LOOKUP",$cnd_to);

		if(count($to_data) > 0) {
			$amount_to["total_available"] = $to_data[0]["available"];
			$amount_to["q1_available"] = $to_data[0]["q1_available"];
			$amount_to["q2_available"] = $to_data[0]["q2_available"];
			$amount_to["q3_available"] = $to_data[0]["q3_available"];
			$amount_to["q4_available"] = $to_data[0]["q4_available"];
		}
}

$objects["available_from"] = $amount_from;
$objects["available_to"] = $amount_to;
$jsonInfo = json_encode($objects);
echo $jsonInfo;
?>