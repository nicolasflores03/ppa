<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");
include("class/object.php");

/* Begin the transaction. */
if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

//Generate Object
$crudapp = new crudClass();
$filterapp = new filterClass();

//VARIABLES
$errorFlag = false;
$errorMessage = "";
$year = $_GET['year'];
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$year_budget = "";
$msg = @$_GET['msg'];
$res = @$_GET['res'];
$orgcode = @$_GET['ORG_CODE'];
//-----
$id = @$_GET['id'];
$from_id = @$_GET['from_id'];
$from_val = @$_GET['from_val'];
$prj_code = @$_GET['prj_code'];
$prj_desc = @$_GET['prj_desc'];
$from_val = @$_GET['from_val'];
$to_id = @$_GET['to_id'];
$to_val = @$_GET['to_val'];
$amount = @$_GET['amount'];
$movementType = @$_GET['movementType'];

//GET status Based on reference_no,dept,org,year
$appfilter = "year_budget = '$year' AND ORG_CODE = '$orgcode' AND status = 'Approved'";
$appcolumn = $crudapp->readColumn($conn,"R5_APP_VERSION");
$appinfo = $crudapp->listTable($conn,"R5_APP_VERSION",$appcolumn,$appfilter);
@$app_id = $appinfo[0]['app_id'];

$filter = array();
$cnd = "year_budget = '$year' ORDER BY ID DESC";
$column = $crudapp->readColumn($conn,"R5_VIEW_BUDGET_MOVEMENT_PROJECT");
$requiredField = array('id','project_description','Source','Destination','amount','year_budget','type','status','reason');
$column = array_intersect($column,$requiredField);
$listView = $crudapp->listTable($conn,"R5_VIEW_BUDGET_MOVEMENT_PROJECT",$column,$cnd);
$tableView = $filterapp->filterViewURLXdeleteID($conn,$column,$listView,$filter,"id");

if (isset($_POST['search'])){
$fieldname = $_POST['fieldname'];
$value = $_POST['value'];
$type = $_POST['type'];
	//Form Validation
	if ($fieldname == ""){
	$errorMessage .= 'Please select a fieldname.\n\n';
	$errorFlag = true;
	}
	
	if ($value == ""){
	$errorMessage .= 'Please select a value.\n\n';
	$errorFlag = true;
	}

	if(!$errorFlag){
		$filter = array($fieldname,$type,$value);
		$tableView = $filterapp->filterViewURLXdeleteID($conn,$column,$listView,$filter,"id");
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}	
}


if (isset($_POST['submit'])){
	//Passing of Data
	$id = $_POST['id'];
	$fr_code = $_POST['from_id'];
	$project_description = $_POST['project_description'];
	$project_code = $_POST['project_code'];
	$to_code =  $_POST['to_id'];
	$year_budget =  $_POST['year_budget'];
	$amount =  $_POST['amount'];
	$amount = str_replace(",","",$amount);
	$type =  @$_POST['movementType'];
	$reason = $_POST['reason'];

	if ($project_code == ""){
	$errorMessage .= 'Please select a project code.\n\n';
	$errorFlag = true;
	}
	
	if ($type == ""){
	$errorMessage .= 'Please select a movement type.\n\n';
	$errorFlag = true;
	}else{
		if ($type == "reallocation" && $fr_code == ""){
		$errorMessage .= 'Please select a source of budget milestone.\n\n';
		$errorFlag = true;
		}

		if ($type == "reallocation" && $to_code == ""){
		$errorMessage .= 'Please select a destination of budget milestone.\n\n';
		$errorFlag = true;
		}
		
		if ($type == "supplement" && $to_code == ""){
		$errorMessage .= 'Please select a destination of budget milestone.\n\n';
		$errorFlag = true;
		}
	}
	
	if ($amount == ""){
	$errorMessage .= 'Please enter a valid amount.\n\n';
	$errorFlag = true;
	}
	
	if(!$errorFlag){	
		$table = "dbo.R5_BUDGET_MOVEMENT_PROJECT";
	
		$today = date("m/d/Y H:i");	
		if($id != ""){
		$data = array("app_id"=>$app_id,"ORG_CODE"=>$orgcode,"fr_code"=>$fr_code,"to_code"=>$to_code,"amount"=>$amount,"year_budget"=>$year_budget,"type"=>$type,"status"=>"Created","updatedAt"=>$today,"reason"=>$reason,"project_id"=>$project_code,"project_description"=>$project_description);
		$result2 = $crudapp->updateRecord($conn,$data,$table,"id",$id);
		}else{
		$record_id = $crudapp->readID($conn,"R5_BUDGET_MOVEMENT_PROJECT");
		$id = $record_id + 1;
		$data = array("app_id"=>$app_id,"ORG_CODE"=>$orgcode,"fr_code"=>$fr_code,"to_code"=>$to_code,"amount"=>$amount,"year_budget"=>$year_budget,"type"=>$type,"status"=>"Created","createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"reason"=>$reason,"project_id"=>$project_code,"project_description"=>$project_description);
		$result2 = $crudapp->insertRecord($conn,$data,$table);
		}
				
		
		if($result2) {
			sqlsrv_commit( $conn );
					//SEND EMAIL
		$emailfilter = "id = 1";
		$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
		$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
		$subject = @$emailinfo[0]['subject'];
		$body = @$emailinfo[0]['body'];
	
		$content = "This is to inform you that a Budget Movement Request has been Created as of $today";
		$content .= "<br><b>Details:</b><br>Organization: $orgcode<br>ID #: $id<br>";
		
		$body = str_replace("\$content",$content,$body);
				
				
		//EMAIL Receiver
		$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE ORG_CODE = '$orgcode' AND FI = 1)";
		$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
		$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
		$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
		$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
				
		
			echo "Transaction committed.<br />";
		} else {
			sqlsrv_rollback( $conn );
			echo "Transaction rolled back.<br />";
		}
		header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year_budget."&cost_center=".$cost_center."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&res=pass&msg=You have successfully saved the records!");
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}	
}
?>


<!DOCTYPE html>
<html>
<title>Infor Eam</title>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
 <link rel="stylesheet" href="css/jquery-ui.css">
<script src="js/jquery.min.js">
</script>
<script src="js/jquery-ui.js">
</script>
<script src="js/string-util.js">
</script>
<script>
function cancel(oForm) {
    
  var elements = oForm.elements; 
    
  oForm.reset();

  for(i=0; i<elements.length; i++) {
      
	field_type = elements[i].type.toLowerCase();
	
	switch(field_type) {
	
		case "text": 
		case "password": 
		case "textarea":
	        case "hidden":	
			$(this).prop("defaultValue");
			//elements[i].value = ""; 
			break;
        
		case "radio":
		case "checkbox":
  			if (elements[i].checked) {
   				elements[i].checked = false; 
			}
			break;

		case "select-one":
		case "select-multi":
            		elements[i].selectedIndex = "";
			break;

		default: 
			break;
	}
    }
}
function onclickEvent(id){
//HASH
$('#id').val(id);
var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	 var json = $.parseJSON(xmlhttp.responseText);
	 var fr_code = json['fr_code'];
	 var Source = json['Source'];
	 var to_code = json['to_code'];
	 var Destination = json['Destination'];
	 var amount = json['Amount'];
	 var year_budget = json['year_budget'];
	 var type = json['type'];
	 var status = json['status'];
	 var project_id = json['project_id'];
	 var project_description = json['project_description'];

	 var reason = json['reason'];
	 status = status.replace(/ /g, '');


	 $('#amount').val(amount);
	 $('#year_budget').val(year_budget);
	 $('#to_id').val(to_code);
	 $('#from_id').val(fr_code);
	 $('#movementType').val(type);
	 $('#id').val(id);
	 $('#to_val').val(Destination);
	 $('#from_val').val(Source);
	 $('#reason').val(reason);
	 $('#project_code').val(project_id);
	 $('#project_description').val(project_description);
	 
	 if (status == "RevisionRequest" || status == ""){
	 $('.actionButtonCenter').show();
	 }else{
	 $('.actionButtonCenter').hide();
	 }
	 movementType(type); 
    }
  }
xmlhttp.open("GET","ajax/app-get-budget-movement-project-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

function getFromToInfo(id,column){
//$('#id').val(id);
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	 var json = $.parseJSON(xmlhttp.responseText);
	 var code = "";
	 code = json['milestone'];
	 var available = json['available'];
	 if (column =="to_code"){		 
		$('#from_val').val(code);
	 }else{
		$('#to_val').val(code);
	 }
  }
 }
xmlhttp.open("GET","ajax/app-get-milestone-info3.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}




function movementType(type){
	if (type == "reallocation"){
		$('#budgetLabel').html('Available Budget:');
		$('#tr_source_tb').show();
		$('#from').show();
		$('#tr_destination_tb').show();
		$('#to').show();
		$('#amount_movement').show();
		$('#department').show();
		$('#fr_cost_center').show();
		}else if (type == "supplement"){
		$('#budgetLabel').html('Current Budget:');
		$('#from').hide();
		$('#tr_source_tb').hide();
		$('#to').show();
		$('#tr_destination_tb').show();
		$('#amount_movement').show();
		$('#department').hide();
		$('#fr_cost_center').hide();
		}else{
		$('#from').hide();
		$('#to').hide();
		$('#tr_source_tb').hide();
		$('#tr_destination_tb').hide();
		$('#amount_movement').hide();
		$('#department').hide();
		$('#fr_cost_center').hide();
		}
}




		
function valideopenerform(field){	
	var from_id = $('#from_id').val();
	var id = $('#id').val();
	var from_val = $('#from_val').val();
	var to_id = $('#to_id').val();
	var to_val = $('#to_val').val();
	var amount = $('#amount').val();
	var year_budget = $('#year_budget').val();
	var type = $('#movementType').val();
	var budget = $('#budget').val();
	var project_code = $('#project_code').val();
	var project_description = $('#project_description').val();
	var orgcode = "<?php echo $orgcode; ?>";
	var login = "<?php echo $user; ?>";
	
	var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
if (project_code !=""){			
var popup= window.open('popup-reallocate-milestone.php?hash='+text+'&from_id='+from_id+'&from_val='+from_val+'&field='+field+'&to_id='+to_id+'&orgcode='+orgcode+''
+'&to_val='+to_val+'&amount='+amount+'&year_budget='+year_budget+'&type='+type+'&budget='+budget+'&id='+id+'&login='+login+'&project_code='+project_code+'&project_description='+project_description+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}else{
alert("Please select a project code!");
}
}


function valideopenerform2(){	
	var from_id = $('#from_id').val();
	var id = $('#id').val();
	var from_val = $('#from_val').val();
	var to_id = $('#to_id').val();
	var to_val = $('#to_val').val();
	var amount = $('#amount').val();
	var year_budget = $('#year_budget').val();
	var type = $('#movementType').val();
	var budget = $('#budget').val();
	var orgcode = "<?php echo $orgcode; ?>";
		
var popup= window.open('popup-project-reallocation.php?from_id='+from_id+'&from_val='+from_val+'&to_id='+to_id+'&orgcode='+orgcode+''
+'&to_val='+to_val+'&amount='+amount+'&year_budget='+year_budget+'&type='+type+'&budget='+budget+'&id='+id+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}


$(document).ready(function(){
$("#movementType").attr("disabled", true);
$("#ORG_CODE").val("<?php echo $orgcode;?>");
$("#id").val('<?php echo $id; ?>');
$("#from_id").val('<?php echo $from_id; ?>');
$("#from_val").val('<?php echo $from_val; ?>');
$("#to_id").val('<?php echo $to_id; ?>');
$("#to_val").val('<?php echo $to_val; ?>');

$("#project_code").val('<?php echo $prj_code; ?>');
$("#project_description").val('<?php echo $prj_desc; ?>');

if($("#to_id").val() != ""){

	getFromToInfo($("#to_id").val(),"to");
 
}

$("#amount").val('<?php echo $amount; ?>');
$("#movementType").val('<?php echo $movementType; ?>');
	var orgCount = $('#ORG_CODE option').size();
	
var org = $("#ORG_CODE").val();
if (org == ""){	
	if (orgCount == 2){
		$("#ORG_CODE")[0].selectedIndex=1;
		var org = $("#ORG_CODE").val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&year=$year&ORG_CODE="; ?>'+org; 
	}
}else{
$("#movementType").attr("disabled", false);
}


//Error Message
var res = "<?php echo @$res;?>";
if(res !=""){
	if (res == "pass"){
		$('.isa_success').show();
		$('.isa_error').hide();
	}else {
		$('.isa_error').show();
		$('.isa_success').hide();
	}
}


var yr = "<?php echo $year;?>";
	$("#year_budget").val(yr);
	
	//YEAR
	$("#year_budget").change(function() {
		var yr = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=".$user."&ORG_CODE=".$orgcode."&year="; ?>'+yr; 
	});
	
	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&year=$year&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
	
	var type = $('#movementType').val();
		if (type == "reallocation"){
		$('#budgetLabel').html('Available Budget:');
		$('#from').show();
		$('#to').show();
		$('#amount_movement').show();
		}else if (type == "supplement"){
		$('#budgetLabel').html('Current Budget:');
		$('#from').hide();
		$('#to').show();
		$('#amount_movement').show();
		}else{
		$('#from').hide();
		$('#to').hide();
		$('#amount_movement').hide();
		}


	$('#year_budget').change(function() {
		var from_id = $('#from_id').val('');
		var from_val = $('#from_val').val('');
		var to_id = $('#to_id').val('');
		var to_val = $('#to_val').val('');
		var amount = $('#amount').val(0.00);
		var budget = $('#budget').val('');
		var budget_fr = $('#budget_fr').val('');
	});

		//YEAR
	$("#amount").change(function() {
		var amount = $(this).val();
		amount = parseFloat(amount.replace(/,/g, ''));
		var budget_fr = $("#budget_fr").val();
		budget_fr = parseFloat(budget_fr.replace(/,/g, ''));
		var movementType = $('#movementType').val();
		if (movementType == "reallocation"){
			//alert(amount+"---"+budget_fr);
			if (amount > budget_fr){
				alert('Insufficient budget!');
				$(this).val("");
			}
		}
	});
	
	$('#movementType').change(function() {
		var type = $('#movementType').val();
		$('#from_val').val("");
		$('#to_val').val("");
		$('#to_id').val("");
		$('#from_id').val("");
		$('#amount').val(0.00);
		$('#budget').val("");
		$('#budget_fr').val("");
	
		if (type == "reallocation"){
		$('#budgetLabel').html('Available Budget:');
		$('#from').show();
		$('#to').show();;
		$('#amount_movement').show();
		}else if (type == "supplement"){
		$('#budgetLabel').html('Current Budget:');
		$('#from').hide();
		$('#to').show();
		$('#amount_movement').show();
		}else{
		$('#from').hide();
		$('#to').hide();
		$('#amount_movement').hide();
		}
	});
	
	var user = "<?php echo $user; ?>";
	var year = "<?php echo $year; ?>";
	//Save Button
	$("#newRecord").click(function() {
		window.location = "dpp-budget-movement-project.php?login="+user+"&year="+year;
	});

});

function checkType(){
var type = $('#movementType').val();
	if (type == "reallocation"){
	$('#budgetLabel').html('Available Budget:');
	$('#from').show();
	$('#to').show();
	$('#amount_movement').show();
	}else if (type == "supplement"){
	$('#budgetLabel').html('Current Budget:');
	$('#from').hide();
	$('#to').show();
	$('#amount_movement').show();
	
	}else{
	$('#from').hide();
	$('#to').hide();
	$('#amount_movement').hide();
	}
}
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&ORG_CODE=".$orgcode."&id=".$id."&from_id=".$from_id."&from_val=".$from_val."&to_id=".$to_id."&to_val=".$to_val."&amount=".$amount."&movementType=".$movementType;?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">Budget Re-allocation / Supplement</div></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField"><input type="hidden" class="field" name="ref_no" id="ref_no" spellcheck="false" tabindex="1" value= "<?php echo $reference_no;?>">
					<?php 
						$tbname = "R5_VIEW_USERINFO";
						$tbfield = "DISTINCT(ORG_CODE)";
						$tbfield2 = "ORG_DESC";
						$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user'");
					?>
			</td>			
			<td class="textLabel">Year Budget:</td>
			<td class="textField">
				<select name="year_budget" id="year_budget">
					<option value="">-- Please select --</option>
					<option value="2014">2014</option>
					<option value="2015">2015</option>
					<option value="2016">2016</option>
					<option value="2017">2017</option>
					<option value="2018">2018</option>
					<option value="2019">2019</option>
					<option value="2020">2020</option>
					<option value="2021">2021</option>
					<option value="2022">2022</option>
					<option value="2023">2023</option>
					<option value="2024">2024</option>
					<option value="2025">2025</option>
					<option value="2026">2026</option>
					<option value="2027">2027</option>
					<option value="2028">2028</option>
					<option value="2029">2029</option>
					<option value="2030">2030</option>
					<option value="2031">2031</option>
					<option value="2032">2032</option>
					<option value="2033">2033</option>
					<option value="2034">2034</option>
					<option value="2035">2035</option>
					<option value="2036">2036</option>
					<option value="2037">2037</option>
					<option value="2038">2038</option>
					<option value="2039">2039</option>
					<option value="2040">2040</option>
				</select>
			</td>				
		</tr>
	</tbody>
</table>
</div>
<div class="headerText">Budget Movement Details</div>
<div class="headerText">
<!--<input type="button" class="tabs selected" name="ITEM-BASE" value=" ITEM-BASED ">-->
<!--<input type="button" class="tabs" name="COST-BASE" id="tabURL" value=" COST-BASED ">-->
<input type="button" class="tabs" name="NEW-RECORD" id="newRecord" value=" New Record ">
</div>

<div class="filters">
  <table width="33%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
	<tr>
		<td>
			<?php 
			//Render them in drop down box	
			$selection = "";
			$selection .= "<select name='fieldname' id='fieldname'>";
			$selection .= "<option value=''>-- Please select --</option>";
			$requiredFilter = array('id','Source','Destination','year_budget','status','reason');
			$column = array_intersect($column,$requiredFilter);
			
			foreach($column as $fieldName){
				$fieldNameVal = str_replace('_', ' ', $fieldName);
				$selection .= "<option name='". $fieldName . "' value='" .$fieldName . "'>". $fieldNameVal . "</option>";
			}
			
			$selection .= "</select>";
			echo $selection;
			?>
			<select name="type" id="type">
				<option name="role" value="sw">Starts With</option>
				<option name="role" value="ew">Ends With</option>
				<option name="role" value="eq">Equals</option>
				<option name="role" value="co">Contains</option>
			</select>
			<input type="text" name="value" id="value" maxlength="50" tabindex="3">		
			<input type="submit" class="bold" name="search" id="search" value=" Run ">&nbsp;&nbsp;
		</td>
	</tr>
  </table>
</div>
	<?php
		echo $tableView;
	?>	
<!--PROJECT MILESTONE-->
<div class="formDiv">
<div class="headerText">Budget Movement Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
	<tbody>
			<tr>
			<td class="textLabel">Movement Type: <i class="required">*</i></td>
			<td class="textField">
			<input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1">
				<select name="movementType" id="movementType">
					<option value="">-- Please select --</option>
					<option value="reallocation">Budget Re-Allocation</option>
					<option value="supplement">Budget Supplement</option>
				</select>
			</td>				
		</tr>
		<tr id="project">
			<td class="textLabel">Project Code <i class="required">*</i></td>
			<td class="textField"><input type="hidden" class="field" name="project_code" id="project_code" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="project_description" id="project_description" spellcheck="false" tabindex="1" readonly><button name="project" onclick="valideopenerform2()">...</button></td>
		</tr>
		<tr id="from">
			<td class="textLabel">From <i class="required">*</i></td>
			<td class="textField"><input type="hidden" class="field" name="from_id" id="from_id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="from_val" id="from_val" spellcheck="false" tabindex="1" readonly><button name="ItemCode" onclick="valideopenerform('from')">...</button></td>
		</tr>
		<tr id="to">
			<td class="textLabel">To <i class="required">*</i></td>
			<td class="textField"><input type="hidden" class="field" name="to_id" id="to_id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="to_val" id="to_val" spellcheck="false" tabindex="1" readonly><button name="ItemCode" onclick="valideopenerform('to')">...</button></td>
		</tr>
		<tr id="amount_movement">
			<td class="textLabel">Amount: <i class="required">*</i></td>
			<td class="textField"><input type="text" class="field" name="amount" id="amount" spellcheck="false" tabindex="1"  value="0.00" onkeypress="return numbersonly(this, event)" onblur="round(this,2);">
		</tr>
		<tr>
			<td class="textLabel" id="budgetLabel">Available Budget:</td>
			<td class="textField"><input type="text" class="field" name="budget" id="budget" spellcheck="false" tabindex="1" readonly>
			<input type="hidden" class="field" name="budget_fr" id="budget_fr" spellcheck="false" tabindex="1">
			<!--<input type="hidden" class="field" name="budget_to" id="budget_to" spellcheck="false" tabindex="1">-->
			</td>			
		</tr>
		<tr>
			<td class="textLabel">Reason:</td>
			<td class="textField" colspan="3"><textarea id="reason" name="reason"></textarea></td>		
		</tr>
	</tbody>
	</table>
	<!--Action Button-->
	<div class="actionButtonCenter">
				<input type="submit" class="bold" name="submit" id="submit" value=" Save ">
				<input type="button" value=" Cancel " Onclick="cancel(this.form)">&nbsp;&nbsp;
	</div>
	</div>
</div>

</div>
</form>
</body>
</html>  
