<?php
include("include/connect.php");
include("class/crud.php");

//VARIABLES
$errorFlag = false;
$errorMessage = "";

$tableName = $_GET['obj'];

$code = $_GET['code'];
$quantity = $_GET['quantity'];
$item_val = $_GET['item_val'];
$january = $_GET['january'];
$february = $_GET['february'];
$march = $_GET['march'];
$april = $_GET['april'];
$may = $_GET['may'];
$june = $_GET['june'];
$july = $_GET['july'];
$august = $_GET['august'];
$september = $_GET['september'];
$october = $_GET['october'];
$november = $_GET['november'];
$december = $_GET['december'];
$source_tb = $_GET['source_tb'];
$destination_tb = $_GET['destination_tb'];
	
$crudapp = new crudClass();
$data = $crudapp->listRefRecord($conn,$tableName);

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
			if ($type == "eq"){
				if ($views["$fieldname"] == $value){
					$tableView .= "<tr id='tbitem'>";
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
					$tableView .="</tr>";
				}
			}else if ($type == "co"){
				if (strpos($views["$fieldname"],$value) !== false){
					$tableView .= "<tr id='tbitem'>";
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
					$tableView .="</tr>";
				}				
			}else if ($type == "sw"){
				if (0 === strpos($views["$fieldname"], $value)){
					$tableView .= "<tr id='tbitem'>";
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
					$tableView .="</tr>";
				}				
			}else if ($type == "ew"){
				if (stripos(strrev($views["$fieldname"]), strrev($value)) === 0){
					$tableView .= "<tr id='tbitem'>";
					$tableView .= "<td>".$code."</td>";
					$tableView .= "<td>".$desc."</td>";
					$tableView .="</tr>";
				}				
			}
			
		}
		$tableView .="</table>";
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
		var code = "<?php echo $code; ?>";
		var quantity = "<?php echo $quantity; ?>";
		var item_val = "<?php echo $item_val; ?>";
		var january = "<?php echo $january; ?>";
		var february = "<?php echo $february; ?>";
		var march = "<?php echo $march; ?>";
		var april = "<?php echo $april; ?>";
		var may = "<?php echo $may; ?>";
		var june = "<?php echo $june; ?>";
		var july = "<?php echo $july; ?>";
		var august = "<?php echo $august; ?>";
		var september = "<?php echo $september; ?>";
		var october = "<?php echo $october; ?>";
		var november = "<?php echo $november; ?>";
		var december = "<?php echo $december; ?>";
		
		
		//Default value for ref tables 
		window.opener.document.theForm.code.value=code; 
		window.opener.document.theForm.quantity.value=quantity; 
		window.opener.document.theForm.item_val.value=item_val;
		
		
		if( objName == "R5PARTS" || objName == "R5_VIEW_PARTS_UOM_INFO"){
		window.opener.document.theForm.code.value=id;
		window.opener.document.theForm.item_val.value=name;
		}
		
		window.opener.document.theForm.january.value=january;
		window.opener.document.theForm.february.value=february;
		window.opener.document.theForm.march.value=march;
		window.opener.document.theForm.april.value=april;
		window.opener.document.theForm.may.value=may;
		window.opener.document.theForm.june.value=june;
		window.opener.document.theForm.july.value=july;
		window.opener.document.theForm.august.value=august;
		window.opener.document.theForm.september.value=september;
		window.opener.document.theForm.october.value=october;
		window.opener.document.theForm.november.value=november;
		window.opener.document.theForm.december.value=december;
		window.opener.getItemInfo();
		self.close(); 

	});
});
</script> 
</head> 
<body> 
<div class="filters">
<form action="<?php echo $_SERVER['PHP_SELF'].'?obj='.$tableName.'&code='.$code.'&item_val='.$item_val.'&quantity='.$quantity
.'&january='.$january.'&february='.$february.'&march='.$march.'&april='.$april.'&may='.$may.'&june='.$june.'&july='.$july
.'&august='.$august.'&september='.$september.'&october='.$october.'&november='.$november.'&december='.$december.'&source_tb='.$source_tb.'&destination_tb='.$destination_tb; ?>" method="post" name="theForm" enctype="multipart/form-data">
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
<div class="headerText">Annual Procurement Plan</div>
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