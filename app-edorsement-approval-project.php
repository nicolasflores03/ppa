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
$ORG_CODE = @$_GET['ORG_CODE'];

$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$password = $_GET['password'];
$year = $_GET['year'];
$usercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
$userinfocnd = "USR_CODE LIKE '$user'";
$userinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$usercolumn,$userinfocnd);
$filter = array();
$cnd = "year_budget = $year AND ORG_CODE = '$ORG_CODE' AND (status = 'For Endorsement' OR status = 'Endorsed')";

//PROJECT BASE
$column2 = $crudapp->readColumn($conn,"R5_ENDORESED_APP_PROJECTBASE");
$requiredField2 = array('reference_no','Organization','Submitted_Amount','status','id');
$column2 = array_intersect($column2,$requiredField2);
$listView2 = $crudapp->listTable($conn,"R5_ENDORESED_APP_PROJECTBASE",$column2,$cnd);
$tableView2 = $filterapp->filterViewURL2($conn,$column2,$listView2,$filter,"id");

//ENDORSE or REJECT DEPT APP
if (isset($_POST['submit_project'])){
$reference_no = $_POST['reference_no_project'];
$status = $_POST['status_project'];
$remarks = $_POST['remarks_project'];
$project_version = $_POST['project_version'];
$ORG_CODE = $_POST['ORG_CODE'];

//Validation
if ($status == ""){
$errorMessage .= 'Please select status before submitting the form.\n\n';
$errorFlag = true;
}

$today = date("m/d/Y H:i");
	if(!$errorFlag){
		$data = array("status"=>$status,"remarks"=>$remarks,"updatedBy"=>$user,"updatedAt"=>$today);
		$table = "dbo.R5_PROJECT_VERSION";
		//$updateStats = $crudapp->updateRecord($conn,$data,$table,"reference_no",$reference_no);
		$condition = "ORG_CODE = '$ORG_CODE' AND year_budget = '$year' AND reference_no = '$reference_no' AND version = '$project_version'";
		$updateStats = $crudapp->updateRecord2($conn,$data,$table,$condition);
		
		
		//update rejectFlag
		if($status == "Revision Request"){
		
		//SEND EMAIL
				$emailfilter = "id = 1";
				$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
				$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
				$subject = @$emailinfo[0]['subject'];
				$body = @$emailinfo[0]['body'];
				
				$content = "This is to inform you that your Project Budget has been revised as of $today<br>";
				$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>Reference #: $reference_no<br>Version: $project_version<br>";
				$body = str_replace("\$content",$content,$body);

				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT updatedBy FROM R5_PROJECT_VERSION WHERE reference_no = '$reference_no' AND version = '$project_version') AND ORG_CODE ='$ORG_CODE'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	

				
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
			header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&password=".$password."&ORG_CODE=".$ORG_CODE."&year=".$year."&res=pass&msg=Record has been successfully updated!");
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}
}


if (isset($_POST['generateApp'])){
$ORG_CODE = $_POST['ORG_CODE'];
$year = $_POST['year_budget'];

$condition = "WHERE ORG_CODE = '$ORG_CODE' AND year_budget = '$year' AND (status = 'Approved' OR status = 'Endorsed')";
//check if other endorsement exist
$appCtr = $crudapp->matchRecord2($conn,"R5_APP_VERSION",'app_id',$condition);
if ($appCtr < 1){
$generateAPP = $crudapp->generateApp($conn,$year,$ORG_CODE);
	if( $generateAPP > 0) {

		//SEND EMAIL
		$emailfilter = "id = 2";
		$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
		$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
		$subject = @$emailinfo[0]['subject'];
		$body = @$emailinfo[0]['body'];
		$today = date("m/d/Y H:i");	
		$content = "This is to inform you that the Annual Procurement Plan for $year is pending for your approval as of $today";
		$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>Year: $year<br>";
		
		$body = str_replace("\$content",$content,$body);
		
		//EMAIL Receiver
		$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE CFO = 1)";
		$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
		$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
		$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
		$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);
		
		sqlsrv_commit( $conn );
		//echo "Transaction committed.<br />";
		header("Location:http://eamqas.fdcutilities.local:8080/crn/cgi-bin/mod_cognos.dll?b_action=cognosViewer&ui.action=run&ui.object=%2fcontent%2ffolder%5b%40name%3d%27DS_MP_1%27%5d%2freport%5b%40name%3d%27APP_CONSOLIDATED.xml%27%5d&ui.name=APP_CONSOLIDATED.xml&run.outputFormat=PDF&run.prompt=false&CAMUsername=R5&CAMPassword=R5&p_Version=$generateAPP&p_Org=$ORG_CODE&p_Year=$year&p_Status=Endorsed");
	} else {
		sqlsrv_rollback( $conn );
		//echo "Transaction rolled back.<br />";
		header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&password=".$password."&year=".$year."&res=fail&msg=Error on creating a new version of APP!");
	}
}else{
echo '<script>alert("Validation Error:\n\nYou have an existing APP!");</script>';
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
	
	$("#year_budget").val("<?php echo $year;?>");
	
	 var projectRef = $('#reference_no_project').val();
	
	 
	 if(projectRef == "" || projectRef == undefined){
	 $('#submit_project, #status_project, #remarks_project').attr("disabled", true);
	 }else{
	 $('#submit_project, #status_project, #remarks_project').attr("disabled", false);
	 }
	 
	
	//YEAR
	$("#year_budget").change(function() {
		var yr = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=".$user."&password=".$password."&ORG_CODE=".$ORG_CODE."&year="; ?>'+yr; 
	});
	
	$("#tabURL").click(function() {
		window.location = 'app-edorsement-approval-item.php<?php echo "?login=".$user."&password=".$password."&ORG_CODE=".$ORG_CODE."&year=".$year; ?>';
	});
	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&password=$password&year=$year&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
    var counter = 0;
    $(".list th").each(function(){
        var width = $('.list tr:last td:eq(' + counter + ')').width();
        $(".NewHeader tr").append(this);
        this.width = width;
        counter++;
    });
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&password=".$password."&ORG_CODE=".$ORG_CODE."&year=".$year; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">Endorsed APP</div></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="actionBar">
	<div class="divText">
		<!--<input type="button" class="bold" name="back_tmp" id="back_tmp" value=" Back ">-->
		<input type="submit" class="bold" name="generateApp" id="generateApp" value=" Generate APP ">
		<div class="hidden">
			<input type="submit" class="bold" name="back" id="back" value=" Back ">
		</div>
	</div>
</div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField">
					<?php 
									$tbname = "R5ORGANIZATION";
									$tbfield = "ORG_CODE";
									$tbfield2 = "ORG_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE ORG_CODE != '*'");
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
	</tbody>
</table>
</div>
<div class="headerText">Budget Breakdown</div>

<div class="headerText">
<input type="button" class="tabs" id="tabURL" name="ITEM-BASE" value=" ITEM-BASE / COST-BASED ">
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
				<td class="textLabel">Status:</td>
				<td class="textField">
				<input type="hidden" class="field" name="project_version" id="project_version" spellcheck="false" tabindex="1">
				<input type="hidden" class="field" name="reference_no_project" id="reference_no_project" spellcheck="false" tabindex="1">
				<select name="status_project" id="status_project">
					<option value="">-- Please select --</option>
					<option name="role" value="Endorsed">Endorse</option>
					<option name="role" value="Revision Request">Revision Request</option>
				</select>
				</td>
				<td class="textLabel">Remarks:</td>
				<td class="textField" colspan="3"><textarea id="remarks_project" name="remarks_project"></textarea></td>	
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
