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
$ORG_CODE = @$_GET['ORG_CODE'];

$cnd = "ORG_CODE LIKE '$ORG_CODE' ORDER BY convert(int, reference_no),version ASC";



$column = $crudapp->readColumn($conn,"R5_PROJECT_VERSION");
$requiredField = array('reference_no','year_budget','version','status','ORG_CODE','id');
$column = array_intersect($column,$requiredField);

$listView = $crudapp->listTable($conn,"R5_PROJECT_VERSION",$column,$cnd);

$tableView = $filterapp->filterViewURL($conn,$column,$listView,$filter,"id");

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
		$tableView = $filterapp->filterViewURL($conn,$column,$listView,$filter,"id");
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
function onclickEvent(id){
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
	 var year = json['year_budget'];
	  var version = json['version'];
	  var reference_no = json['reference_no'];
	 var user = "<?php echo $user;?>";
	 window.location = "app-project-lines.php?login="+user+"&year="+year+"&version="+version+"&reference_no="+reference_no;
    }
  }
xmlhttp.open("GET","ajax/get-project-version-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

function deleteRecord(id){
var updatedBy = "<?php echo $user; ?>";
var obj = 'R5_PROJECT_VERSION';
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
		xmlhttp2.open("GET","ajax/app-delete-record.php?hash="+text+"&id="+id+"&obj="+obj+"&updatedBy="+updatedBy,true);
		xmlhttp2.send();
	}
}

$(document).ready(function(){
	$("#ORG_CODE").val("<?php echo $ORG_CODE;?>");
	var orgCount = $('#ORG_CODE option').size();
	

var org = $("#ORG_CODE").val();
if (org == ""){	
	if (orgCount == 2){
		$("#ORG_CODE")[0].selectedIndex=1;
		var org = $("#ORG_CODE").val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&ORG_CODE="; ?>'+org; 
	}
}

	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&ORG_CODE="; ?>'+ORG_CODE; 
	});
	

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
});
</script>
<style>
.listContent{
background:#fff;
}
</style>
</head>
<body>
<div class="headerText">APP Project List</div>
<div class="filterContainer">
	<div class="deptFilter">
		<table width="33%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
			<tr>
				<td>Organization: </td>
				<td>
					<?php 
									$tbname = "R5_VIEW_USERINFO";
									$tbfield = "DISTINCT(ORG_CODE)";
									$tbfield2 = "ORG_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user'");
					?>
				</td>
			</tr>
		</table>
	</div>
	<div class="filters">
	<form action="<?php echo $_SERVER['PHP_SELF']."?login=$user&ORG_CODE=$ORG_CODE"; ?>" method="post" name="theForm" enctype="multipart/form-data">
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
