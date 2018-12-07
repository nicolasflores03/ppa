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
$ORG_CODE = $_GET['orgcode'];
$login = $_GET['login'];
$project_code = $_GET['project_code'];
$project_description = $_GET['project_description'];
$cnd = "WHERE year_budget = '$year_budget' AND ORG_CODE = '$ORG_CODE' AND ID = '$project_code' AND mileSaveFlag = 1";
$crudapp = new crudClass();
$data = $crudapp->listRefRecordProject($conn,"R5_VIEW_PROJECT_LINES_APPROVED",$cnd)
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
var org_code = "<?php echo $ORG_CODE; ?>";
var from_id = "<?php echo $from_id; ?>";
var from_val = "<?php echo $from_val; ?>";
var project_code = "<?php echo $project_code; ?>";
var project_description = "<?php echo $project_description; ?>";

$("#new_tmp").click(function() {
	var r=confirm("Are you sure you want to add a new record?");
	if (r==true){
			//window.opener.close();
			
			window.opener.document.location.href = "dpp-record-lines-milestone-addmore.php?year="+year_budget+"&login="+login+"&org_code="+org_code+"&project_id="+project_code;
			self.close();
			//window.open("dpp-record-lines-item-addmore.php?year="+year_budget+"&login="+login+"&mrccode="+mrccode+"&org_code="+org_code+"&cost_center="+cost_center);
	}

});


$("#new_tmp_reallocate").click(function() {
	var r=confirm("Are you sure you want to add a new record?");
	if (r==true){
			//window.opener.close();
			
			window.opener.document.location.href = "dpp-record-lines-milestone-addmore-reallocate.php?year="+year_budget+"&login="+login+"&org_code="+org_code+"&from_id="+from_id+"&from_val="+from_val+"&project_code="+project_code+"&project_description="+project_description;
			self.close();
			//window.open("dpp-record-lines-item-addmore.php?year="+year_budget+"&login="+login+"&mrccode="+mrccode+"&org_code="+org_code+"&cost_center="+cost_center);
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
		var project_code = "<?php echo $project_code; ?>";
		var project_description = "<?php echo $project_description; ?>";
		
		//Default value for ref tables
		window.opener.document.theForm.from_id.value=from_id;
		window.opener.document.theForm.id.value=ids; 
		window.opener.document.theForm.to_id.value=to_id;  
		
		window.opener.document.theForm.from_val.value=from_val;  
		window.opener.document.theForm.to_val.value=to_val; 
		window.opener.document.theForm.budget.value=budgetOrig; 
		window.opener.document.theForm.project_code.value=project_code;
		window.opener.document.theForm.project_description.value=project_description;
		
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
		window.opener.checkType();
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
		<th>Item Code</th>
		<th>Item Desc</th>
		<th>Budget</th>
	</tr>
	<?php
	foreach($data as $value){
	$id=$value['milestoneID'];
	$milestone = $value['milestone'];
	$budget_amount = $value['available2'];
	$budget_amount = number_format($budget_amount, 2, '.', ',');
	echo "<tr id='tbitem'>";
	echo "<td>".$id."</td>";
	echo "<td>".$milestone."</td>";
	echo "<td>".$budget_amount."</td>";		
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