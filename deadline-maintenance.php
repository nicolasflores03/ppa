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

//GET USER INFO
$userfilter = "USR_CODE = '$user'";
$usercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
$userinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$usercolumn,$userfilter);
$mrccode = $userinfo[0]['MRC_CODE'];
$orgcode = $userinfo[0]['ORG_CODE'];

$filter = array();
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
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}		
}

$cnd = "";
$column = $crudapp->readColumn($conn,"R5_DEADLINE_MAINTENANCE");
$requiredField = array('id','month','date','year','budget_year', array('isActive', 'IS ACTIVE') , array('Q1', 'Q1 Open'), array('Q2', 'Q2 Open'), array('Q3', 'Q3 Open'), array('Q4', 'Q4 Open'), array('updatedAt', 'Date Created'));
// $column = array_intersect($column,$requiredField);
$listView = $crudapp->listTableDeadline($conn,"R5_DEADLINE_MAINTENANCE",$column,$cnd);


$tableView = $filterapp->filterViewURLXdelete($conn,$requiredField,$listView,$filter,"id");



if (isset($_POST['submit'])){
	//Passing of Data
	$deadline = $_POST['deadline'];
	$budget_year = $_POST['budget_year'];
	$id =  @$_POST['id'];
	$active = @$_POST['active'];
	$Q1 = @$_POST['Q1'];
	$Q2 = @$_POST['Q2'];
	$Q3 = @$_POST['Q3'];
	$Q4 = @$_POST['Q4'];

	// $Q1 = $_POST['Q1'] != "" ? date("Y-m-d",  strtotime($_POST['Q1']))  : null;
	// $Q2 = $_POST['Q2'] != "" ? date("Y-m-d",  strtotime($_POST['Q2']))  : null;
	// $Q3 = $_POST['Q3'] != "" ? date("Y-m-d",  strtotime($_POST['Q3']))  : null;
	// $Q4 = $_POST['Q4'] != "" ? date("Y-m-d",  strtotime($_POST['Q4']))  : null;

//Validation
if ($deadline == ""){
	$errorMessage .= 'Please enter budget deadline.\n\n';
	$errorFlag = true;

	$checkmonth = "";
	$checkdate = "";
	$checkyear = "";
} else {
	//VALIDATE DEADLIN
	$checkdeadline = explode("/",$deadline);
	$checkmonth = $checkdeadline[0];
	$checkdate = $checkdeadline[1];
	$checkyear = $checkdeadline[2];
}
	
	//Validation
if ($budget_year == ""){
$errorMessage .= 'Please select a budget year.\n\n';
$errorFlag = true;
}

$isvalidDate = checkdate($checkmonth, $checkdate, "20".$checkyear);

if (!$isvalidDate){
$errorMessage .= 'Please select a valid deadline.\n\n';
$errorFlag = true;
}

if(!$errorFlag){
	$table = "dbo.R5_DEADLINE_MAINTENANCE";
	if ($active != '1') {
		$active = '0';
	} else {
		//UNCHECK EXISTING DEADLINE
		$dataisActive = array("isActive"=>"0");			
		$cnd = "WHERE isActive = '1' AND budget_year = '$budget_year'";
		$crudapp->updateRecord3($conn,$dataisActive,$table,$cnd);
	}
	
	$today = date("m/d/Y H:i");	
	$deadline = explode("/",$deadline);
	$month = $deadline[0];
	$date = $deadline[1];
	$year = $deadline[2];
	
	$data = array("year"=>$year,"month"=>$month,"date"=>$date,"year"=>$year,"budget_year"=>$budget_year,"isActive"=>$active,"createdAt"=>$today,"createdBy"=>$user,"updatedAt"=>$today,"updatedBy"=>$user, "Q1"=>$Q1, "Q2"=>$Q2, "Q3"=>$Q3, "Q4"=>$Q4 );	
	$dataupdate = array("year"=>$year,"month"=>$month,"date"=>$date,"year"=>$year,"budget_year"=>$budget_year,"isActive"=>$active,"updatedAt"=>$today,"updatedBy"=>$user, "Q1"=>$Q1, "Q2"=>$Q2, "Q3"=>$Q3, "Q4"=>$Q4);	
		
	if($id != ""){
		$result = $crudapp->updateRecord($conn,$dataupdate,$table,"id",$id);
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
	header("Location:".$_SERVER['PHP_SELF']."?login=".$user);
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
 
<!--DATE-->
<script type="text/javascript" src="js/datepickr.js"></script>
<!--DATE-->	

<script src="js/jquery.min.js">
</script>
<script src="js/jquery-ui.js">
</script>
<script src="js/jquery.maskedinput-1.3.1.min_.js">
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
	 insertDeadlineInfo(json);
    }
  }
xmlhttp.open("GET","ajax/app-get-deadline-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}

function insertDeadlineInfo(json){
	var month = json['month'];
	var year = json['year'];
	var date = json['date'];
	var budget_year = json['budget_year'];	 
	var id = json['id'];
	var isActive = json['isActive'];
	var q1_isActive = json['Q1'];
	var q2_isActive = json['Q2'];
	var q3_isActive = json['Q3'];
	var q4_isActive = json['Q4'];

	$('#q1_isActive').prop('checked', false);
	$('#q2_isActive').prop('checked', false);
	$('#q3_isActive').prop('checked', false);
	$('#q4_isActive').prop('checked', false);

	if (q1_isActive > 0){
		$('#q1_isActive').prop('checked', true);
	}
	if (q2_isActive > 0){
		$('#q2_isActive').prop('checked', true);
	}
	if (q3_isActive > 0){
		$('#q3_isActive').prop('checked', true);
	}
	if (q4_isActive > 0){
		$('#q4_isActive').prop('checked', true);
	}

	// var q1_date = "";
	// var q2_date = "";
	// var q3_date = "";
	// var q4_date = "";
	
	// if(json['Q1'] != null) {
	// 	q1_date = json['Q1'].date.replace(" 00:00:00", "").split("-");
	// 	q1_date = q1_date[1] + "/" + q1_date[2] + "/" + q1_date[0].replace("20","");
	// } 
	// if(json['Q2'] != null) {
	// 	q2_date = json['Q2'].date.replace(" 00:00:00", "").split("-");
	// 	q2_date = q2_date[1] + "/" + q2_date[2] + "/" + q2_date[0].replace("20","");
	// } 
	// if(json['Q3'] != null) {
	// 	q3_date = json['Q3'].date.replace(" 00:00:00", "").split("-");
	// 	q3_date = q3_date[1] + "/" + q3_date[2] + "/" + q3_date[0].replace("20","");
	// } 
	// if(json['Q4'] != null) {
	// 	q4_date = json['Q4'].date.replace(" 00:00:00", "").split("-");
	// 	q4_date = q4_date[1] + "/" + q4_date[2] + "/" + q4_date[0].replace("20","");
	// }

	// $('#q1_datepicker').val(q1_date);
	// $('#q2_datepicker').val(q2_date);
	// $('#q3_datepicker').val(q3_date);
	// $('#q4_datepicker').val(q4_date);
	 
	 month = month.replace(/ /g, '');
	 date = date.replace(/ /g, '');
	 year = year.replace(/ /g, '');

	 var deadline = month+"/"+date+"/"+year;
	 $('#datepick2').val(deadline);
	 $('#id').val(id);
	 $('#budget_year').val(budget_year);
	 if (isActive > 0){
		$('#active').prop('checked', true);
	 }else{
		$('#active').prop('checked', false);
	 }
}

function deleteRecord(id){
var obj = 'R5_VIEW_DEADLINE_MAINTENANCE';
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
var user ="<?php echo $user; ?>";
	$("#newRecord").click(function() {
		window.location = "deadline-maintenance.php?login="+user;
	});

	
	new datepickr('datepick2', {
		'dateFormat': 'm/d/y'
	});
	// new datepickr('q1_datepicker', {
	// 	'dateFormat': 'm/d/y'
	// });
	// new datepickr('q2_datepicker', {
	// 	'dateFormat': 'm/d/y'
	// });
	// new datepickr('q3_datepicker', {
	// 	'dateFormat': 'm/d/y'
	// });
	// new datepickr('q4_datepicker', {
	// 	'dateFormat': 'm/d/y'
	// });

	// $("#q1_datepicker").datepicker();

	$("#budget_year").change(function() {
		var budget_year = $(this).val();
		//HASH
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
			var result = $.parseJSON(xmlhttp.responseText);
			$('#q1_isActive').prop('checked', false);
			$('#q2_isActive').prop('checked', false);
			$('#q3_isActive').prop('checked', false);
			$('#q4_isActive').prop('checked', false);	

				if(result != "0"){
					var r=confirm("There is an existing budget deadline for the selected year. Do you want to change?");
					if (r==true){
						insertDeadlineInfo(result);
					}else{
						$('#active').prop('checked', true);
						$('#budget_year').val('');
						$('#datepick2').val('');
		
					}
				} else {
					$('#datepick2').val('');
					// var Q1 = $.datepicker.formatDate('mm/dd/yy', new Date(budget_year, 3, 0));
					// var Q2 = $.datepicker.formatDate('mm/dd/yy', new Date(budget_year, 6, 0));
					// var Q3 = $.datepicker.formatDate('mm/dd/yy', new Date(budget_year, 9, 0));
					// var Q4 = $.datepicker.formatDate('mm/dd/yy', new Date(budget_year, 12, 0));

					// $('#q1_datepicker').val(Q1);
					// $('#q2_datepicker').val(Q2);
					// $('#q3_datepicker').val(Q3);
					// $('#q4_datepicker').val(Q4);
				}
			}
		}
		xmlhttp.open("GET","ajax/app-deadline.php?hash="+text+"&budget_year="+budget_year,true);
		xmlhttp.send();
	});
});


</script>
<style>
			.calendar {
				font-family: 'Trebuchet MS', Tahoma, Verdana, Arial, sans-serif;
				font-size: 0.9em;
				background-color: #EEE;
				color: #333;
				border: 1px solid #DDD;
				-moz-border-radius: 4px;
				-webkit-border-radius: 4px;
				border-radius: 4px;
				padding: 0.2em;
				width: 14em;
			}
			
			.calendar .months {
				background-color: #2b76b8;
				border: 1px solid #ccc;
				-moz-border-radius: 4px;
				-webkit-border-radius: 4px;
				border-radius: 4px;
				color: #FFF;
				padding: 0.2em;
				text-align: center;
			}
			
			.calendar .prev-month,
			.calendar .next-month {
				padding: 0;
			}
			
			.calendar .prev-month {
				float: left;
			}
			
			.calendar .next-month {
				float: right;
			}
			
			.calendar .current-month {
				margin: 0 auto;
			}
			
			.calendar .months .prev-month,
			.calendar .months .next-month {
				color: #FFF;
				text-decoration: none;
				padding: 0 0.4em;
				-moz-border-radius: 4px;
				-webkit-border-radius: 4px;
				border-radius: 4px;
				cursor: pointer;
			}
			
			.calendar .months .prev-month:hover,
			.calendar .months .next-month:hover {
				background-color: #FDF5CE;
				color: #C77405;
			}
			
			.calendar table {
				border-collapse: collapse;
				padding: 0;
				font-size: 0.8em;
				width: 100%;
			}
			
			.calendar th {
				text-align: center;
			}
			
			.calendar td {
				text-align: right;
				padding: 1px;
				width: 14.3%;
			}
			
			.calendar td span {
				display: block;
				color: #1C94C4;
				background-color: #F6F6F6;
				border: 1px solid #CCC;
				text-decoration: none;
				padding: 0.2em;
				cursor: pointer;
			}
			
			.calendar td span:hover {
				color: #C77405;
				background-color: #FDF5CE;
				border: 1px solid #FBCB09;
			}
			
			.calendar td.today span {
				background-color: #FFF0A5;
				border: 1px solid #FED22F;
				color: #363636;
			}
</style>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="mainContainer">
<div class="headerText">Budget Parameter</div>

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
			$requiredFilter = array('budget_year');
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
<div class="headerText">Budget Deadline Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
	<tbody>
	<tr>
			<td class="textLabel">Budget Year:</td>
				<td class="textField">
					<select name="budget_year" id="budget_year">
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
			<td class="textLabel">Deadline:</td>
			<td class="textField">
						<input type="hidden" value="" id="id" name="id">
						<input type="text" autocomplete="off" value="" id="datepick2" name="deadline">
			</td>				
	</tr>
		<tr>
			<td class="textLabel">Is Active: <i class="required">*</i></td>
			<td class="textField">	<input type="checkbox" name="active" id="active" value="1" checked>
		</tr> 
		<tr>
			<td class="textLabel"><strong>Quarter Open (Check to open):</strong></td>
		</tr> 
		<tr>
			<td class="textLabel">Q1:</td>
			<td class="textField">

				<input type="checkbox" name="Q1" id="q1_isActive" value="1" >
			</td>
		</tr>
		<tr>
			<td class="textLabel">Q2:</td>
			<td class="textField">
				<input type="checkbox" name="Q2" id="q2_isActive" value="1" >
			</td>
		</tr>
		<tr>
			<td class="textLabel">Q3:</td>
			<td class="textField">
				<input type="checkbox" name="Q3" id="q3_isActive" value="1" >
			</td>
		</tr>
		<tr>
			<td class="textLabel">Q4:</td>
			<td class="textField">
				<input type="checkbox" name="Q4" id="q4_isActive" value="1" >
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
<script type="text/javascript">
jQuery(function($){
   $("#datepick2",".datepick2").mask("99/99/99");
});
</script>
</body>
</html>  
