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
$errorFlag = false;
$errorMessage = "";
$msg = @$_GET['msg'];
$res = @$_GET['res'];

$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$id = $_GET['id'];
$year = $_GET['year'];
$orgcode = $_GET['orgcode'];

$orgcolumn = $crudapp->readColumn($conn,"R5ORGANIZATION");
$orgcodeinfocnd = "ORG_CODE LIKE '$orgcode'";
$orginfo = $crudapp->listTable($conn,"R5ORGANIZATION",$orgcolumn,$orgcodeinfocnd);
$filter = array();

//PROJECT BASE
$cnd2 = "year_budget = '$year' AND ORG_CODE = '$orgcode' AND status = 'Endorsed' AND reference_no IN (SELECT DISTINCT(reference_no) FROM dbo.R5_APP_VERSION_PROJECT_LINES WHERE app_id = '$id')";
$column2 = $crudapp->readColumn($conn,"R5_ENDORESED_APP_PROJECTBASE");
$requiredField2 = array('id','reference_no','Organization','Submitted_Amount','status');
$column2 = array_intersect($column2,$requiredField2);
$listView2 = $crudapp->listTable($conn,"R5_ENDORESED_APP_PROJECTBASE",$column2,$cnd2);
$tableView2 = $filterapp->filterViewURL2($conn,$column2,$listView2,$filter,"id");

//ENDORSE or REJECT DEPT APP
if (isset($_POST['submit_project'])){
$reference_no = $_POST['reference_no_project'];
$status = $_POST['status_project'];
$remarks = $_POST['remarks_project'];
$project_version = $_POST['project_version'];

//Validation
if ($status == ""){
$errorMessage .= 'Please select status before submitting the form.\n\n';
$errorFlag = true;
}


	if(!$errorFlag){
		$data = array("status"=>$status,"remarks"=>$remarks);
		$table = "dbo.R5_PROJECT_VERSION";
		//$updateStats = $crudapp->updateRecord($conn,$data,$table,"reference_no",$reference_no);
		$condition = "ORG_CODE = '$orgcode' AND year_budget = '$year' AND reference_no = '$reference_no' AND version = '$project_version'";
		$updateStats = $crudapp->updateRecord2($conn,$data,$table,$condition);
		
		
		//update rejectFlag
		if($status == "Revision Request"){
		$data = array("rejectFlag"=>1);
		$recordStatus = $crudapp->updateRecordStatus2($conn,$data,$reference_no,$project_version);
		}
		
			if( $updateStats == 1) {
				sqlsrv_commit( $conn );
				echo "Transaction committed.<br />";
			} else {
				sqlsrv_rollback( $conn );
				echo "Transaction rolled back.<br />";
			}
			
			header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&id=".$id."&orgcode=".$orgcode."&res=pass&msg=Record has been successfully updated!");
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
//ONCLICK PROJECT BASED
function onclickProjectEvent(id){
$('#submit_project, #status_project, #remarks_project').attr("disabled", false);

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
	 $('.projectMilestone').html(xmlhttp.responseText);
    }
  }
xmlhttp.open("GET","ajax/app-endorsement-project.php?hash="+text+"&id="+id,true);
xmlhttp.send();


//GET REFERENCENO
//HASH - To random string that will reload pages with ajax call
var xmlhttp3=new XMLHttpRequest();
xmlhttp3.onreadystatechange=function()
  {
  if (xmlhttp3.readyState==4 && xmlhttp3.status==200)
    {
	 var json = $.parseJSON(xmlhttp3.responseText);
	  var reference_no = json['reference_no'];
	  var version = json['version'];
	 $('#reference_no_project').val(reference_no);
	  $('#project_version').val(version);
    }
  }
xmlhttp3.open("GET","ajax/get-project-version-info2.php?hash="+text+"&id="+id,true);
xmlhttp3.send();

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
	
	$("#year_budget").val("<?php echo $year;?>");
		
    var counter = 0;
    $(".list th").each(function(){
        var width = $('.list tr:last td:eq(' + counter + ')').width();
        $(".NewHeader tr").append(this);
        this.width = width;
        counter++;
    });
	
    var counter2 = 0;
    $(".list2 th").each(function(){
		width2 = "460";
        $(".NewHeader2 tr").append(this);
        this.width = width2;
        counter2++;
    });
	
	$("#tabURL").click(function() {
		window.location = 'app-for-rejection-list-item.php<?php echo "?login=".$user."&year=".$year."&id=".$id."&orgcode=".$orgcode; ?>';
	});
	
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&id=".$id."&orgcode=".$orgcode; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">APP for Rejection List</div></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organitzation:</td>
			<td class="textField"><input type="text" class="field" name="organization" id="organization" spellcheck="false" tabindex="1" value= "<?php echo $orginfo[0]['ORG_DESC'];?>" readonly><input type="hidden" class="field" name="ORG_CODE" id="ORG_CODE" spellcheck="false" tabindex="1" value= "<?php echo $orginfo[0]['ORG_CODE'];?>"></td>			
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
	</tbody>
</table>
</div>
<div class="headerText">Budget Breakdown</div>

<div class="headerText">
<input type="button" class="tabs" id="tabURL" name="ITEM-BASE" value=" ITEM-BASED / COST-BASED ">
<input type="button" class="tabs selected" name="PROJECT-BASE" value=" PROJECT-BASED ">
</div>

	<?php
	echo $tableView2;
	?>	
	<!--APP Section-->
	<div class="formDiv">
	<div class="headerText">Budget Details</div>
	<table class="procurement" border="0" cellspacing="5px" width="100%">
		<tbody>
			<tr>
				<td class="textLabel">Department:</td>
				<td class="textField">
				<input type="hidden" class="field" name="project_version" id="project_version" spellcheck="false" tabindex="1">
				<input type="hidden" class="field" name="reference_no_project" id="reference_no_project" spellcheck="false" tabindex="1">
				<input type="text" class="field" name="department" id="department" spellcheck="false" tabindex="1" readonly></td>					
				<td class="textLabel">Status:</td>
				<td class="textField">
				<select name="status_project" id="status_project">
					<option value="">-- Please select --</option>
					<option name="role" value="Endorsed">Endorse</option>
					<option name="role" value="Revision Request">Revision Request</option>
				</select>
				</td>
			</tr>
			<tr>
				<td class="textLabel">Remarks:</td>
				<td class="textField" colspan="3"><textarea name="remarks_project"></textarea></td>	
			</tr>
		</tbody>
	</table>
	<!--DATE of NEED-->
	<div class="projectMilestone">
		<table border="1" cellspacing="5px" width="70%" style="margin:0 auto;">
			<tr>
				<th>Milestone</th>
				<th>Cost (PHP)</th>
			</tr>
		</table>
	</div>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit_project" id="submit_project" value=" Submit ">&nbsp;&nbsp;
	</div>
	</div>
</div>
</form>
</body>
</html>  
