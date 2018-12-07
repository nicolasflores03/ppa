<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$crudapp = new crudClass();
$id = $_GET['id'];
$content = "";
$totalCost = 0.00;
$totalCostFormat = 0.00;
$column = $crudapp->readColumn($conn,"R5_ENDORSED_APP_TYPE_COSTBASE");
$requiredField = array('category','Cost','reference_no','MRC_CODE','ORG_CODE','year_budget','version');
$column = array_intersect($column,$requiredField);
$condition = "id = '$id'";
$costView = $crudapp->listTable($conn,"R5_ENDORSED_APP_TYPE_COSTBASE",$column,$condition);
$content .='<table border="1" cellspacing="5px" width="70%" style="margin:0 auto;">';
$content .= '<tr><th colspan="2">Cost Based</th></tr>';
$content .= '<tr>';
$content .= '<th style="background:#d1cab0;">Category</th>';
$content .= '<th style="background:#d1cab0;">Cost (PHP)</th>';
$content .= '</tr>';
foreach($costView AS $list){
$category = $list['category'];
$reference_no = $list['reference_no'];
$MRC_CODE = $list['MRC_CODE'];
$ORG_CODE = $list['ORG_CODE'];
$year_budget = $list['year_budget'];
$version = $list['version'];
$cost = $list['Cost'];
$cost2 = $list['Cost'];
$cost = str_replace(",","",$cost);
$totalCost += $cost;
$totalCostFormat = number_format($totalCost,2);
$content .= "<tr onClick=\"runCostReport('$reference_no','$MRC_CODE','$ORG_CODE','$year_budget','$version','$category')\">";
$content .= "<td width='50%'>$category</td>";
$content .= "<td width='50%'>$cost2</td>";
$content .= '</tr>';
}
$content .= '<tr>';
$content .= "<td width='50%' style='text-align:right;'><b>Total:</b></td>";
$content .= "<td width='50%'><b>$totalCostFormat</b></td>";
$content .= '</tr>';
$content .= '</table>';
echo $content;
?>