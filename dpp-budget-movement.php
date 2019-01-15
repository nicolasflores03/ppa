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
$mrccode = @$_GET['MRC_CODE'];
$orgcode = @$_GET['ORG_CODE'];
//-----
$id = @$_GET['id'];
$from_id = @$_GET['from_id'];
$from_val = @$_GET['from_val'];
$to_id = @$_GET['to_id'];
$to_val = @$_GET['to_val'];
$amount = @$_GET['amount'];
$movementType = @$_GET['movementType'];
$costcenterfr = @$_GET['costcenterfr'];
$department_id =  @$_GET['department_id'];
$department_val =  @$_GET['department_val'];
$source_tb =  @$_GET['source_tb'];
$destination_tb =  @$_GET['destination_tb'];

$fr_quarter_tb =  @$_GET['fr_quarter_tb'];
$to_quarter_tb =  @$_GET['to_quarter_tb'];
$to_org_code =  @$_GET['to_org_code'];


$cost_center = @$_GET['cost_center'];
$cost_center = str_replace(" ","@",$cost_center);

$total_available = 0.00;
$q1_available = 0.00;
$q2_available = 0.00;
$q3_available = 0.00;
$q4_available = 0.00;

$current_date = new DateTime("now");

$q1_deadline = false;
$q2_deadline = false;
$q3_deadline = false;
$q4_deadline = false;

$orgInfo = array();
if($orgcode){
	$parameters = array('ORG_CODE', 'ORG_DESC');
	$org_where = " USR_CODE = '$user' AND ORG_CODE = '$orgcode' ";
	if($mrccode) {
		array_push($parameters, 'MRC_DESC');
		array_push($parameters, 'MRC_CODE');
		$org_where .= " AND MRC_CODE IN (SELECT MRC_CODE FROM R5_DPP_APPROVED_DEPT WHERE ORG_CODE = '$orgcode' AND year_budget = '$year') AND MRC_CODE = '$mrccode'"; 
	}

	$_orgInfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO", $parameters, $org_where );
	if(count($_orgInfo) > 0) {
		$orgInfo = $_orgInfo[0];
	}
}

//get closed date per quarters
if(isset($_GET['year'])) {
	$condition = " budget_year = '$year' AND isActive = '1'";
	// $crudapp = new crudClass();
	$deadLineInfoColumn = array('Q1', 'Q2', 'Q3', 'Q4');
	$quarterDeadlineInfo = $crudapp->listTable($conn,"R5_DEADLINE_MAINTENANCE",$deadLineInfoColumn,$condition);

	$q1_deadline = isset($quarterDeadlineInfo[0]['Q1']) ? ($quarterDeadlineInfo[0]['Q1'] > 0 ? true : false ) : false;
	$q2_deadline = isset($quarterDeadlineInfo[0]['Q2']) ? ($quarterDeadlineInfo[0]['Q2'] > 0 ? true : false ) : false;
	$q3_deadline = isset($quarterDeadlineInfo[0]['Q3']) ? ($quarterDeadlineInfo[0]['Q3'] > 0 ? true : false ) : false;
	$q4_deadline = isset($quarterDeadlineInfo[0]['Q4']) ? ($quarterDeadlineInfo[0]['Q4'] > 0 ? true : false ) : false;
}

$from_data = array();
if(isset($_GET['movementType']))  {
	$source_quarter = isset($_GET['source_quarter']) ? $_GET['source_quarter'] : '';
	$destination_quarter = isset($_GET['destination_quarter']) ?  $_GET['destination_quarter'] : '';
	$to_org_code = isset($_GET['to_org_code']) ?  $_GET['to_org_code'] : '';
	$proceed_budget_query = false;
	if($movementType == "reallocation" && isset($_GET['from_id'])){
		$cnd = "year_budget = '$year' AND ORG_CODE = '$to_org_code' AND MRC_CODE = '$department_id' AND status = 'Approved' AND cost_center = '$costcenterfr'";
		$proceed_budget_query = true;
	} else if($movementType == "supplement" && isset($_GET['to_id'])) {
		$cnd = "year_budget = '$year' AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$mrccode' AND status = 'Approved' AND cost_center = '$cost_center'";
		$proceed_budget_query = true;
	}
	
	if($proceed_budget_query) {
		$from_data = $crudapp->readRecord3($conn,"R5_BUDGET_REALLOCATION_LOOKUP",$cnd);
		if(count($from_data) > 0) {
			$total_available = $from_data[0]["available"];
			$q1_available = $from_data[0]["q1_available"];
			$q2_available = $from_data[0]["q2_available"];
			$q3_available = $from_data[0]["q3_available"];
			$q4_available = $from_data[0]["q4_available"];
		}
	
	}
}
// end closed date per quarters

//GET status Based on reference_no,dept,org,year
$appfilter = "year_budget = '$year' AND ORG_CODE = '$orgcode' AND status = 'Approved'";
$appcolumn = $crudapp->readColumn($conn,"R5_APP_VERSION");
$appinfo = $crudapp->listTable($conn,"R5_APP_VERSION",$appcolumn,$appfilter);
@$app_id = $appinfo[0]['app_id'];

$filter = array();
$cnd = "year_budget = '$year' AND cost_center = '$cost_center' ORDER BY ID DESC";
$column = $crudapp->readColumn($conn,"R5_VIEW_BUDGET_MOVEMENT");
$requiredField = array('id','Source_Department','Source', 'source_quarter','Destination_Organization','Destination_Department','Destination', 'destination_quarter','amount','year_budget', 'type','status','reason');
$column = array_intersect($column,$requiredField);

$listView = $crudapp->listTable($conn,"R5_VIEW_BUDGET_MOVEMENT",$requiredField,$cnd);
$tableView = $filterapp->filterViewURLXdeleteID($conn,$requiredField,$listView,$filter,"id");


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
	$to_code =  $_POST['to_id'];
	$year_budget =  $_POST['year_budget'];
	$amount =  $_POST['amount'];
	$amount = str_replace(",","",$amount);
	$type =  @$_POST['movementType'];
	$department_id =  $_POST['department_id'];
	$cost_center = $_POST['CST_CODE'];
	$costcenterfr = $_POST['costcenterfr'];
	$reason = $_POST['reason'];
	$source_tb = $_POST['source_tb'];
	$destination_tb = $_POST['destination_tb'];
	$status = $_POST['status'];
	$to_quarter_tb = $_POST['to_quarter_tb'];
	$fr_quarter_tb = $_POST['fr_quarter_tb'];
	$to_org_code = $_POST['to_org_code'];

	
	if ($type == ""){
	$errorMessage .= 'Please select a movement type.\n\n';
	$errorFlag = true;
	}else{
		if ($type == "reallocation" && $fr_code == ""){
		$errorMessage .= 'Please select a source of budget item.\n\n';
		$errorFlag = true;
		}else if ($type == "reallocation" && $fr_code != ""){
			if ($department_id == ""){
			$errorMessage .= 'Please select a source department.\n\n';
			$errorFlag = true;
			}else{
				if ($costcenterfr == ""){
				$errorMessage .= 'Please select a cost center for the source department.\n\n';
				$errorFlag = true;
				}
			}
		}

		
		if ($type == "reallocation" && $to_code == ""){
		$errorMessage .= 'Please select a destination of budget item.\n\n';
		$errorFlag = true;
		}
		
		if ($type == "supplement" && $to_code == ""){
		$errorMessage .= 'Please select a destination of budget item.\n\n';
		$errorFlag = true;
		}

		if ($type == "reallocation" && $to_org_code == ""){
			$errorMessage .= 'Please select a target organization .\n\n';
			$errorFlag = true;
		}

		if ($type == "reallocation" && $fr_quarter_tb == ""){
			$errorMessage .= 'Please select a source quarter .\n\n';
			$errorFlag = true;
		}

		if ($to_quarter_tb == ""){
			$errorMessage .= 'Please select a target quarter .\n\n';
			$errorFlag = true;
		}
	}
	
	if ($amount == ""){
	$errorMessage .= 'Please enter a valid amount.\n\n';
	$errorFlag = true;
	}

	if ($amount == ""){
		$errorMessage .= 'Please enter a reason for this request.\n\n';
		$errorFlag = true;
	}
	
	if(!$errorFlag){	
		$table = "dbo.R5_BUDGET_MOVEMENT";
		$today = date("m/d/Y H:i");	
		$data = array("app_id"=>$app_id,"ORG_CODE"=>$orgcode,"TO_MRC_CODE"=>$mrccode,"FR_MRC_CODE"=>$department_id,"fr_code"=>$fr_code,"to_code"=>$to_code,"amount"=>$amount,"year_budget"=>$year_budget,"type"=>$type,"status"=>"Created","cost_center"=>$cost_center,"updatedAt"=>$today,"fr_cost_center"=>$costcenterfr,"reason"=>$reason,"fr_table"=>$source_tb,"to_table"=>$destination_tb, "to_quarter"=>$to_quarter_tb, "to_org_code" => null);
		if($id != ""){
			if($status != "") {
				// $data = array("app_id"=>$app_id,"ORG_CODE"=>$orgcode,"TO_MRC_CODE"=>$mrccode,"FR_MRC_CODE"=>$department_id,"fr_code"=>$fr_code,"to_code"=>$to_code,"amount"=>$amount,"year_budget"=>$year_budget,"type"=>$type,"status"=>$status,"cost_center"=>$cost_center,"updatedAt"=>$today,"fr_cost_center"=>$costcenterfr,"reason"=>$reason,"fr_table"=>$source_tb,"to_table"=>$destination_tb, "to_quarter"=>$to_quarter_tb);
				$data["status"] = $status;
			} 
			
			if ($type == "reallocation"){
				$data["to_org_code"] = $to_org_code;
			}

			$result2 = $crudapp->updateRecord($conn,$data,$table,"id",$id);
		
		}else{
		
			$record_id = $crudapp->readID($conn,"R5_BUDGET_MOVEMENT");
			$id = $record_id + 1;
			$data = array("app_id"=>$app_id,"ORG_CODE"=>$orgcode,"TO_MRC_CODE"=>$mrccode,"FR_MRC_CODE"=>$department_id,"fr_code"=>$fr_code,"to_code"=>$to_code,"amount"=>$amount,"year_budget"=>$year_budget,"type"=>$type,"status"=>"Created","cost_center"=>$cost_center,"createdBy"=>$user,"createdAt"=>$today,"updatedAt"=>$today,"fr_cost_center"=>$costcenterfr,"reason"=>$reason,"fr_table"=>$source_tb,"to_table"=>$destination_tb, "to_quarter"=>$to_quarter_tb, "fr_quarter"=>$fr_quarter_tb, "to_org_code"=>null);
			if ($type == "reallocation"){
				$data["to_org_code"] = $to_org_code;
			}
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
			$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$mrccode' AND ORG_CODE = '$orgcode' AND DH = 1) AND ORG_CODE = '$orgcode' AND MRC_CODE = '$mrccode'";
			$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
			$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
			$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
			
			/*
			if($cost_center == 'CC8001') {
				$crudapp->sentEmailCC($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
			} else {
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
			}
			*/
			
			$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
			
			echo "Transaction committed.<br />";
		} else {
			sqlsrv_rollback( $conn );
			echo "Transaction rolled back.<br />";
		}
						
		header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year_budget."&cost_center=".$cost_center."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&res=pass&msg=You have successfully saved the records!");
		
		echo $conn;
		echo $receiver;
		echo $subject;
		echo $body;
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
	 var FR_MRC_CODE = json['FR_MRC_CODE'];
	 var Destination_Department = json['Destination_Department'];
	 var TO_MRC_CODE = json['TO_MRC_CODE'];
	 var Source_Department = json['Source_Department'];
	 var fr_code = json['fr_code'];
	 var Source = json['Source'];
	 var to_code = json['to_code'];
	 var Destination = json['Destination'];
	 var amount = json['Amount'];
	 var year_budget = json['year_budget'];
	 var type = json['type'];
	 var fr_cost_center = json['fr_cost_center'];
	 var status = json['status'];
	 var to_table = json['to_table'];
	 var fr_table = json['fr_table'];
	 
	 var reason = json['reason'];
	 var remarks = json['remarks'];
	 var to_quarter_tb = json['to_quarter'];
	 var fr_quarter_tb = json['fr_quarter'];
	 var to_org_code = json['to_org_code'];
	 var responsible ='';


	 if (status != 'Approved'){
	  responsible = json['USR_DESC'];
	 }else{
	  responsible = '';
	 }
	 
	 
	 fr_cost_center = fr_cost_center.replace(/ /g, '');
	 status = status.replace(/ /g, '');
	 to_table = to_table.replace(/ /g, '');
	 fr_table = fr_table.replace(/ /g, '');

	 getFromCostCenter(FR_MRC_CODE,fr_cost_center);

	 $('#amount').val(amount);
	 $('#department_val').val(Source_Department);
	 $('#department_id').val(FR_MRC_CODE);
	 $('#year_budget').val(year_budget);
	 $('#to_id').val(to_code);
	 $('#from_id').val(fr_code);
	 $('#movementType').val(type);
	 $('#id').val(id);
	 $('#to_val').val(Destination);
	 $('#from_val').val(Source);
	 $('#reason').val(reason);
	 $('#remarks').val(remarks);
	 $('#responsible').val(responsible);

	 $('#source_tb').val(fr_table);
	 $('#destination_tb').val(to_table);
	 $('#to_quarter_tb').val(to_quarter_tb);
	 $('#fr_quarter_tb').val(fr_quarter_tb);
	 $('#to_org_code').val(to_org_code);

	 if (status == "RevisionRequest" || status == ""){
	 $('#select-status').css('visibility', 'visible');
	 $('.actionButtonCenter').show();
	 }else{
	 $('#select-status').css('visibility', 'hidden');
	 $('.actionButtonCenter').hide();
	 }
	 //movementType(type); 
	 movementTypeFields();
    }
  }
xmlhttp.open("GET","ajax/app-get-budget-movement-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

function getFromToInfo(id,column,table){
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
	 if (table == "IB"){
	 code = json['Description'];
	 }else{
	 code = json['description'];
	 }
	 var available = json['available'];
	 if (column =="to_code"){		 
		$('#from_val').val(code);
	 }else{
		$('#to_val').val(code);
	 }
  }
 }
xmlhttp.open("GET","ajax/app-get-itembase-info2.php?hash="+text+"&id="+id+"&table="+table,true);
xmlhttp.send();
}

function movementType(type){

	if (type == "reallocation"){
		// $('#budgetLabel').html('Available Budget:');
		$('#td_source_tb').show();
		$('#from').show();
		$('#td_destination_tb').show();
		$('#td_to_quarter_tb').show();
		$('#td_to_org_code_tb').show();
		$('#td_fr_quarter_tb').show();
		$('#to').show();
		$('#amount_movement').show();
		$('#department').show();
		$('#fr_cost_center').show();
		}else if (type == "supplement"){
		// $('#budgetLabel').html('Current Budget:');
		$('#from').hide();
		$('#td_source_tb').hide();
		$('#to').show();
		$('#td_destination_tb').show();
		$('#td_to_quarter_tb').show();
		$('#td_to_org_code_tb').hide();
		$('#td_fr_quarter_tb').hide();
		$('#amount_movement').show();
		$('#department').hide();
		$('#fr_cost_center').hide();
		}else{
		$('#from').hide();
		$('#to').hide();
		$('#td_source_tb').hide();
		$('#td_destination_tb').hide();
		$('#td_to_quarter_tb').hide();
		$('#td_to_org_code_tb').hide();
		$('#td_fr_quarter_tb').hide();
		$('#amount_movement').hide();
		$('#department').hide();
		$('#fr_cost_center').hide();
		}
}
		
function valideopenerform(field){	
	var from_id = $('#from_id').val();
	var id = $('#id').val();
	var from_val = encodeURIComponent($('#from_val').val());
	var source_tb = $('#source_tb').val();
	var destination_tb = $('#destination_tb').val();
	var to_id = $('#to_id').val();
	var to_val = encodeURIComponent($('#to_val').val());
	var amount = $('#amount').val();
	var year_budget = $('#year_budget').val();
	var type = $('#movementType').val();
	var budget = $('#budget').val();
	var orgcode = "<?php echo $orgcode; ?>";
	var frmrccode = $('#department_id').val();
	var tomrccode = "<?php echo $mrccode; ?>";
	var mrcdesc = encodeURIComponent($('#department_val').val());
	var cost_center = $('#CST_CODE').val();
	var costcenterfr = $('#costcenterfr').val();
	var to_quarter_tb = $('#to_quarter_tb').val();
	var login = "<?php echo $user; ?>";

	var source_quarter = $('#fr_quarter_tb').val();
	var destination_quarter = $('#to_quarter_tb').val();
	var to_org_code = $('#to_org_code').val();
	
	var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
	
	var filename ="";	
	
	if (field == "from"){
		if (source_tb == "IB"){
			filename = "popup-reallocate";
		}else{
			filename = "popup-reallocate-cost";
		}
	}else if (field == "to"){
		if (destination_tb == "IB"){
			filename = "popup-reallocate";
		}else{
			filename = "popup-reallocate-cost";
		}
	}		

	if($("#movementType").val() == "reallocation" &&  field == "from") {
		orgcode = $("#to_org_code").val();
	}
		
	var popup= window.open(filename+'.php?hash='+text+'&from_id='+from_id+'&from_val='+from_val+'&field='+field+'&to_id='+to_id+'&orgcode='+orgcode+'&frmrccode='+frmrccode+'&tomrccode='+tomrccode+'&mrcdesc='+mrcdesc+''
	+'&to_val='+to_val+'&amount='+amount+'&year_budget='+year_budget+'&type='+type+'&budget='+budget+'&cost_center='+cost_center+'&costcenterfr='+costcenterfr+'&id='+id+'&login='+login+'&source_tb='+source_tb+'&destination_tb='+destination_tb+''
	+'&source_quarter='+source_quarter+'&destination_quarter='+destination_quarter+"&to_org_code="+to_org_code
	,'popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
	popup.focus(); 
}

function valideopenerform2(){	
	var from_id = $('#from_id').val();
	var id = $('#id').val();
	var from_val = encodeURIComponent($('#from_val').val());
	var to_id = $('#to_id').val();
	var destination_tb = $('#destination_tb').val();
	var source_tb = $('#source_tb').val();
	var to_val = encodeURIComponent($('#to_val').val());
	var amount = $('#amount').val();
	var year_budget = $('#year_budget').val();
	var type = $('#movementType').val();
	var budget = $('#budget').val();
	var orgcode = $("#to_org_code").val(); //"<?php echo $orgcode; ?>";
	var mrccode = "" //"<?php echo $mrccode; ?>";
	var cost_center = $('#CST_CODE').val();
		
var popup= window.open('popup-reallocate2.php?from_id='+from_id+'&from_val='+from_val+'&to_id='+to_id+'&orgcode='+orgcode+'&mrccode='+mrccode+''
+'&to_val='+to_val+'&amount='+amount+'&year_budget='+year_budget+'&type='+type+'&budget='+budget+'&cost_center='+cost_center+'&id='+id+'&source_tb='+source_tb+'&destination_tb='+destination_tb+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}


function getFromCostCenter(id,val){
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
	var htmlContent = '';
	// htmlContent += '<td class="textLabel">Source Cost Center:</td>';
	// htmlContent += '<td class="textField">';
	htmlContent += xmlhttp.responseText;
	// htmlContent += '</td>';	

		$('#fr_cost_center').html(htmlContent);
		setFromCostCenter(val);
  }
 }
xmlhttp.open("GET","ajax/get-cost-center-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

function setFromCostCenter(id){
$('#costcenterfr').val(id);
}

$(document).ready(function(){
	$("#movementType").attr("disabled", true);
	$("#ORG_CODE").val("<?php echo $orgcode;?>");
	$("#MRC_CODE").val("<?php echo $mrccode;?>");
	$("#CST_CODE").val('<?php echo $cost_center; ?>');
	$("#id").val('<?php echo $id; ?>');
	$("#from_id").val('<?php echo $from_id; ?>');
	$("#from_val").val('<?php echo $from_val; ?>');
	$("#to_id").val('<?php echo $to_id; ?>');
	$("#to_val").val('<?php echo $to_val; ?>');
	$("#department_id").val('<?php echo $department_id; ?>');
	$("#department_val").val('<?php echo $department_val; ?>');
	$("#source_tb").val('<?php echo $source_tb; ?>');
	$("#destination_tb").val('<?php echo $destination_tb; ?>');
	
	$("#fr_quarter_tb").val('<?php echo $fr_quarter_tb; ?>');
	$("#to_quarter_tb").val('<?php echo $to_quarter_tb; ?>');
	$("#to_org_code").val('<?php echo $to_org_code; ?>');


	if($("#to_id").val() != ""){ 
		var table = $("#destination_tb").val();
		getFromToInfo($("#to_id").val(),"to_code",table);
	}

	if($("#department_id").val() != ""){ 
		getFromCostCenter($("#department_id").val(),"<?php echo $costcenterfr; ?>");
	}

	$("#to_org_code").change(function() {
		$("#department_id").val('');
		$("#department_val").val('');
		$("#costcenterfr").html('<option value="">-- Please select --</option>');
		$('#from_id').val('');
		$('#from_val').val('');
	});

	$("#amount").val('<?php echo $amount; ?>');
	$("#movementType").val('<?php echo $movementType; ?>');
	$("#costcenterfr").val('<?php echo $costcenterfr; ?>');

		var orgCount = $('#ORG_CODE option').size();
		var mrcCount = $('#MRC_CODE option').size();
		

	var org = $("#ORG_CODE").val();
	if (org == ""){	
		if (orgCount == 2){
			$("#ORG_CODE")[0].selectedIndex=1;
			var org = $("#ORG_CODE").val();
			window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&year=$year&ORG_CODE="; ?>'+org; 
		}
	}

	var mrc = $("#MRC_CODE").val();
	if (mrc == ""){	
		if (mrcCount == 2){
			$("#MRC_CODE")[0].selectedIndex=1;
			var mrc = $("#MRC_CODE").val();
			window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&cost_center=$cost_center&year=$year&ORG_CODE=$orgcode&MRC_CODE="; ?>'+mrc;
		}
	}


		
	var cst = $("#CST_CODE").val();
	if (cst != ""){
		$("#movementType").attr("disabled", false);
	} else {
		$("#movementType").attr("disabled", true);
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
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=".$user."&cost_center=".$cost_center."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&year="; ?>'+yr; 
	});
	
	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&year=$year&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
	$("#MRC_CODE").change(function() {
		var MRC_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&cost_center=$cost_center&year=$year&ORG_CODE=$orgcode&MRC_CODE="; ?>'+MRC_CODE; 
	});
	
	
var type = $('#movementType').val();
		//$('#from_val').val("");
		//$('#to_val').val("");
		//$('#amount').val("");
		//$('#budget_fr').val('');
		//$('#budget_to').val('');
		
		if (type == "reallocation"){
		// $('#budgetLabel').html('Available Budget:');
		$('#from').show();
		$('#td_source_tb').show();
		$('#to').show();
		$('#td_destination_tb').show();
		$('#td_to_quarter_tb').show();
		$('#td_to_org_code_tb').show();
		$('#td_fr_quarter_tb').show();
		$('#amount_movement').show();
		$('#department').show();
		$('#fr_cost_center').show();
		}else if (type == "supplement"){
		// $('#budgetLabel').html('Current Budget:');
		$('#from').hide();
		$('#td_source_tb').hide();
		$('#to').show();
		$('#td_destination_tb').show();
		$('#td_to_quarter_tb').show();
		$('#td_to_org_code_tb').hide();
		$('#td_fr_quarter_tb').hide();
		$('#amount_movement').show();
		$('#department').hide();
		$('#fr_cost_center').hide();
		}else{
		$('#from').hide();
		$('#td_source_tb').hide();
		$('#to').hide();
		$('#td_destination_tb').hide();
		$('#td_to_quarter_tb').hide();
		$('#td_to_org_code_tb').hide();
		$('#td_fr_quarter_tb').hide();
		$('#amount_movement').hide();
		$('#department').hide();
		$('#fr_cost_center').hide();
		}


	$('#year_budget').change(function() {
		var from_id = $('#from_id').val('');
		var from_val = $('#from_val').val('');
		var to_id = $('#to_id').val('');
		var to_val = $('#to_val').val('');
		var amount = $('#amount').val(0.00);
		var budget = $('#budget').val('');
		var budget_fr = $('#budget_fr').val('');
		//var budget_to = $('#budget_to').val('');
	});
	
	
		//YEAR
	$("#CST_CODE").change(function() {
		var cost_center = $(this).val();
		if (cost_center != ""){
		$("#movementType").attr("disabled", false);
		}
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=".$user."&year=".$year."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&cost_center="; ?>'+cost_center; 
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

	movementTypeFields();
	$('#movementType').change(function() {
		movementTypeFields();
	});
	
	var user = "<?php echo $user; ?>";
	var year = "<?php echo $year; ?>";
	//Save Button
	$("#tabURL").click(function() {
		window.location = "dpp-budget-movement-cost.php?login="+user+"&year="+year;
	});
	
	$("#newRecord").click(function() {
		location.reload();
		// window.location = "dpp-budget-movement.php?login="+user+"&year="+year;
		// 	var url = "dpp-budget-movement.php?login="+user+"&year="+year+"&MRC_CODE="+MRC_CODE;
		// 	url = url + "&ORG_CODE="+ORG_CODE + "&cost_center=" + $("#CST_CODE").val();
		// 	window.location = url;
	});
	setBudgetValue("from");

	$("#amount").change(function() {
		changeAmount();
	});

	$('#source_tb').val("IB");
	$('#destination_tb').val("IB");
});

function changeAmount() {
	var amount = parseFloat($("#amount").val());
	var budget_to = parseFloat($("#budget_to").val());
	var budget = parseFloat($("#budget").val());

	var reallocated_to = parseFloat(budget_to + amount).toFixed(2);
	var reallocated_fr = parseFloat(budget - amount).toFixed(2);

	if(isNaN(reallocated_to)) {
		reallocated_to = 0;
	}

	if(isNaN(reallocated_fr)) {
		reallocated_fr = 0;
	}

	$("#reallocated_budget_to").val(reallocated_to);
	$("#reallocated_budget_fr").val(reallocated_fr);
}

function movementTypeFields(){
	$("#tr_quarter").show();
	// $("#tr_table").show();
	$("#tr_item").show();

	var type = $('#movementType').val();
		$('#to_org_code').val("");
		$('#departmen_id').val("");
		$('#departmen_val').val("");
		$("#td_reallocated_budget_fr").hide();
		$("#td_budget_to").hide();
		$("#th_from").hide();


		$('#from_val').val("");
		$('#to_val').val("");
		$('#to_id').val("");
		$('#from_id').val("");
		$('#amount').val(0.00);
		$('#budget').val("");
		$('#budget_fr').val("");
		//$('#budget_to').val("");
	
		if (type == "reallocation"){
			$('#to_org_code').val("<?php echo $orgcode; ?>");
			$('#department_id').val("<?php echo $mrccode; ?>");
			$('#department_val').val("<?php echo isset($orgInfo['MRC_DESC']) ? $orgInfo['MRC_DESC'] : ''; ?>");
			$("#td_reallocated_budget_fr").show();
			$("#td_budget_to").show();
			$("#th_from").show();

			getFromCostCenter("<?php echo $mrccode; ?>", "<?php echo $cost_center; ?>");

			// $('#budgetLabel').html('Available Budget:');
			$('#from').show();
			$('#td_source_tb').show();
			$('#to').show();
			$('#td_destination_tb').show();
			$('#td_to_quarter_tb').show();
			$('#td_to_org_code_tb').show();
			$('#td_fr_quarter_tb').show();
			$('#amount_movement').show();
			$('#department').show();
			$('#fr_cost_center').show();
		}else if (type == "supplement"){
			// $('#budgetLabel').html('Current Budget:');
			$('#from').hide();
			$('#td_source_tb').hide();
			$('#to').show();
			$('#td_destination_tb').show();
			$('#td_to_quarter_tb').show();
			$('#td_fr_quarter_tb').hide();
			$('#td_to_org_code_tb').hide();
			$('#amount_movement').show();
			$('#department').hide();
			$('#fr_cost_center').hide();
		}else{
			$("#tr_quarter").hide();
			// $("#tr_table").hide();
			$("#tr_item").hide();
	
			$('#from').hide();
			$('#to').hide();
			$('#td_source_tb').hide();
			$('#td_destination_tb').hide();
			$('#td_to_org_code_tb').hide();
			$('#td_to_quarter_tb').hide();
			$('#td_fr_quarter_tb').hide();
			$('#amount_movement').hide();
			$('#department').hide();
			$('#fr_cost_center').hide();
		}
}

function checkType(){
var type = $('#movementType').val();
	if (type == "reallocation"){
	// $('#budgetLabel').html('Available Budget:');
	$('#from').show();
	$('#td_source_tb').show();
	$('#to').show();
	$('#td_destination_tb').show();
	$('#amount_movement').show();
	$('#department').show();
	$('#fr_cost_center').show();
	}else if (type == "supplement"){
	// $('#budgetLabel').html('Current Budget:');
	$('#from').hide();
	$('#to').show();
	$('#td_source_tb').hide();
	$('#td_destination_tb').show();
	$('#amount_movement').show();
	$('#department').hide();
	$('#fr_cost_center').hide();
	
	}else{
	$('#from').hide();
	$('#to').hide();
	$('#td_source_tb').hide();
	$('#td_destination_tb').hide();
	$('#amount_movement').hide();
	$('#department').hide();
	$('#fr_cost_center').hide();
	}
}

function setBudgetValue(field) {
	if($("#movementType").val() != "") {
		var budget = "";
		if(field == "from"){
			var quarter = $("#fr_quarter_tb").val();
			switch(quarter){
				case "1":
					budget = $("#q1_budget").val();	
				break;
				case "2":
					budget = $("#q2_budget").val();
				break;
				case "3":
					budget = $("#q3_budget").val();
				break;
				case "4":
					budget = $("#q4_budget").val();
				break;
			}
			$("#budget").val(budget);
		} else {
			var quarter = $("#to_quarter_tb").val();
			switch(quarter){
				case "1":
					budget = $("#q1_budget_to").val();	
				break;
				case "2":
					budget = $("#q2_budget_to").val();
				break;
				case "3":
					budget = $("#q3_budget_to").val();
				break;
				case "4":
					budget = $("#q4_budget_to").val();
				break;
			}
			$("#budget_to").val(budget);
		}

		changeAmount();
	}
}

$("#destination_tb").change(function(e) {
	// $('#from_val').val("");
	// $('#to_val').val("");
	// $('#to_id').val("");
	// $('#from_id').val("");
	// $('#amount').val(0.00);
	// $('#budget').val("");
	// $('#budget_fr').val("");
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&cost_center=".$cost_center."&MRC_CODE=".$mrccode."&ORG_CODE=".$orgcode."&id=".$id."&from_id=".$from_id."&from_val=".$from_val."&to_id=".$to_id."&to_val=".$to_val."&amount=".$amount."&movementType=".$movementType."&costcenterfr=".$costcenterfr."&department_id=".$department_id."&department_val=".$department_val;?>" method="post" name="theForm" enctype="multipart/form-data">
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
		<tr>
			<td class="textLabel">Department:</td>
			<td class="textField">
			
						<?php 
							$tbname = "R5_VIEW_USERINFO";
							$tbfield = "MRC_CODE";
							$tbfield2 = "MRC_DESC";
							$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user' AND ORG_CODE = '$orgcode' AND MRC_CODE IN (SELECT MRC_CODE FROM R5_DPP_APPROVED_DEPT WHERE ORG_CODE = '$orgcode' AND year_budget = '$year')");
						?>
			
			</td>	
			<td class="textLabel">Cost Center:</td>
			<td class="textField">
				<?php 
						$tbname = "R5COSTCODES";
						$tbfield = "CST_CODE";
						$crudapp->optionValue2($conn,$tbname,$tbfield,"WHERE CST_CLASS = '$mrccode' AND CST_NOTUSED LIKE '-' AND CST_CODE COLLATE Latin1_General_CI_AS IN (SELECT cost_center FROM R5_DPP_APPROVED_DEPT WHERE ORG_CODE = '$orgcode' AND year_budget = '$year')");
				?>
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
	<div style="padding: 15px;">
		
		<div style="padding: 5px 0px; width: 100%; text-align: right;">
			<label style="font-size: 12px; font-weight:strong;">Movement Type<i class="required">*</i></label>
			<input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1">
			<select name="movementType" id="movementType">
				<option value="">-- Please select --</option>
				<option value="reallocation">Budget Re-Allocation</option>
				<option value="supplement">Budget Supplement</option>
			</select>
		</div>
		<table class="procurement" border="0" cellspacing="5px"  width="100%">

			<thead>
				<tr>
					<th class="textField" style="max-width:40%;">Column Name</th>
					<th class="textField" style="max-width:30%;" id="th_from">From:</th>
					<th class="textField" style="max-width:30%;">To:</th>
				</tr>
			</thead>
			<tbody>
			<tr >
				<td class="textLabel">Organization <i class="required">*</i></td>
				<td id="td_to_org_code_tb">
					<?php 
						$tbname = "R5_VIEW_USERINFO";
						$tbfield = "DISTINCT(ORG_CODE)";
						$tbfield2 = "ORG_DESC";
						$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user'", "to_org_code");
					?>
				</td>
				<td>
					<input type="text" value="<?php echo isset($orgInfo['ORG_DESC']) ? $orgInfo['ORG_DESC'] : ''; ?>" readonly />
				</td>
			</tr>
			<tr >
				<td class="textLabel">Department <i class="required">*</i></td>
				<td id="department" class="textField"><input type="hidden" class="field" name="department_id" id="department_id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="department_val" id="department_val" spellcheck="false" tabindex="1" readonly><button name="department" onclick="valideopenerform2(); return false;">...</button></td>
				<td>
					<input type="text" value="<?php echo isset($orgInfo['MRC_DESC']) ? $orgInfo['MRC_DESC'] : ''; ?>" readonly/>
				</td>
			</tr>
			<tr >
				<td class="textLabel">Cost Center <i class="required">*</i></td>
				<td id="fr_cost_center" class="textField" >				
					<select name="costcenterfr" id="costcenterfr">
					<option value="">-- Please select --</option>
					</select>
				</td>
				<td>
					<input type="text" value="<?php echo $cost_center; ?>" readonly/>
				</td>
			</tr>
			<tr id="tr_quarter">
				<td class="textLabel">Quarter <i class="required">*</i></td>
				<td id="td_fr_quarter_tb">
					<select name="fr_quarter_tb" id="fr_quarter_tb" onchange="setBudgetValue('from');">
						<option value="">-- Please select --</option>
						<option <?php echo  $q1_deadline ? "" : "disabled" ; ?> value="1">Q1 <?php echo  $q1_deadline ? "" : "(Closed)" ; ?></option>
						<option <?php echo  $q2_deadline ? "" : "disabled" ; ?> value="2">Q2 <?php echo  $q2_deadline ? "" : "(Closed)" ; ?></option>
						<option <?php echo  $q3_deadline ? "" : "disabled" ; ?> value="3">Q3 <?php echo  $q3_deadline ? "" : "(Closed)" ; ?></option>
						<option <?php echo  $q4_deadline ? "" : "disabled" ; ?> value="4">Q4 <?php echo  $q4_deadline ? "" : "(Closed)" ; ?></option>
					</select>
				</td>
				<td id="td_to_quarter_tb">
					<select name="to_quarter_tb" id="to_quarter_tb" onchange="setBudgetValue('to');">
						<option value="">-- Please select --</option>
						<option <?php echo $q1_deadline ? "" : "disabled"; ?> value="1">Q1 <?php echo $q1_deadline ? "" : "(Closed)"; ?></option>
						<option <?php echo $q2_deadline ? "" : "disabled"; ?> value="2">Q2 <?php echo $q2_deadline ? "" : "(Closed)"; ?></option>
						<option <?php echo $q3_deadline ? "" : "disabled"; ?> value="3">Q3 <?php echo $q3_deadline ? "" : "(Closed)"; ?></option>
						<option <?php echo $q4_deadline ? "" : "disabled"; ?> value="4">Q4 <?php echo $q4_deadline ? "" : "(Closed)"; ?></option>
					</select>
				</td>
			</tr>
			<tr id="tr_table" class="hidden">
				<td class="textLabel">Table <i class="required">*</i></td>
				<td id="td_source_tb">
					<select name="source_tb" id="source_tb">
						<option value="IB" selected>ITEM-BASED</option>
						<option value="CB">COST-BASED</option>
					</select>
				</td>
				<td id="td_destination_tb">
					<select name="destination_tb" id="destination_tb">
						<option value="IB" selected>ITEM-BASED</option>
						<option value="CB">COST-BASED</option>
					</select>
				</td>
			</tr>
			<tr id="tr_item">
				<td class="textLabel">Item <i class="required">*</i></td>
				<td id="from" class="textField"><input type="hidden" class="field" name="from_id" id="from_id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="from_val" id="from_val" spellcheck="false" tabindex="1" readonly><button name="ItemCode" onclick="valideopenerform('from'); return false;">...</button></td>
				<td id="to" class="textField"><input type="hidden" class="field" name="to_id" id="to_id" spellcheck="false" tabindex="1"><input type="text" class="fieldLookUp" name="to_val" id="to_val" spellcheck="false" tabindex="1" readonly><button name="ItemCode" onclick="valideopenerform('to'); return false;">...</button></td>
			</tr>

			<tr>
				<td class="textLabel" id="budgetLabel">Available Budget:</td>
				<td class="textField" id="td_budget_to"><input type="text" class="field" name="budget" id="budget" spellcheck="false" tabindex="1" readonly>
					<input type="hidden" class="field" name="budget_fr" id="budget_fr" spellcheck="false" tabindex="1"/>
					<input type='hidden' value='<?php echo $total_available;?>' id='total_budget' name='total_budget' />
					<input type='hidden' value='<?php echo $q1_available;?>' id='q1_budget' name='q1_budget' />
					<input type='hidden' value='<?php echo $q2_available;?>' id='q2_budget' name='q2_budget' />
					<input type='hidden' value='<?php echo $q3_available;?>' id='q3_budget' name='q3_budget' />
					<input type='hidden' value='<?php echo $q4_available;?>' id='q4_budget' name='q4_budget' />
					<!--<input type="hidden" class="field" name="budget_to" id="budget_to" spellcheck="false" tabindex="1">-->
				</td>	
				<td class="textField">
					<input type="text" value='0'class="field" name="budget_to" id="budget_to" spellcheck="false" tabindex="1" readonly>
					<input type='hidden' value='0' id='total_budget_to' name='total_budget_to' />
					<input type='hidden' value='0' id='q1_budget_to' name='q1_budget_to' />
					<input type='hidden' value='0' id='q2_budget_to' name='q2_budget_to' />
					<input type='hidden' value='0' id='q3_budget_to' name='q3_budget_to' />
					<input type='hidden' value='0' id='q4_budget_to' name='q4_budget_to' />
					<!--<input type="hidden" class="field" name="budget_to" id="budget_to" spellcheck="false" tabindex="1">-->
				</td>	
			</tr>

			<tr>
				<td class="textLabel">Reallocated Budget:</td>
				<td class="textField" id="td_reallocated_budget_fr">
					<input type="text" class="field" name="reallocated_budget_fr" id="reallocated_budget_fr" spellcheck="false" tabindex="1" readonly>
				</td>	
				<td class="textField">
					<input type="text" class="field" name="reallocated_budget_to" id="reallocated_budget_to" spellcheck="false" tabindex="1" readonly>
				</td>	
			</tr>
			</table>
			<br/>
			<div style="margin: auto; text-align: center; width: 50%;">
				<table class="procurement" style="margin: auto; text-align: center; width: 50%;" border="0" cellspacing="5px">

					<tr id="amount_movement">
						<td style="width:50%;" class="textLabel">Amount: <i class="required">*</i></td>
						<td class="textField"><input type="text" class="field" name="amount" id="amount" spellcheck="false" tabindex="1"  value="0.00" onkeypress="return numbersonly(this, event)" onblur="round(this,2);">
					</tr>
				
					<tr>
						<td class="textLabel">Reason <i class="required">*</i></td>
						<td class="textField" colspan="3"><textarea id="reason" name="reason"></textarea></td>		
					</tr>
					<tr>
						<td class="textLabel" style="width:50%;">Remarks:</td>
						<td class="textField" colspan="3"><textarea id="remarks" name="remarks" disabled></textarea></td>		
					</tr>
					<tr>
						<td class="textLabel">Responsible:</td>
						<td class="textField" colspan="3"><input type="text" class="field" name="responsible" id="responsible" spellcheck="false" tabindex="1" value="" readonly=""></td>
					</tr>
					<tr id="select-status" style="visibility:hidden">
						<td class="textLabel">Change Status:</td>
						<td>
						<select name="status" id="status">
							<option value="">-- Please select --</option>
							<option value="Rejected">Rejected</option>
						</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit" id="submit" value=" Save ">
		<input type="button" value=" Cancel " Onclick="cancel(this.form);">&nbsp;&nbsp;
	</div>
	</div>
</div>

</div>
</form>
</body>
</html>  
