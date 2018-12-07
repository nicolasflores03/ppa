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
$user = @$_GET['user'];
$ORG_CODE = @$_GET['ORG_CODE'];
$MRC_CODE = @$_GET['MRC_CODE'];
$msg = @$_GET['msg'];
$res = @$_GET['res'];
$id = @$_GET['id'];
$bo = @$_GET['BO'];
$dh = @$_GET['DH'];
$fi = @$_GET['FI'];
$cfo = @$_GET['CFO'];


$filter = array();
$cnd = "";
$sort = "ORDER BY USR_CODE,ORG_CODE ASC";
$column = $crudapp->readColumn($conn,"R5_CUSTOM_SAM");
$requiredField = array('id','USR_CODE','ORG_CODE','MRC_CODE','BO','FI','DH','CFO');
$column = array_intersect($column,$requiredField);
$listView = $crudapp->listTable3($conn,"R5_CUSTOM_SAM",$column,$cnd,$sort);
$tableView = $filterapp->filterViewURL($conn,$column,$listView,$filter,"id");

if (isset($_POST['submit'])){
	//Passing of Data
	$USR_CODE = $_POST['USR_CODE'];
	$ORG_CODE =  $_POST['ORG_CODE'];
	$MRC_CODE =  $_POST['MRC_CODE'];
	$BO =  @$_POST['BO'];
	$DH =  @$_POST['DH'];
	$FI =  @$_POST['FI'];
	$CFO =  @$_POST['CFO'];	
	$id =  @$_POST['id'];
	
//Validation
if ($USR_CODE == ""){
$errorMessage .= 'Please select a User.\n\n';
$errorFlag = true;
}
	
	//Validation
if ($ORG_CODE == ""){
$errorMessage .= 'Please select Organization.\n\n';
$errorFlag = true;
}

//Validation
if ($MRC_CODE == ""){
$errorMessage .= 'Please select Department.\n\n';
$errorFlag = true;
}

//Check if same dept access > 1
$cnd1 = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE' AND DH = '1'";
$existCtr = $crudapp->matchRecord2($conn,"R5_CUSTOM_SAM",'id',$cnd1);
if ($existCtr > 0 && $DH == '1'){
$errorMessage .= 'Head of department has already been defined for this department.\n\n';
$errorFlag = true;
}


//Check if same CFO > 1
$cnd2 = "WHERE ORG_CODE = '$ORG_CODE' AND CFO = '1'";
$cfoCtr = $crudapp->matchRecord2($conn,"R5_CUSTOM_SAM",'id',$cnd2);
if ($cfoCtr > 0 && $CFO == '1'){
$errorMessage .= 'CFO has already been defined.\n\n';
$errorFlag = true;
}

//Check if same FI > 1
$cnd3 = "WHERE ORG_CODE = '$ORG_CODE' AND FI = '1'";
$fiCtr = $crudapp->matchRecord2($conn,"R5_CUSTOM_SAM",'id',$cnd3);
if ($fiCtr > 0 && $FI == '1'){
$errorMessage .= 'Finance has already been defined.\n\n';
$errorFlag = true;
}

if(!$errorFlag){
	if ($BO != '1') {
		$BO = '0';
	}
	if ($DH != '1') {
		$DH = '0';
	}
	if ($FI != '1') {
		$FI = '0';
	}
	if ($CFO != '1') {
		$CFO = '0';
	}

$condition = "WHERE ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$MRC_CODE' AND USR_CODE = '$USR_CODE'";
$result = false;
//check if other endorsement exist
		$table = "dbo.R5_CUSTOM_SAM";
		$today = date("m/d/Y H:i");	
		$data = array("USR_CODE"=>$USR_CODE,"ORG_CODE"=>$ORG_CODE,"MRC_CODE"=>$MRC_CODE,"BO"=>$BO,"DH"=>$DH,"FI"=>$FI,"CFO"=>$CFO,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user);	
		$dataupdate = array("USR_CODE"=>$USR_CODE,"ORG_CODE"=>$ORG_CODE,"MRC_CODE"=>$MRC_CODE,"BO"=>$BO,"DH"=>$DH,"FI"=>$FI,"CFO"=>$CFO,"updatedAt"=>$today,"updatedBy"=>$user);	
		echo $id;
		if($id != ""){
			$result = $crudapp->updateRecord($conn,$dataupdate,$table,"id",$id);
		}else{
			$userCtr = $crudapp->matchRecord2($conn,"R5_CUSTOM_SAM",'id',$condition);
			if ($userCtr < 1){
			$result = $crudapp->insertRecord($conn,$data,$table);
			}else{
			$errorMessage ="You have an existing security access for this user!";
			echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
			}	
		}
		
		if($result) {
			sqlsrv_commit( $conn );
			echo "Transaction committed.<br />";
			header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&res=pass&msg=You have successfully saved the records!");
		} else {
			sqlsrv_rollback( $conn );
			//echo "Transaction rolled back.<br />";
		}
}else{
	echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
}
}

//Item
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
		$tableView = $filterapp->filterViewURLXdelete2($conn,$column,$listView,$filter,"id");
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
	var gl = $('#gl').val();
		
		var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	
	for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
			
var popup= window.open('popupGL.php?hash='+text+'&obj='+obj+'&gl='+gl+'','popup_form','location=no,menubar=no,status=no,scrollbars=yes,top=50%,left=50%,height=550,width=750'); 

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
	 var USR_CODE = json['USR_CODE'];
	 $('#USR_CODE').val(USR_CODE);
	 USR_CODE = USR_CODE.replace(/ /g,'');
	 var MRC_CODE = json['MRC_CODE'];
	 MRC_CODE = MRC_CODE.replace(/ /g,'');
	 var ORG_CODE = json['ORG_CODE'];
	 ORG_CODE = ORG_CODE.replace(/ /g,'');
	 var BO = json['BO'];
	 var DH = json['DH'];
	 var FI = json['FI'];
	 var CFO = json['CFO'];
	 var id = json['id'];
	 
	 window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?"; ?>'+'user='+USR_CODE+'&ORG_CODE='+ORG_CODE+'&MRC_CODE='+MRC_CODE+'&id='+id+'&BO='+BO+'&DH='+DH+'&FI='+FI+'&CFO='+CFO;
    }
  }
xmlhttp.open("GET","ajax/app-get-sam-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}


function deleteRecord(id){
var obj = 'R5_CUSTOM_SAM';
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
var user ="<?php echo $user; ?>";
var ORG_CODE ="<?php echo $ORG_CODE; ?>";
var MRC_CODE ="<?php echo $MRC_CODE; ?>";
var id ="<?php echo $id; ?>";
var bo ="<?php echo $bo; ?>";
var dh ="<?php echo $dh; ?>";
var fi ="<?php echo $fi; ?>";
var cfo ="<?php echo $cfo; ?>";


$('#USR_CODE').val(user);
$('#ORG_CODE').val(ORG_CODE);
$('#MRC_CODE').val(MRC_CODE);
$('#id').val(id);
if (bo > 0){
$('#BO').prop('checked', true);
}

if (dh > 0){
$('#DH').prop('checked', true);
}

if (fi > 0){
$('#FI').prop('checked', true);
}

if (cfo > 0){
$('#CFO').prop('checked', true);
}
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

	$("#newRecord").click(function() {
		window.location = "sam-maintenance.php";
	});


	$("#USR_CODE").change(function() {
		var USR_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?user="; ?>'+USR_CODE+"#FormAnchor"; 
	});
	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?user=$user&ORG_CODE="; ?>'+ORG_CODE+"#FormAnchor"; 
	});
	
	$("#MRC_CODE").change(function() {
		var MRC_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?user=$user&ORG_CODE=$ORG_CODE&MRC_CODE="; ?>'+MRC_CODE+"#FormAnchor"; 
	});
	
	
});


</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?user=".$user."&ORG_CODE=".$ORG_CODE."&MRC_CODE=".$MRC_CODE; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="mainContainer">
<div class="headerText">Security Access Maintenance</div>
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
			$requiredFilter = array('USR_CODE','ORG_CODE','MRC_CODE','BO','FI','DH','CFO');
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
<a name="FormAnchor"></a>
<div class="formDiv">
<div class="headerText">Security Access Maintenance Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">User: <i class="required">*</i></td>
			<td class="textField">
			
						<?php 
						$tbname = "R5USERS";
						$tbfield = "USR_CODE";
						$tbfield2 = "USR_DESC";
						$crudapp->optionValue42($conn,$tbname,$tbfield,$tbfield2," WHERE USR_CODE != '*'");
						?>	
			<input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1">
			</td>	
		</tr>
		<tr>
			<td class="textLabel">Organization: <i class="required">*</i></td>
			<td class="textField">
			
						<?php 
						$tbname = "R5_VIEW_USERINFO";
						$tbfield = "DISTINCT(ORG_CODE)";
						$tbfield2 = "ORG_DESC";
						$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2," WHERE USR_CODE = '$user'");
						?>	
			
			</td>	
		</tr>	
		<tr>
			<!--<td class="textLabel">Department:</td>
			<td class="textField"><input type="text" class="field" name="department" id="department" spellcheck="false" tabindex="1" value= "<?php echo $userinfo[0]['MRC_DESC'];?>" disabled><input type="hidden" class="field" name="MRC_CODE" id="MRC_CODE" spellcheck="false" tabindex="1" value= "<?php echo $userinfo[0]['MRC_CODE'];?>"></td>-->

			<td class="textLabel">Department: <i class="required">*</i></td>
			<td>
			<?php 
							$tbname = "R5_VIEW_USERINFO";
							$tbfield = "MRC_CODE";
							$tbfield2 = "MRC_DESC";
							$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user' AND ORG_CODE = '$ORG_CODE'");
			?>
			</td>			
		</tr>
		<tr>
			<td class="textLabel">Access: <i class="required">*</i></td>
			<td class="textField">	
			<input type="checkbox" name="BO" id="BO" value="1">Budget Officer<br />
			<input type="checkbox" name="DH" id="DH" value="1">Head of Department<br />
			<input type="checkbox" name="FI" id="FI" value="1">Finance<br />
			<input type="checkbox" name="CFO" id="CFO" value="1">CFO<br />
			</td>	
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
