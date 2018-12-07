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
$mrccode = $_GET['mrccode'];
$orgcode = $_GET['org_code'];
$cost_center = $_GET['cost_center'];
$frmrccode = $_GET['frmrccode'];
$frmrcdesc = $_GET['mrcdesc'];
$costcenterfr = $_GET['costcenterfr'];
$from_id = $_GET['from_id'];
$from_val = $_GET['from_val'];
$lastDigit = substr($year, -1); 
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$msg = @$_GET['msg'];
$res = @$_GET['res'];
$source_tb = $_GET['source_tb'];
$destination_tb = $_GET['destination_tb'];

//GET status Based on reference_no,dept,org,year
$dppfilter = "year_budget = '$year' AND ORG_CODE = '$orgcode' AND MRC_CODE = '$mrccode' AND cost_center = '$cost_center' AND status = 'Approved'";
$dppcolumn = $crudapp->readColumn($conn,"R5_VIEW_DPP_VERSION");
$dppinfo = $crudapp->listTable($conn,"R5_VIEW_DPP_VERSION",$dppcolumn,$dppfilter);
$status = $dppinfo[0]['status'];
$reference_no = $dppinfo[0]['reference_no'];
$version = $dppinfo[0]['version'];


//COST BASE
$record_id2 = $crudapp->readID($conn,"R5_EAM_DPP_COSTBASE_LINES");
$record_id2 = $record_id2 + 1;

//COST BASED
if (isset($_POST['submit_cost'])){
$ref_no = $_POST['ref_no'];
$id = @$_POST['id_cost'];
$CMD_CODE = $_POST['CMD_CODE'];
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
//$year_budget = $_POST['year_budget'];
$description = $_POST['description_cost'];
$code = $_POST['code'];
$classification_cost = $_POST['classification_cost'];
$category_cost = $_POST['category_cost'];
$january = $_POST['january_cost'];
$february = $_POST['february_cost'];
$march =  $_POST['march_cost'];
$april =  $_POST['april_cost'];
$may =  $_POST['may_cost'];
$june = $_POST['june_cost'];
$july = $_POST['july_cost'];
$august = $_POST['august_cost'];
$september = $_POST['september_cost'];
$october = $_POST['october_cost'];
$november = $_POST['november_cost'];
$december = $_POST['december_cost'];
$type = $_POST['type'];

//Validation
if ($CMD_CODE == ""){
$errorMessage .= 'Please enter a Commodity code.\n\n';
$errorFlag = true;
}

//Check if same FI > 1
$cnd3 = "WHERE code = '$code' AND reference_no = '$ref_no'";
$Ctr = $crudapp->matchRecord2($conn,"R5_VIEW_COSTBASE_LINES",'id',$cnd3);
if ($Ctr > 0 && $id == ""){
$errorMessage .= 'Item already exist for this budget year.\n\n';
$errorFlag = true;
}


if (!is_numeric ($january) || !is_numeric ($february) || !is_numeric ($march) || !is_numeric ($april) || !is_numeric ($may) || !is_numeric ($june) || !is_numeric ($july) || !is_numeric ($august) || !is_numeric ($september) || !is_numeric ($october) || !is_numeric ($november) || !is_numeric ($december)){
$errorMessage .= 'Budget Month must be numeric characters only.\n\n';
$errorFlag = true;
}

$today = date("m/d/Y H:i");	
	if(!$errorFlag){
		$data = array("record_id"=>$record_id2,"id"=>$record_id2,"CMD_CODE"=>$CMD_CODE,"description"=>$description,"budget_amount"=>0,"available"=>0,"classification"=>$classification_cost,"category"=>$category_cost,"saveFlag"=>0,"version"=>1,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user,"code"=>$code,"type"=>$type);	
		$data2 = array("id"=>$record_id2,"january"=>$january,"february"=>$february,
		"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
		"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
		$data5 = array("reference_no"=>$ref_no,"rowid"=>$record_id2,"version"=>$version);	
		
		$table = "R5_EAM_DPP_COSTBASE_LINES";
		$table2 = "R5_REF_COSTBASE_BUDGET_MONTH";
		$table3 = "R5_EAM_DPP_COSTBASE_BRIDGE";
		
			$result = $crudapp->insertRecord($conn,$data,$table);
			$result2 = $crudapp->insertRecord($conn,$data2,$table2);
			$result3 = $crudapp->insertRecord($conn,$data5,$table3);	
		
			//if( $result == 1 && $result2 == 1) {
				sqlsrv_commit( $conn );
				//echo "Transaction committed.<br />";
			//} else {
				//sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
			//}
			header("Location:dpp-budget-movement.php?login=".$user."&year=".$year."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&cost_center=".$cost_center."&id=&from_id=".$from_id."&from_val=".$from_val."&to_id=".$record_id2."&to_val=".$to_val."&amount=".$amount."&movementType=reallocation&costcenterfr=".$costcenterfr."&department_id=".$frmrccode."&department_val=".$frmrcdesc."&source_tb=".$source_tb."&destination_tb=".$destination_tb);
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
<script src="js/jquery.min.js">
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
$('#id_cost').val(id);
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
	 var CMD_CODE = json['CMD_CODE'];
	 CMD_CODE = CMD_CODE.replace(/ /g,'');
	 var CMD_DESC = json['CMD_DESC'];
	 var budget_amount = json['Budget_Amount'];
	 var io_number = json['io_number'];
	 var description = json['Description'];
	 var code = json['code'];
	 var classification = json['Classification'];
	 var category = json['category'];
	 //category = category.trim();
	 category = category.replace(/ /g,'');
	 var jan = json['Jan'];
	 var feb = json['Feb'];
	 var mar = json['Mar'];
	 var apr = json['Apr'];
	 var may = json['May'];
	 var jun = json['Jun'];
	 var jul = json['Jul'];
	 var aug = json['Aug'];
	 var sept = json['Sept'];
	 var oct = json['Oct'];
	 var nov = json['Nov'];
	 var dec = json['Dec'];
	 
	 $('#CMD_CODE').val(CMD_CODE);
	  $('#costCommodity').val(CMD_DESC);
	 $('#io_number').val(io_number);
	 $('#budget_amount_cost').val(budget_amount);
	 $('#description_cost').val(description);
	 $('#code').val(code);
	 $('#classification_cost').val(classification);
	 $('#category_cost').val(category);
	 $('#categoryDisp').val(category);
	 $('#january_cost').val(jan);
	 $('#february_cost').val(feb);
	 $('#march_cost').val(mar);
	 $('#april_cost').val(apr);
	 $('#may_cost').val(may);
	 $('#june_cost').val(jun);
	 $('#july_cost').val(jul);
	 $('#august_cost').val(aug);
	 $('#september_cost').val(sept);
	 $('#october_cost').val(oct);
	 $('#november_cost').val(nov);
	 $('#december_cost').val(dec);	
	 getGLInfo(CMD_CODE);
    }
  }
xmlhttp.open("GET","ajax/app-get-costbase-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

//R5COMMODITIES TABLE
function valideopenerform2(obj){
	var description_cost = $('#description_cost').val();
	var id_cost = $('#id_cost').val();
	var code = $('#code').val();
	var classification_cost = $('#classification_cost').val();
	var CMD_CODE = $('#CMD_CODE').val();
	var costCommodity = $('#costCommodity').val();
	costCommodity = costCommodity.replace(/&/g, '%26');
	var january = $('#january_cost').val();
	var february = $('#february_cost').val();
	var march = $('#march_cost').val();
	var april = $('#april_cost').val();
	var may = $('#may_cost').val();
	var june = $('#june_cost').val();
	var july = $('#july_cost').val();
	var august = $('#august_cost').val();
	var september = $('#september_cost').val();
	var october = $('#october_cost').val();
	var november = $('#november_cost').val();
	var december = $('#december_cost').val();	
		var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
var popup= window.open('popup7.php?hash='+text+'&obj='+obj+'&description_cost='+description_cost+'&costCommodity='+costCommodity+'&classification_cost='+classification_cost+''
+'&january='+january+'&february='+february+'&march='+march+'&april='+april+'&may='+may+'&june='+june+'&july='+july+''
+'&august='+august+'&september='+september+'&october='+october+'&november='+november+'&december='+december+'&code='+code+'&CMD_CODE='+CMD_CODE+'&id_cost='+id_cost+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}

//R5COMMODITIES TABLE
function valideopenerform3(obj){
	var description_cost = $('#description_cost').val();
	var code = $('#code').val();
	var id_cost = $('#id_cost').val();
	var classification_cost = $('#classification_cost').val();
	//var category_cost = $('#category_cost').val();
	var costCommodity = $('#costCommodity').val();
	var january = $('#january_cost').val();
	var february = $('#february_cost').val();
	var march = $('#march_cost').val();
	var april = $('#april_cost').val();
	var may = $('#may_cost').val();
	var june = $('#june_cost').val();
	var july = $('#july_cost').val();
	var august = $('#august_cost').val();
	var september = $('#september_cost').val();
	var october = $('#october_cost').val();
	var november = $('#november_cost').val();
	var december = $('#december_cost').val();	

		var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
var popup= window.open('popup6.php?hash='+text+'&obj='+obj+'&description_cost='+description_cost+'&costCommodity='+costCommodity+'&classification_cost='+classification_cost+''
+'&january='+january+'&february='+february+'&march='+march+'&april='+april+'&may='+may+'&june='+june+'&july='+july+''
+'&august='+august+'&september='+september+'&october='+october+'&november='+november+'&december='+december+'&code='+code+'&id_cost='+id_cost+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}

function getTotalCost(){
	var jan = $('#january').val();
		var feb = $('#february').val();
		var mar = $('#march').val();
		var apr = $('#april').val();
		var may = $('#may').val();
		var jun = $('#june').val();
		var jul = $('#july').val();
		var aug = $('#august').val();
		var sept = $('#september').val();
		var oct = $('#october').val();
		var nov = $('#november').val();
		var dec = $('#december').val();
		
		var qty = parseFloat(jan) + parseFloat(feb) + parseFloat(mar) + parseFloat(apr) + 
		parseFloat(may) + parseFloat(jun) + parseFloat(jul) + parseFloat(aug) + parseFloat(sept) + 
		parseFloat(oct) + parseFloat(nov) + parseFloat( dec);
		
		$("#quantity").val(qty);
		$("#quantity_val").val(qty);
		var total_cost = parseFloat($('#quantity').val()) * parseFloat($('#unit_cost').val());
		$("#cost").val(total_cost);
}

function getItemInfo(){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var costCommodity = json['CMD_DESC'];
	 var classification = json['PAR_UDFCHAR07'];
	 var gl = json['gl'];
	 var gl_description = json['gl_description'];
	 var PAR_COMMODITY = json['PAR_COMMODITY'];
	 
	 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo $cost_center;?>';
	 
	 $('#costCommodity').val(costCommodity);
	 $('#classification_cost').val(classification);
	 $('#CMD_CODE').val(PAR_COMMODITY);
	 $('#gl_code').val(gl);
	 $('#gl_description').val(gl_description);
	 $('#io_number').val(io_number);
    }
  }
xmlhttp.open("GET","ajax/app-record-create-cost.php?hash="+text+"&code="+code,true);
xmlhttp.send();
}


function getGLInfo(CMD_CODE){
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
	 var gl_code = json['GL_Code'];
	 var gl_description = json['GL_Description'];
	 var category = json['category'];
	 category = category.replace(/ /g, '');
	 $('#gl_code').val(gl_code);
	 $('#gl_description').val(gl_description);
	 $('#category_cost').val(category);
	 $('#categoryDisp').val(category);	 
	 
	 getIONumber();
    }
  }
xmlhttp.open("GET","ajax/app-get-gl-info2.php?hash="+text+"&CMD_CODE="+CMD_CODE,true);
xmlhttp.send();
}

function getIONumber(){
 var gl = $('#gl_code').val();
 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo $cost_center;?>';
	$('#io_number').val(io_number);	
}


$(document).ready(function(){

//Error Message
var res = "<?php echo @$res;?>";
var year = "<?php echo @$year;?>";

$('.isa_info').show();
if(res !=""){
	if (res == "pass"){
		$('.isa_success').show();
		$('.isa_error').hide();
	}else {
		$('.isa_error').show();
		$('.isa_success').hide();
	}
}

	
	$("#year_budget").val(year);
	

	$(".scheduleField").change(function() {
		getTotalCost();
	});
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&mrccode=".$mrccode."&org_code=".$orgcode."&cost_center=".$cost_center."&from_val=".$from_val."&from_id=".$from_id."&frmrccode=".$frmrccode."&costcenterfr=".$costcenterfr."&mrcdesc=".$frmrcdesc."&source_tb=".$source_tb."&destination_tb=".$destination_tb; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">Annual Procurement Plan</div></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField"><input type="hidden" class="field" name="ref_no" id="ref_no" spellcheck="false" tabindex="1" value= "<?php echo $reference_no;?>"><input type="text" class="field" name="organization" id="organization" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['ORG_DESC'];?>" readonly><input type="hidden" class="field" name="ORG_CODE" id="ORG_CODE" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['ORG_CODE'];?>"></td>			
			<td class="textLabel">Year Budget:</td>
			<td class="textField">
				<select name="year_budget" id="year_budget" readonly>
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
		<tr>
			<td class="textLabel">Department:</td>
			<td class="textField"><input type="text" class="field" name="department" id="department" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['MRC_DESC'];?>" readonly><input type="hidden" class="field" name="MRC_CODE" id="MRC_CODE" spellcheck="false" tabindex="1" value= "<?php echo $dppinfo[0]['MRC_CODE'];?>"></td>				
			<td class="textLabel"></td>
			<td class="textField"></td>				
		</tr>
		<tr>
			<td class="textLabel">Cost Center:</td>
			<td class="textField"><input type="text" class="field" name="cost_center" id="cost_center" spellcheck="false" tabindex="1" value="<?php echo $cost_center; ?>" readonly></td>		
		</tr>
	</tbody>
</table>
</div>
<div class="headerText">Annual Procurement Plan Details</div>
	<!--APP Section-->
	<div class="formDiv">
	<div class="headerText">Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
		<tbody>
			<tr>		
				<td class="textLabel">Cost-Based Item: <i class="required">*</i></td>
				<td class="textField">
				<input type="text" class="fieldLookUp" name="description_cost" id="description_cost" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="code" id="code" spellcheck="false" tabindex="1" readonly><button name="cmd" onclick="valideopenerform2('R5_VIEW_SERVICE_UOM_INFO')">...</button>			
				<input type="hidden" class="field" name="id_cost" id="id_cost" spellcheck="false" tabindex="1">			
				</td>				
				<td class="textLabel">IO Number:</td>
				<td class="textField" colspan="3"><input type="text" class="field" name="io_number" id="io_number" spellcheck="false" tabindex="1" readonly></td>
			</tr>
			<tr>	

				<td class="textLabel">Type:</td>
				<td class="textField">
					<input type="text" class="field" name="type" id="type" spellcheck="false" tabindex="1">
				</td>
						
				<td class="textLabel">Service Type: <i class="required">*</i></td>
				<td class="textField">	
					<input type="text" class="fieldLookUp" name="costCommodity" id="costCommodity" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="CMD_CODE" id="CMD_CODE" spellcheck="false" tabindex="1" readonly><button name="cmd" onclick="valideopenerform3('R5_VIEW_COMMODITIES')">...</button>
				</td>						
			</tr>
			<tr>	

				<td class="textLabel">GL Code: <i class="required">*</i></td>
				<td class="textField">
					<input type="text" class="field" name="gl_code" id="gl_code" spellcheck="false" tabindex="1" readonly>						
				</td>				
			
			<td class="textLabel">Category: <i class="required">*</i></td>
				<td class="textField">
					<input type="hidden" class="field" name="category_cost" id="category_cost" spellcheck="false" tabindex="1">
					<input type="text" class="field" name="categoryDisp" id="categoryDisp" spellcheck="false" tabindex="1" readonly>			
				</td>			
			</tr>
			<tr>
			<td class="textLabel">GL Description:</td>
			<td class="textField"><input type="text" class="field" name="gl_description" id="gl_description" spellcheck="false" tabindex="1" readonly></td>
			
				<td class="textLabel">Classification:</td>
				<td class="textField">
					<select name="classification_cost" id="classification_cost">
						<option value=''>-- Please select --</option>
						<option name="classification" value="FDC">FDC</option>
						<option name="classification" value="Proprietary">Proprietary</option>
						<option name="classification" value="Non-Proprietary">Non-Proprietary</option>
					</select>
				</td>							
			</tr>
		</tbody>
	</table>
	<!--DATE of NEED-->
	
	<table border="1" class="schedule">
	<tr>
		<th>Jan</th>
		<th>Feb</th>
		<th>Mar</th>
		<th>Apr</th>
		<th>May</th>
		<th>Jun</th>
		<th>Jul</th>
		<th>Aug</th>
		<th>Sept</th>
		<th>Oct</th>
		<th>Nov</th>
		<th>Dec</th>
	</tr>
	<tr>
		<td><input type="text" class="scheduleField" name="january_cost" id="january_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="february_cost" id="february_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="march_cost" id="march_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="april_cost" id="april_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="may_cost" id="may_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="june_cost" id="june_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="july_cost" id="july_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="august_cost" id="august_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="september_cost" id="september_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="october_cost" id="october_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="november_cost" id="november_cost" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="december_cost" id="december_cost" spellcheck="false" tabindex="1" value="0"></td>
	</tr>
	</table>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit_cost" id="submit" value=" Save ">&nbsp;&nbsp;
		<input type="button" value=" Clear " Onclick="cancel(this.form)">&nbsp;&nbsp;
	</div>
	</div>
	
</div>
</form>
</body>
</html>  
