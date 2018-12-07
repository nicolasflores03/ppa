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
$ORG_CODE = @$_GET['ORG_CODE'];
$MRC_CODE = @$_GET['MRC_CODE'];

/*$usercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
$userinfocnd = "USR_CODE LIKE '$user'";
$userinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$usercolumn,$userinfocnd);
$mrccode = $userinfo[0]['MRC_CODE'];
$orgcode = $userinfo[0]['ORG_CODE'];*/

//echo $mrccode."--".$orgcode;

$cnd = "MRC_CODE LIKE '$MRC_CODE' AND ORG_CODE LIKE '$ORG_CODE' ORDER BY convert(int, reference_no),version ASC";

$column = $crudapp->readColumn($conn,"R5_DPP_VERSION");
$requiredField = array('reference_no','MRC_CODE','year_budget','cost_center','version','status','id');
$column = array_intersect($column,$requiredField);

$listView = $crudapp->listTable($conn,"R5_DPP_VERSION",$column,$cnd);

$tableView = $filterapp->filterViewURLReport($conn,$column,$listView,$filter,"id");

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
	 window.location = "dpp-record-lines-item.php?login="+user+"&year="+year+"&version="+version+"&reference_no="+reference_no;
    }
  }
xmlhttp.open("GET","ajax/get-dpp-version-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();

}

function deleteRecord(id){
var obj = 'R5_DPP_VERSION';
var updatedBy = "<?php echo $user; ?>";
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
		xmlhttp2.open("GET","ajax/app-delete-record.php?hash="+text+"&id="+id+"&obj="+objid+"&updatedBy="+updatedBy,true);
		xmlhttp2.send();
	}
}


function runReport(id){
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
		var MRC_CODE = json['MRC_CODE'];
		var ORG_CODE = json['ORG_CODE'];
		var user = "<?php echo $user;?>";
		var password = "<?php echo $password;?>";
		window.location = "http://eamqas.fdcutilities.local:8080/crn/cgi-bin/mod_cognos.dll?b_action=cognosViewer&ui.action=run&ui.object=%2fcontent%2ffolder%5b%40name%3d%27DS_MP_1%27%5d%2freport%5b%40name%3d%27APP_DEPT.xml%27%5d&ui.name=APP_DEPT.xml&run.outputFormat=PDF&run.prompt=false&CAMUsername=R5&CAMPassword=R5&p_Refno="+reference_no+"&p_Department="+MRC_CODE+"&p_Year="+year+"&p_Version="+version+"&p_Status=&p_GL=&p_Organization="+ORG_CODE+"";
		}
	}
	xmlhttp.open("GET","ajax/get-dpp-version-info.php?hash="+text+"&id="+id,true);
	xmlhttp.send();
}

$(document).ready(function(){
	$("#ORG_CODE").val("<?php echo $ORG_CODE;?>");
	$("#MRC_CODE").val("<?php echo $MRC_CODE;?>");
	
	
	
	var orgCount = $('#ORG_CODE option').size();
	var mrcCount = $('#MRC_CODE option').size();
	

var org = $("#ORG_CODE").val();
if (org == ""){	
	if (orgCount == 2){
		$("#ORG_CODE")[0].selectedIndex=1;
		var org = $("#ORG_CODE").val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&password=$password&MRC_CODE=$MRC_CODE&ORG_CODE="; ?>'+org; 
	}
}

var mrc = $("#MRC_CODE").val();
if (mrc == ""){	
	if (mrcCount == 2){
		$("#MRC_CODE")[0].selectedIndex=1;
		var mrc = $("#MRC_CODE").val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&password=$password&ORG_CODE=$ORG_CODE&MRC_CODE="; ?>'+mrc;  
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
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&password=$password&MRC_CODE=$MRC_CODE&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
	$("#MRC_CODE").change(function() {
		var MRC_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&password=$password&ORG_CODE=$ORG_CODE&MRC_CODE="; ?>'+MRC_CODE; 
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
<div class="headerText">Department APP List</div>
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
				<td>Department: </td>
				<td>
					<?php 
									$tbname = "R5_VIEW_USERINFO";
									$tbfield = "MRC_CODE";
									$tbfield2 = "MRC_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user' AND ORG_CODE = '$ORG_CODE'");
					?>
				</td>
			</tr>
		</table>
	</div>
	<div class="filters">
	<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&password=".$password."&MRC_CODE=".$MRC_CODE."&ORG_CODE=".$ORG_CODE; ?>" method="post" name="theForm" enctype="multipart/form-data">
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
