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

//ITEMBASE
$column = $crudapp->readColumn($conn,"R5_VIEW_ITEMBASE_LINES");
$record_id = $crudapp->readID($conn,"R5_EAM_DPP_ITEMBASE_LINES");
$record_id = $record_id + 1;

$versionExist = $crudapp->checkRecordExist($conn,"R5_DPP_VERSION","ORG_CODE = '$orgcode' AND MRC_CODE = '$mrccode' AND year_budget = '$year'");

//ITEM BASED
if (isset($_POST['submit'])){
$ref_no = $_POST['ref_no'];
$id = $_POST['id'];
$code = $_POST['code'];
$ORG_CODE = $_POST['ORG_CODE'];
$MRC_CODE = $_POST['MRC_CODE'];
$quantity = $_POST['quantity_val'];
$january = $_POST['january'];
$february = $_POST['february'];
$march =  $_POST['march'];
$april =  $_POST['april'];
$may =  $_POST['may'];
$june = $_POST['june'];
$july = $_POST['july'];
$august = $_POST['august'];
$september = $_POST['september'];
$october = $_POST['october'];
$november = $_POST['november'];
$december = $_POST['december'];

//Validation
if ($code == ""){
$errorMessage .= 'Please select an Item.\n\n';
$errorFlag = true;
}

//Check if same FI > 1
$cnd3 = "WHERE code = '$code' AND reference_no = '$ref_no'";
$Ctr = $crudapp->matchRecord2($conn,"R5_VIEW_ITEMBASE_LINES",'id',$cnd3);
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
		$data = array("record_id"=>$record_id,"id"=>$record_id,"code"=>$code,"quantity"=>$quantity,"saveFlag"=>0,"version"=>1,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user,"unit_cost"=>0.00,"total_cost"=>0.00,"foreign_cost"=>0.00,"foreign_curr"=>'PHP');	
		$data2 = array("id"=>$record_id,"january"=>$january,"february"=>$february,
		"march"=>$march,"april"=>$april,"may"=>$may,"june"=>$june,"july"=>$july,
		"august"=>$august,"september"=>$september,"october"=>$october,"november"=>$november,"december"=>$december,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);
		$data5 = array("reference_no"=>$reference_no,"rowid"=>$record_id,"version"=>$version);	
		
		$table = "R5_EAM_DPP_ITEMBASE_LINES";
		$table2 = "R5_REF_ITEMBASE_BUDGET_MONTH";
		$table3 = "R5_EAM_DPP_ITEMBASE_BRIDGE";
		
		$result = $crudapp->insertRecord($conn,$data,$table);
		$result2 = $crudapp->insertRecord($conn,$data2,$table2);
		$result3 = $crudapp->insertRecord($conn,$data5,$table3);
		
			//if( $result == 1 && $result2 == 1 && $result3 == 1) {
				sqlsrv_commit( $conn );
				//echo "Transaction committed.<br />";
			//} else {
				//sqlsrv_rollback( $conn );
				//echo "Transaction rolled back.<br />";
			//}
			header("Location:dpp-budget-movement.php?login=".$user."&year=".$year."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&cost_center=".$cost_center."&id=&from_id=".$from_id."&from_val=".$from_val."&to_id=".$record_id."&to_val=".$to_val."&amount=".$amount."&movementType=reallocation&costcenterfr=".$costcenterfr."&department_id=".$frmrccode."&department_val=".$frmrcdesc."&source_tb=".$source_tb."&destination_tb=".$destination_tb);
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
//EAM TABLE
function valideopenerform2(obj){	

	var code = $('#code').val();
	var quantity = $('#quantity').val();
	var item_val = $('#item_val').val();
	var january = $('#january').val();
	var february = $('#february').val();
	var march = $('#march').val();
	var april = $('#april').val();
	var may = $('#may').val();
	var june = $('#june').val();
	var july = $('#july').val();
	var august = $('#august').val();
	var september = $('#september').val();
	var october = $('#october').val();
	var november = $('#november').val();
	var december = $('#december').val();
	var source_tb = "<?php echo $source_tb; ?>";
	var destination_tb = "<?php echo $destination_tb; ?>";
	

			var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
		
var popup= window.open('popup5.php?hash='+text+'&obj='+obj+'&code='+code+'&item_val='+item_val+'&quantity='+quantity+''
+'&january='+january+'&february='+february+'&march='+march+'&april='+april+'&may='+may+'&june='+june+'&july='+july+''
+'&august='+august+'&september='+september+'&october='+october+'&november='+november+'&december='+december+'&source_tb='+source_tb+'&destination_tb='+destination_tb+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 


}



function getItemInfo(){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
//code = code.replace(/ /g, '');
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var unit_cost = json['PAR_BASEPRICE'];
	 //Protect Unit Cost if base price is greater than 0
	 var description = json['PAR_DESC'];
	 var itemtype = json['CMD_DESC'];classification
	 var classification = json['PAR_UDFCHAR07'];
	 var PAR_LASTPRICE = json['PAR_LASTPRICE'];
	 var uom = json['UOM_DESC'];
	 var gl = json['gl'];
	 var gl_description = json['gl_description'];
	 
	 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo $cost_center;?>';
	 
	 $('#unit_cost').val(unit_cost);
	 if (unit_cost > 0){
	   $('#unit_cost').attr('readonly', 'true'); // mark it as read only
	   $('#CUR_CODE').val('PHP');
	   $('#CUR_CODE').attr('disabled', 'true'); // mark it as read only
	 }else{
	   $('#unit_cost').removeAttr('readonly'); // mark it as read only
	   $('#CUR_CODE').val('PHP');
	   $('#unit_cost').val('0.00');
	   $('#CUR_CODE').removeAttr('disabled'); // mark it as read only	 
	 }
	 //$('#unit_cost_val').val(unit_cost);
	 $('#description').val(description);
	 $('#PAR_LASTPRICE').val(PAR_LASTPRICE);
	 $('#itemtype').val(itemtype);
	 $('#classification').val(classification);
	 $('#uom').val(uom);
	 $('#itemGL').val(gl);
	 $('#gl_description').val(gl_description);
	 $('#io_number').val(io_number);
	 getTotalCost();
    }
  }
xmlhttp.open("GET","ajax/app-record-create.php?hash="+text+"&code="+code,true);
xmlhttp.send();
}


function getItemInfo2(unit_cost){
//HASH - To random string that will reload pages with ajax call
var text = "";
var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
for( var i=0; i < 5; i++ )
text += possible.charAt(Math.floor(Math.random() * possible.length));
var code = $("#code").val();
//code = code.replace(/ /g, '');
code = $.trim(code);
var xmlhttp=new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	//alert(xmlhttp.responseText);
	 var json = $.parseJSON(xmlhttp.responseText);
	 var unit_cost2 = json['PAR_BASEPRICE'];
	 //Protect Unit Cost if base price is greater than 0
	 var description = json['PAR_DESC'];
	 var itemtype = json['CMD_DESC'];classification
	 var classification = json['PAR_UDFCHAR07'];
	 var PAR_LASTPRICE = json['PAR_LASTPRICE'];
	 var uom = json['UOM_DESC'];
	 var gl = json['gl'];
	 var gl_description = json['gl_description'];
	 
	 if (gl != " " && gl != "null" && gl != null){
	 gl = gl.replace(/ /g, '');
	 }else{
	 gl = "0000000";
	 }
	 var io_number = '<?php echo $lastDigit;?>'+gl+'<?php echo $cost_center;?>';
	 
	 $('#unit_cost').val(unit_cost);
	 $('#CUR_CODE').val('PHP');
	 if (unit_cost2 > 0){
	   $('#unit_cost').attr('readonly', 'true'); // mark it as read only
	     $('#CUR_CODE').attr('disabled', 'true'); // mark it as read only
	 }else if (unit_cost2 == null){
	   $('#unit_cost').removeAttr( "readonly" ); // mark it as read only
	   $('#CUR_CODE').removeAttr( "disabled" ); // mark it as read only
	 }
	
	 //$('#unit_cost_val').val(unit_cost);
	 $('#description').val(description);
	 $('#PAR_LASTPRICE').val(PAR_LASTPRICE);
	 $('#itemtype').val(itemtype);
	 $('#classification').val(classification);
	 $('#uom').val(uom);
	 $('#itemGL').val(gl);
	 $('#gl_description').val(gl_description);
	 $('#io_number').val(io_number);
	 getTotalCost();
    }
  }
xmlhttp.open("GET","ajax/app-record-create.php?hash="+text+"&code="+code,true);
xmlhttp.send();
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
	
	$("#CUR_CODE").change(function() {
		var curr_code = $(this).val();
		$("#CUR_CODE_VAL").val(curr_code);
	});

	$("#unit_cost").change(function() {
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
	<div class="headerText">Item Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
		<tbody>
			<tr>
				<td class="textLabel">Item Code: <i class="required">*</i></td>
				<td class="textField"><input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="code" id="code" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="item_val" id="item_val" spellcheck="false" tabindex="1" readonly><button name="ItemCode" onclick="valideopenerform2('R5_VIEW_PARTS_UOM_INFO')">...</button></td>			
				
				<td class="textLabel">Item Classification:</td>
				<td class="textField">
					<input type="text" class="field" name="classification" id="classification" spellcheck="false" tabindex="1" readonly>
				</td>	
			</tr>
			<tr>
				<td class="textLabel">Item Description: </td>
				<td class="textField"><input type="text" class="field" name="description" id="description" spellcheck="false" tabindex="1" readonly></td>				
				<td class="textLabel">IO Number:</td>
				<td class="textField" colspan="3"><input type="text" class="field" name="io_number" id="io_number" spellcheck="false" tabindex="1" readonly>
				</td>				
			</tr>
			<tr>
				<td class="textLabel">Commodity:</td>
				<td class="textField">
				<input type="text" class="field" name="itemtype" id="itemtype" spellcheck="false" tabindex="1" readonly></td>								
				<td class="textLabel">Unit of Measure:</td>
				<td class="textField"><input type="text" class="field" name="uom" id="uom" spellcheck="false" tabindex="1" readonly></td>						
			</tr>
			<tr>
				
				<td class="textLabel">GL Account:</td>
				<td class="textField">
					<input type="text" class="field" name="itemGL" id="itemGL" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textLabel">Quantity:</td>
				<td class="textField"><input type="text" class="field" name="quantity" id="quantity" spellcheck="false" tabindex="1" value="0" readonly><input type="hidden" class="field" name="quantity_val" id="quantity_val" spellcheck="false" tabindex="1" value="0"></td>						
			</tr>
			<tr>
				
				<td class="textLabel">GL Description:</td>
				<td class="textField">
					<input type="text" class="field" name="gl_description" id="gl_description" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textLabel">Last Price:</td>
				<td class="textField"><input type="text" class="field" name="PAR_LASTPRICE" id="PAR_LASTPRICE" spellcheck="false" tabindex="1" readonly></td>								
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
		<td><input type="text" class="scheduleField" name="january" id="january" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="february" id="february" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="march" id="march" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="april" id="april" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="may" id="may" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="june" id="june" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="july" id="july" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="august" id="august" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="september" id="september" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="october" id="october" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="november" id="november" spellcheck="false" tabindex="1" value="0"></td>
		<td><input type="text" class="scheduleField" name="december" id="december" spellcheck="false" tabindex="1" value="0"></td>
	</tr>
	</table>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit" id="submit" value=" Save ">&nbsp;&nbsp;
		<input type="button" value=" Clear " Onclick="cancel(this.form)">&nbsp;&nbsp;
	</div>
	</div>
</div>
</form>
</body>
</html>  
