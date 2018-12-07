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
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$msg = @$_GET['msg'];
$res = @$_GET['res'];

//GET USER INFO
$userfilter = "USR_CODE = '$user'";
$usercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
$userinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$usercolumn,$userfilter);
$mrccode = $userinfo[0]['MRC_CODE'];
$orgcode = $userinfo[0]['ORG_CODE'];

$filter = array();
$cnd = "";
$column = $crudapp->readColumn($conn,"R5_COSTCENTER_ACCOUNTS");
$requiredField = array('id','MRC_CODE','cost_center');
$column = array_intersect($column,$requiredField);
$listView = $crudapp->listTable($conn,"R5_COSTCENTER_ACCOUNTS",$column,$cnd);
$tableView = $filterapp->filterViewURLXdelete($conn,$column,$listView,$filter,"id");


if (isset($_POST['submit'])){
	//Passing of Data
	$cost_center = $_POST['cost_center'];
	$MRC_CODE =  $_POST['MRC_CODE'];
	$id =  @$_POST['id'];
	$active = @$_POST['active'];
	
	if ($active != '1') {
		$active = '0';
	}

	$table = "dbo.R5_COSTCENTER_ACCOUNTS";


	$data = array("MRC_CODE"=>$MRC_CODE,"cost_center"=>$cost_center,"isActive"=>$active);	
	
	if($id != ""){
	$result = $crudapp->updateRecord($conn,$data,$table,"id",$id);
	}else{
	$result = $crudapp->insertRecord($conn,$data,$table);
	}
	
	if($result) {
		sqlsrv_commit( $conn );
		echo "Transaction committed.<br />";
	} else {
		sqlsrv_rollback( $conn );
		echo "Transaction rolled back.<br />";
	}
	header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&res=pass&msg=You have successfully saved the records!");
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

//R5COMMODITIES TABLE
function valideopenerform(obj){

var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		
	
	var cost_center = $('#cost_center').val();
	
var popup= window.open('popupMRC.php?hash='+text+'&obj='+obj+'&cost_center='+cost_center+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 
popup.focus(); 
}

function onclickEvent(id){
//HASH
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
	 var cost_center = json['cost_center'];
	 var MRC_CODE = json['MRC_CODE'];
	 var MRC_DESC = json['MRC_DESC'];
	 var id = json['id'];
	 $('#cost_center').val(cost_center);
	 $('#MRC_CODE').val(MRC_CODE);
	 $('#MRC_DESC').val(MRC_DESC);
	 $('#id').val(id);
    }
  }
xmlhttp.open("GET","ajax/app-get-mrc-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}


function deleteRecord(id){
var obj = 'R5_COSTCENTER_ACCOUNTS';
var r=confirm("Are you sure you want to delete this record?");
var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	if (r==true){
		for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
		var xmlhttp2=new XMLHttpRequest();
		xmlhttp2.onreadystatechange=function()
		{
		if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
			{
				alert('Record has been deleted successfully!');
				location.reload();
			}
		}
		xmlhttp2.open("GET","ajax/app-delete-record.php?hash="+text+"&id="+id+"&obj="+obj,true);
		xmlhttp2.send();
	}
}


$(document).ready(function(){
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
var user ="<?php echo $user; ?>";
	$("#newRecord").click(function() {
		window.location = "cost-center-maintenance.php?login="+user;
	});
});


</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="mainContainer">
<div class="headerText">Cost Center Maintenance</div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>

<div class="headerText">
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
			$requiredFilter = array('project_code','ORG_CODE','year_budget');
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
			<td class="textLabel">Department: <i class="required">*</i></td>
			<td class="textField"><input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1"><input type="text" class="field" name="MRC_DESC" id="MRC_DESC" spellcheck="false" tabindex="1" readonly><input type="hidden" class="field" name="MRC_CODE" id="MRC_CODE" spellcheck="false" tabindex="1" readonly><button name="cmd" onclick="valideopenerform('R5MRCS')">...</button></td>	
		</tr>
		<tr>
			<td class="textLabel">Cost Center: <i class="required">*</i></td>
			<td class="textField"><input type="text" class="field" name="cost_center" id="cost_center" spellcheck="false" tabindex="1">
		</tr>
		<tr>
			<td class="textLabel">Is Active: <i class="required">*</i></td>
			<td class="textField">	<input type="checkbox" name="active" value="1" checked>
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
