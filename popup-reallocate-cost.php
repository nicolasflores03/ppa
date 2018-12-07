<?php
include("include/connect.php");
include("class/crud.php");

$field = $_GET['field'];
$id = $_GET['id'];
$from_id = $_GET['from_id'];
$from_val = $_GET['from_val'];
$to_id = $_GET['to_id'];
$to_val = $_GET['to_val'];
$amount = $_GET['amount'];
$year_budget = $_GET['year_budget'];
$type = $_GET['type'];
$budget = $_GET['budget'];
$frmrccode = $_GET['frmrccode'];
$tomrccode = $_GET['tomrccode'];
$MRC_DESC = $_GET['mrcdesc'];
$ORG_CODE = $_GET['orgcode'];
$cost_center = $_GET['cost_center'];
$costcenterfr = $_GET['costcenterfr'];
$source_tb = $_GET['source_tb'];
$destination_tb = $_GET['destination_tb'];
$login = $_GET['login'];
$cnd="";

if ($field=="from"){
$cnd = "year_budget = '$year_budget' AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$frmrccode' AND status = 'Approved' AND cost_center = '$costcenterfr'";
}else{
$cnd = "year_budget = '$year_budget' AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$tomrccode' AND status = 'Approved' AND cost_center = '$cost_center'";
}

$crudapp = new crudClass();
$data = $crudapp->readRecord4($conn,"R5_BUDGET_REALLOCATION_LOOKUP_COST",$cnd)
?>


<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
<title>Opener</title> 
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type='text/javascript'> 
$(document).ready(function(){
var type = "<?php echo $type; ?>";
var field = "<?php echo $field; ?>";
	if (type == "reallocation" && field == "to"){
		$('#new_tmp').hide();
		$('#new_tmp_reallocate').show();
	}else if (type == "supplement"){
		$('#new_tmp').show();
		$('#new_tmp_reallocate').hide();
	}else{
		$('#new_tmp').hide();
		$('#new_tmp_reallocate').hide();
	}

var year_budget = "<?php echo $year_budget; ?>";
var login = "<?php echo $login; ?>";
var mrccode = "<?php echo $tomrccode; ?>";
var cost_center = "<?php echo $cost_center; ?>";
var org_code = "<?php echo $ORG_CODE; ?>";
var from_id = "<?php echo $from_id; ?>";
var from_val = "<?php echo $from_val; ?>";
var frmrccode = "<?php echo $frmrccode; ?>";
var costcenterfr = "<?php echo $costcenterfr; ?>";
var MRC_DESC = "<?php echo $MRC_DESC; ?>";
var source_tb = "<?php echo $source_tb; ?>";
var destination_tb = "<?php echo $destination_tb; ?>";


$("#new_tmp").click(function() {
	var r=confirm("Are you sure you want to add a new record?");
	if (r==true){
			//window.opener.close();
			//window.open("dpp-record-lines-cost-addmore.php?year="+year_budget+"&login="+login+"&mrccode="+mrccode+"&org_code="+org_code+"&cost_center="+cost_center);
	
			window.opener.document.location.href = "dpp-record-lines-cost-addmore.php?year="+year_budget+"&login="+login+"&mrccode="+mrccode+"&org_code="+org_code+"&cost_center="+cost_center+"&source_tb="+source_tb+"&destination_tb="+destination_tb;
			self.close();
			
	}

});


$("#new_tmp_reallocate").click(function() {
	var r=confirm("Are you sure you want to add a new record?");
	if (r==true){
			//window.opener.close();
			//window.open("dpp-record-lines-cost-addmore.php?year="+year_budget+"&login="+login+"&mrccode="+mrccode+"&org_code="+org_code+"&cost_center="+cost_center);
	
			window.opener.document.location.href = "dpp-record-lines-cost-addmore-reallocate.php?year="+year_budget+"&login="+login+"&mrccode="+mrccode+"&org_code="+org_code+"&cost_center="+cost_center+"&from_id="+from_id+"&from_val="+from_val+"&frmrccode="+frmrccode+"&costcenterfr="+costcenterfr+"&mrcdesc="+MRC_DESC+"&source_tb="+source_tb+"&destination_tb="+destination_tb;
			self.close();
			
	}

});

	$("tr#tbitem").click(function() {        // function_tr
		var id = $(this).find("td").eq(0).text();
		var name = $(this).find("td").eq(1).text();
		var budget = $(this).find("td").eq(2).text();
		var field = "<?php echo $field; ?>";
		var ids = "<?php echo $id; ?>";
		var from_id = "<?php echo $from_id; ?>";
		var from_val = "<?php echo $from_val; ?>";
		var amount = "<?php echo $amount; ?>";
		var to_id = "<?php echo $to_id; ?>";
		var to_val = "<?php echo $to_val; ?>";
		var type = "<?php echo $type; ?>";
		var year_budget = "<?php echo $year_budget; ?>";
		var budgetOrig = "<?php echo $budget; ?>";
		var ORG_CODE = "<?php echo $ORG_CODE; ?>";
		var frmrccode = "<?php echo $frmrccode; ?>";
		var MRC_DESC = "<?php echo $MRC_DESC; ?>";
		var cost_center = "<?php echo $cost_center; ?>";
		var costcenterfr = "<?php echo $costcenterfr; ?>";
		var source_tb = "<?php echo $source_tb; ?>";
		var destination_tb = "<?php echo $destination_tb; ?>";
		
		//Default value for ref tables
		window.opener.document.theForm.from_id.value=from_id;
		window.opener.document.theForm.id.value=ids; 
		window.opener.document.theForm.department_val.value=MRC_DESC; 
		window.opener.document.theForm.department_id.value=frmrccode;  
		window.opener.document.theForm.to_id.value=to_id;  
		
		window.opener.document.theForm.from_val.value=from_val;  
		window.opener.document.theForm.to_val.value=to_val; 
		window.opener.document.theForm.budget.value=budgetOrig; 
		//window.opener.document.theForm.budget_fr.value=budgetOrig;
		
		if( field == "from" ){
			if (type == "reallocation"){
				window.opener.document.theForm.budget.value=budget;
				window.opener.document.theForm.budget_fr.value=budget;
			}	
		window.opener.document.theForm.from_id.value=id; 
		window.opener.document.theForm.from_val.value=name;  
		}else{
		window.opener.document.theForm.budget_fr.value=budgetOrig;
		//window.opener.document.theForm.budget_to.value=budget;
			if (type == "supplement"){
				window.opener.document.theForm.budget.value=budget;
				window.opener.document.theForm.budget_fr.value=budget;
			}	
		window.opener.document.theForm.to_id.value=id; 
		window.opener.document.theForm.to_val.value=name;  
		}
		
		window.opener.document.theForm.amount.value=amount;
		window.opener.document.theForm.movementType.value=type;
		window.opener.document.theForm.CST_CODE.value=cost_center;
		window.opener.document.theForm.source_tb.value=source_tb;
		window.opener.document.theForm.destination_tb.value=destination_tb;
		window.opener.checkType();
		window.opener.getFromCostCenter(frmrccode,costcenterfr);
		//window.opener.setFromCostCenter();
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
		<th>ID</th>
		<th>Item Desc</th>
		<th>Budget</th>
	</tr>
	<?php
	foreach($data as $value){
	$id=$value['rowid'];
	$description = $value['description'];
	$budget = $value['available'];
	echo "<tr id='tbitem'>";
	echo "<td>".$id."</td>";
	echo "<td>".$description."</td>";
	echo "<td>".$budget."</td>";		
	echo "</tr>";
	}
	echo '</table>';
	?>
	
	<div class="divText">
		<input type="button" class="bold" name="new_tmp" id="new_tmp" value=" Add Item ">
		<input type="button" class="bold" name="new_tmp_reallocate" id="new_tmp_reallocate" value=" Add Item ">
	</div>
</body> 
</html>