<?php
include("include/connect.php");
include("class/crud.php");

$from_id = $_GET['from_id'];
$id = $_GET['id'];
$from_val = $_GET['from_val'];
$to_id = $_GET['to_id'];
$to_val = $_GET['to_val'];
$amount = $_GET['amount'];
$year_budget = $_GET['year_budget'];
$type = $_GET['type'];
$budget = $_GET['budget'];
$MRC_CODE = $_GET['mrccode'];
$ORG_CODE = $_GET['orgcode'];
$cost_center = $_GET['cost_center'];
$source_tb = $_GET['source_tb'];
$destination_tb = $_GET['destination_tb'];


$crudapp = new crudClass();
$column = array('MRC_CODE','MRC_DESC');
$data = $crudapp->listTable($conn,"R5_DPP_APPROVED_DEPT",$column,"ORG_CODE = '$ORG_CODE' AND year_budget = '$year_budget'")
?>


<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
<title>Opener</title> 
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type='text/javascript'> 
$(document).ready(function(){
	$("tr#tbitem").click(function() {        // function_tr
		var id = $(this).find("td").eq(0).text();
		var desc = $(this).find("td").eq(1).text();
	
		var from_id = "<?php echo $from_id; ?>";
		var ids = "<?php echo $id; ?>";
		var from_val = "<?php echo $from_val; ?>";
		var amount = "<?php echo $amount; ?>";
		var to_id = "<?php echo $to_id; ?>";
		var to_val = "<?php echo $to_val; ?>";
		var type = "<?php echo $type; ?>";
		var year_budget = "<?php echo $year_budget; ?>";
		var budgetOrig = "<?php echo $budget; ?>";
		var ORG_CODE = "<?php echo $ORG_CODE; ?>";
		var MRC_CODE = "<?php echo $MRC_CODE; ?>";
		var cost_center = "<?php echo $cost_center; ?>";
		var source_tb = "<?php echo $source_tb; ?>";
		var destination_tb = "<?php echo $destination_tb; ?>";
		
		window.opener.document.theForm.id.value=ids;  
		window.opener.document.theForm.department_id.value=id;  
		window.opener.document.theForm.department_val.value=desc;  
		window.opener.document.theForm.movementType.value=type;
		window.opener.document.theForm.CST_CODE.value=cost_center;
		window.opener.document.theForm.source_tb.value=source_tb;
		window.opener.document.theForm.destination_tb.value=destination_tb;
		window.opener.checkType();
		window.opener.getFromCostCenter(id,"")
		self.close(); 
		
	});
});
</script> 
</head> 
<body> 
<!--Start of FORM-->
<div class="headerText">Budget Movement</div>
  <table width="100%" cellspacing="0" cellpadding="0" border="1">
	<tr>
		<th>Code</th>
		<th>Description</th>
	</tr>
	<?php
	foreach($data as $value){
	$id=$value['MRC_CODE'];
	$MRC_DESC = $value['MRC_DESC'];
	echo "<tr id='tbitem'>";
	echo "<td>".$id."</td>";
	echo "<td>".$MRC_DESC."</td>";		
	echo "</tr>";
	}
	echo '</table>';
	?>
  
</body> 
</html>