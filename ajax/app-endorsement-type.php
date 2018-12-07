<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$crudapp = new crudClass();
$id = $_GET['id'];
$content = "";
$totalCost = 0.00;
$totalCostFormat = 0.00;
$column = $crudapp->readColumn($conn,"R5_ENDORSED_APP_TYPE_ITEMBASE");
$requiredField = array('Item_Type','Cost','reference_no','MRC_CODE','ORG_CODE','year_budget','version','PAR_COMMODITY');
$column = array_intersect($column,$requiredField);
$condition = "id = '$id'";
$typeView = $crudapp->listTable($conn,"R5_ENDORSED_APP_TYPE_ITEMBASE",$column,$condition);
$content .='<table border="1" cellspacing="5px" width="70%" style="margin:0 auto;">';
$content .= '<tr><th colspan="2">Item Based</th></tr>';
$content .= '<tr>';
$content .= '<th style="background:#d1cab0;">Commodity</th>';
$content .= '<th style="background:#d1cab0;">Cost (PHP)</th>';
$content .= '</tr>';
foreach($typeView AS $list){
$type = $list['Item_Type'];
$cost = $list['Cost'];
$cost2 = $list['Cost'];
$reference_no = $list['reference_no'];
$MRC_CODE = $list['MRC_CODE'];
$ORG_CODE = $list['ORG_CODE'];
$year_budget = $list['year_budget'];
$version = $list['version'];
$PAR_COMMODITY = $list['PAR_COMMODITY'];
$cost = str_replace(",","",$cost);
$totalCost += $cost;
$totalCostFormat = number_format($totalCost,2);
$content .= "<tr onClick=\"runItemReport('$reference_no','$MRC_CODE','$ORG_CODE','$year_budget','$version','$PAR_COMMODITY')\">";
$content .= "<td width='50%'>$type</td>";
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