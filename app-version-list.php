<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");
include("class/object.php");

//Generate Object
$crudapp = new crudClass();
$filterapp = new filterClass();
$errorFlag = false;
$errorMessage = "";
$filter = array();
$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$password = $_GET['password'];
$column = $crudapp->readColumn($conn,"R5_APP_VERSION_VIEW");
$requiredField = array('APP_ID','Organization','Year','Version','Status');
$column = array_intersect($column,$requiredField);
$msg = @$_GET['msg'];
$res = @$_GET['res'];
$ORG_CODE = @$_GET['ORG_CODE'];

$listView = $crudapp->listTable($conn,"R5_APP_VERSION_VIEW",$column,"ORG_CODE = '$ORG_CODE'");

$tableView = $filterapp->filterViewURLAPP($conn,$column,$listView,$filter,"APP_ID");
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
		$tableView = $filterapp->filterView($conn,$column,$listView,$filter);
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
<script>
function reject(id){
var user = "<?php echo $user;?>";
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
	//alert(xmlhttp.responseText);
	 var result = xmlhttp.responseText;
	 if (result){
		//Get App Version Info for URL
		var xmlhttp2=new XMLHttpRequest();
		xmlhttp2.onreadystatechange=function()
		{
		if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
			{
			var json = $.parseJSON(xmlhttp2.responseText);
			var org_code = json['ORG_CODE'];
			var year_budget = json['year_budget'];
			window.location ="app-for-rejection-list-item.php?hash="+text+"&login="+user+"&year="+year_budget+"&id="+id+"&orgcode="+org_code;
			}
		}
		xmlhttp2.open("GET","ajax/get-app-version-info.php?hash="+text+"&id="+id,true);
		xmlhttp2.send();
		
	 }
	}
  }
xmlhttp.open("GET","ajax/app-update-status.php?hash="+text+"&id="+id+"&user="+user+"&action=Revision Request",true);
xmlhttp.send();
}

function approve(id){
var user = "<?php echo $user;?>";
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
	//alert(xmlhttp.responseText);
	 var result = xmlhttp.responseText;
	 window.location ="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&password=".$password."&ORG_CODE=".$ORG_CODE."&res=pass&msg=You have successfully saved the records!"; ?>";
    }
  }
xmlhttp.open("GET","ajax/app-update-status.php?hash="+text+"&id="+id+"&user="+user+"&action=Approved",true);
xmlhttp.send();
}

function onclickAPPEvent(id){
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
		var year = json['year_budget'];
		var version = json['version'];
		var ORG_CODE = json['ORG_CODE'];
		var status = json['status'];
		var user = "<?php echo $user;?>";
		var password = "<?php echo $password;?>";
		window.location = "http://eamqas.fdcutilities.local:8080/crn/cgi-bin/mod_cognos.dll?b_action=cognosViewer&ui.action=run&ui.object=%2fcontent%2ffolder%5b%40name%3d%27DS_MP_1%27%5d%2freport%5b%40name%3d%27APP_CONSOLIDATED.xml%27%5d&ui.name=APP_CONSOLIDATED.xml&run.outputFormat=PDF&run.prompt=false&CAMUsername=R5&CAMPassword=R5&p_Version="+version+"&p_Org="+ORG_CODE+"&p_Year="+year+"&p_Status="+status+"";
		}
	}
	xmlhttp.open("GET","ajax/get-app-version-info.php?hash="+text+"&id="+id,true);
	xmlhttp.send();
}
$(document).ready(function(){
//Error Message
var res = "<?php echo @$res;?>";
$("#ORG_CODE").val("<?php echo $ORG_CODE;?>");

if(res !=""){
	if (res == "pass"){
		$('.isa_success').show();
		$('.isa_error').hide();
	}else {
		$('.isa_error').show();
		$('.isa_success').hide();
	}
}

    var counter = 0;
    $(".list th").each(function(){
        var width = $('.list tr:last td:eq(' + counter + ')').width();
        $(".NewHeader tr").append(this);
        this.width = width;
        counter++;
    });
	//Save Button
	$("#submit_tmp").click(function() {
		var r=confirm("Are you sure you want to Save this record?");
		if (r==true){
				$("#submit").click();
		}
	});
	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&password=$password&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
	
});
</script>
<style>
.listContent{
background:#fff;
}
</style>
</head>
<body>
<div class="headerText">Consolidated APP</div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="filterContainer">
	<div class="deptFilter">
		<table width="33%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
			<tr>
				<td>Organization: </td>
				<td>
					<?php 
									$tbname = "R5ORGANIZATION";
									$tbfield = "ORG_CODE";
									$tbfield2 = "ORG_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE ORG_CODE != '*'");
					?>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="filters">
	<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&password=".$password."&ORG_CODE=".$ORG_CODE; ?>" method="post" name="theForm" enctype="multipart/form-data">
	<table width="33%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
		<tr>
			<td>
				<?php 
				//Render them in drop down box	
				$selection = "";
				$selection .= "<select name='fieldname' id='fieldname'>";
				$selection .= "<option value=''>-- Please select --</option>";
				
				foreach($column as $fieldName){
					$fieldNameVal = str_replace('_', ' ', $fieldName);
					$fieldNameVal = strtoupper($fieldNameVal);
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
		<div class="hidden">
			<input type="submit" class="bold" name="approve" id="approve" value=" approve ">
			<input type="submit" class="bold" name="reject" id="reject" value=" reject ">
		</div>
		</form>
	</table>
	</div>
</div>
<div class="listContent">
	<?php
		echo $tableView;
	?>
</div>	
</body>
</html>  
