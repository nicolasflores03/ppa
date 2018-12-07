<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$crudapp = new crudClass();
$project_code = array();
$id = $_GET['id'];
$content = "";
$totalCost = 0.00;
$totalCostFormat = 0.00;
$column = $crudapp->readColumn($conn,"R5_ENDORSED_APP_TYPE_PROJECTBASE");
$requiredField = array('Milestone','Amount','project_code','id','headerID');
$column = array_intersect($column,$requiredField);
$condition = "headerID = '$id'";
$typeView = $crudapp->listTable($conn,"R5_ENDORSED_APP_TYPE_PROJECTBASE",$column,$condition);

//GET UNIQUE Project Code
foreach($typeView AS $code){
	$pr_code = $code['project_code'];
	array_push($project_code,$pr_code);
}
$project_code = array_unique($project_code);
//--if ($views["$field"] == $value){

$content .='<table border="1" cellspacing="5px" width="70%" style="margin:0 auto;">';
$content .= '<tr>';
$content .= '<th>Milestone</th>';
$content .= '<th>Cost (PHP)</th>';
$content .= '</tr>';
foreach($project_code AS $project){
$content .= '<tr>';
$content .= "<td colspan='2' style='text-align:left;background:#d1cab0;'><b>$project</b></td>";
$content .= '</tr>';
	foreach($typeView AS $list){
		if ($list['project_code'] == $project){
			$milestone = $list['Milestone'];
			$amount = $list['Amount'];
			$amount2 = $list['Amount'];
			$amount = str_replace(",","",$amount);
			$totalCost += $amount;
			$totalCostFormat = number_format($totalCost,2);
			$content .= '<tr>';
			$content .= "<td width='50%'>$milestone</td>";
			$content .= "<td width='50%'>$amount2</td>";
			$content .= '</tr>';
		}
	}
}
$content .= '<tr>';
$content .= "<td width='50%' style='text-align:right;'><b>Total:</b></td>";
$content .= "<td width='50%'><b>$totalCostFormat</b></td>";
$content .= '</tr>';
$content .= '</table>';
echo $content;
?>