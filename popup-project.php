<?php
include("include/connect.php");
include("class/crud.php");

//VARIABLES
$errorFlag = false;
$errorMessage = "";

$tableName = $_GET['obj'];
$org_code = $_GET['org_code'];
$project_code = $_GET['project_code'];
$description = $_GET['description'];
$id = $_GET['id'];
	
$crudapp = new crudClass();
$cnd = "WHERE PRJ_ORG = '$org_code'";
$data = $crudapp->listRefRecordProject2($conn,$tableName,$cnd);

$tableView = "";
foreach($data as $value){
$code = $value['code'];
$desc = $value['desc'];
//$codes = str_replace(" ","@",$code);
//$descs = str_replace(" ","-",$desc);
$tableView .= "<tr id='tbitem'>";
$tableView .= "<td>".$code."</td>";
$tableView .= "<td>".$desc."</td>";
$tableView .= "</tr>";
}
$tableView .= '</table>';

//Item
if (isset($_POST['search'])){
$tableView = "";
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
		foreach($data as $views){
		$code = $views['code'];
		$desc = $views['desc'];
		$tableView .= "<tr id='tbitem'>";
			if ($type == "eq"){
				if ($views["$fieldname"] == $value){
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
				}
			}else if ($type == "co"){
				if (strpos($views["$fieldname"],$value) !== false){
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
				}				
			}else if ($type == "sw"){
				if (0 === strpos($views["$fieldname"], $value)){
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
				}				
			}else if ($type == "ew"){
				if (stripos(strrev($views["$fieldname"]), strrev($value)) === 0){
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
				}				
			}
			
		}
		$tableView .="</tr>";
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}	
}
?>


<html> 
<head> 
<link rel="stylesheet" href="css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
<title>Opener</title> 
<script src="js/jquery.min.js"></script>
<script type='text/javascript'> 
$(document).ready(function(){
	$("tr#tbitem").click(function() {        // function_tr
		var id = $(this).find("td").eq(0).text();
		var name = $(this).find("td").eq(1).text();
		var objName = "<?php echo $tableName; ?>";
		var project_code = "<?php echo $project_code; ?>";
		var projectID = "<?php echo $id; ?>";
		var description = "<?php echo $description; ?>";
		
		//Default value for ref tables 
		window.opener.document.theForm.project_code.value=project_code;
		window.opener.document.theForm.id.value=projectID; 
		window.opener.document.theForm.description.value=description; 
		
		if( objName == "R5PROJECTS"){
		window.opener.document.theForm.project_code.value=id;
		window.opener.document.theForm.description.value=name;
		}
		window.opener.getMilestoneInfo(projectID);
		window.opener.document.theForm.project_code.focus();
		self.close(); 	
	});
});
</script> 
</head> 
<body> 
<div class="filters">
<form action="<?php echo $_SERVER['PHP_SELF'].'?obj='.$tableName.'&code='.$code.'&item_val='.$item_val.'&quantity='.$quantity.'&cost='.$cost
.'&january='.$january.'&february='.$february.'&march='.$march.'&april='.$april.'&may='.$may.'&june='.$june.'&july='.$july
.'&august='.$august.'&september='.$september.'&october='.$october.'&november='.$november.'&december='.$december; ?>" method="post" name="theForm" enctype="multipart/form-data">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tablefilter">
	<tr>
		<td>
			<label>Filter:</label>
			<select name="fieldname" id="fieldname">
				<option value=''>-- Please select --</option>
				<option name="code" value="code">Code</option>
				<option name="desc" value="desc">Description</option>
			</select>
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
</form>
</div>
<!--Start of FORM-->
<div class="headerText">Project List</div>
  <table width="100%" cellspacing="0" cellpadding="0" border="1" class="listpop">
	<tr>
		<th>CODE</th>
		<th>DESCRIPTION</th> 
	</tr>
	<?php
		echo $tableView;
	?>
  
</body> 
</html>