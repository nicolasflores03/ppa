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
$MRC_CODE = @$_GET['MRC_CODE'];

$user = $_GET['login'];
$updateSession = $crudapp->updateSession($conn,$user);
$year = $_GET['year'];
$filter = array();

//GET SAM
$samfilter = "USR_CODE = '$user' AND ORG_CODE = '$ORG_CODE'";
$samcolumn = $crudapp->readColumn($conn,"R5_CUSTOM_SAM");
$saminfo = $crudapp->listTable($conn,"R5_CUSTOM_SAM",$samcolumn,$samfilter);
$bo = @$saminfo[0]['BO'];
$fi = @$saminfo[0]['FI'];
$dh = @$saminfo[0]['DH'];
$cfo =@$saminfo[0]['CFO'];
$cnd = "";
if($fi == 1 && $MRC_CODE == ""){
$cnd = "year_budget = $year AND ORG_CODE = '$ORG_CODE' ORDER BY ID DESC";
}else{
$cnd = "year_budget = $year AND ORG_CODE = '$ORG_CODE' AND (TO_MRC_CODE = '$MRC_CODE' OR FR_MRC_CODE = '$MRC_CODE') ORDER BY ID DESC";
}
//PROJECT BASE
$column2 = $crudapp->readColumn($conn,"R5_VIEW_BUDGET_MOVEMENT");
$requiredField2 = array('id','Source_Department','Destination_Department','Source','Destination','amount','year_budget','type','status','reason');
$column2 = array_intersect($column2,$requiredField2);
$listView2 = $crudapp->listTable($conn,"R5_VIEW_BUDGET_MOVEMENT",$column2,$cnd);
$tableView2 = $filterapp->filterViewURL2ID($conn,$column2,$listView2,$filter,"id");



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
		$tableView2 = $filterapp->filterViewURL2ID($conn,$column2,$listView2,$filter,"id");
	}else{
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}	
}


//ENDORSE or REJECT DEPT APP
if (isset($_POST['submit'])){
$id = $_POST['id'];
$status = $_POST['status'];
$remarks = $_POST['remarks'];
$frm_mrc = $_POST['frm_mrc'];
$to_mrc = $_POST['to_mrc'];

//Validation
if ($status == ""){
$errorMessage .= 'Please select status before submitting the form.\n\n';
$errorFlag = true;
}

$today = date("m/d/Y H:i");
	if(!$errorFlag){
		$data = array("status"=>$status,"remarks"=>$remarks,"updatedBy"=>$user,"updatedAt"=>$today);
		$table = "dbo.R5_BUDGET_MOVEMENT";
		$condition = "id = '$id'";
	$isSufficient = false;
	$isSufficient = $crudapp->checkBudgetMovementBalance($conn,$id);
	if($isSufficient){
		$updateStats = $crudapp->updateRecord2($conn,$data,$table,$condition);
		if ($status == "Approved"){
			$updateBudget = $crudapp->updateBudgetMovement($conn,$id);
		}else if ($status == "Revision Request"){
			$updateBudget = $crudapp->updateBudgetMovementRejected($conn,$id);
		}
		
			if( $updateStats == 1) {
				//SEND EMAIL
				
				/*--
				Created = Department Head
				For Review = Other Dept Head
				Endorsed = Send Finance/Budget Officer
				Approved = Send Dept Head / Budget Officer
				--*/

				if($status == "For Review"){
				
				//from
				$emailfilter = "id = 1";
				$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
				$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
				$subject = @$emailinfo[0]['subject'];
				$body = @$emailinfo[0]['body'];

				$content = "This is to inform you that there is a Budget Movement Request that is subject $status as of $today";
				$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>ID #: $id<br>";
				
				$body = str_replace("\$content",$content,$body);
			
				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$frm_mrc' AND ORG_CODE = '$ORG_CODE' AND DH = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$frm_mrc'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				
				//to
				
				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$to_mrc' AND ORG_CODE = '$ORG_CODE' AND DH = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$to_mrc'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				
				
				}else if ($status == "Endorsed"){
				
				//finance
				$emailfilter = "id = 1";
				$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
				$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
				$subject = @$emailinfo[0]['subject'];
				$body = @$emailinfo[0]['body'];

				$content = "This is to inform you that there is a Budget Movement Request that is subject $status as of $today";
				$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>ID #: $id<br>";
				
				$body = str_replace("\$content",$content,$body);
			
				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE ORG_CODE = '$ORG_CODE' AND FI = 1) AND ORG_CODE = '$ORG_CODE'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				
				
				
				}else if ($status == "Approved"){
				
				/* COMMENT AS PER SIR BOY REQUEST 09-16-2015 4:09PM
				
				//Department head
				$emailfilter = "id = 1";
				$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
				$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
				$subject = @$emailinfo[0]['subject'];
				$body = @$emailinfo[0]['body'];

				$content = "This is to inform you that there is a Budget Movement Request that was $status as of $today";
				$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>ID #: $id<br>";
				
				$body = str_replace("\$content",$content,$body);
			
				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$to_mrc' AND ORG_CODE = '$ORG_CODE' AND DH = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$to_mrc'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				
				//Budget Officer
				
				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$to_mrc' AND ORG_CODE = '$ORG_CODE' AND BO = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$to_mrc'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				*/
								
				}else{
				
				//Department head
				$emailfilter = "id = 1";
				$emailcolumn = $crudapp->readColumn($conn,"R5_EMAIL_TEMPLATE");
				$emailinfo = $crudapp->listTable($conn,"R5_EMAIL_TEMPLATE",$emailcolumn,$emailfilter);
				$subject = @$emailinfo[0]['subject'];
				$body = @$emailinfo[0]['body'];

				$content = "This is to inform you that there is a Budget Movement Request that was $status as of $today";
				$content .= "<br><b>Details:</b><br>Organization: $ORG_CODE<br>ID #: $id<br>";
				
				$body = str_replace("\$content",$content,$body);
			
				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$to_mrc' AND ORG_CODE = '$ORG_CODE' AND DH = 1) AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$to_mrc'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				
				/*
				if($cost_center == 'CC8001') {
					$crudapp->sentEmailCC($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
				} else {
					$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);			
				}
				*/
				
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				
				//Budget Officer
				$receiverId = $crudapp->GetReceiverId($conn,$id);

				//EMAIL Receiver
				$receiverfilter = "USR_CODE COLLATE Latin1_General_CI_AS IN (SELECT USR_CODE FROM R5_CUSTOM_SAM WHERE MRC_CODE = '$to_mrc' AND ORG_CODE = '$ORG_CODE' AND BO = 1 AND USR_CODE = '$receiverId') AND ORG_CODE = '$ORG_CODE' AND MRC_CODE = '$to_mrc'";
				$receivercolumn = $crudapp->readColumn($conn,"R5_VIEW_USERINFO");
				
				$receiverinfo = $crudapp->listTable($conn,"R5_VIEW_USERINFO",$receivercolumn,$receiverfilter);
				$receiver = @$receiverinfo[0]['PER_EMAILADDRESS'];
				$crudapp->sentEmail($conn,"eam@fdcutilities.com",$receiver,$subject,$body);	
				
				}
				
				
				sqlsrv_commit( $conn );
				echo "Transaction committed.<br />";
			} else {
				sqlsrv_rollback( $conn );
				echo "Transaction rolled back.<br />";
			}
			header("Location:".$_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&ORG_CODE=".$ORG_CODE."&MRC_CODE=".$MRC_CODE."&res=pass&msg=You have successfully updated the status of this records!");
	}else{
		$errorMessage = "Insufficient budget!";
		echo '<script>alert("Validation Error:\n\n'.$errorMessage.'");</script>';
	}
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
function onclickProjectEvent(id){
$('#submit, #status, #remarks').attr("disabled", false);
$('#id').val(id);
//alert(id);
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
	  var status = json['status'];type
	  var type = json['type'];
	  var FR_MRC_CODE = json['FR_MRC_CODE'];
	  var TO_MRC_CODE = json['TO_MRC_CODE'];
	  $('#frm_mrc').val(FR_MRC_CODE);
	  $('#to_mrc').val(TO_MRC_CODE);
	  var fi = "<?php echo $fi; ?>";
	  
	var responsible ='';
	 if (status != 'Approved'){
	  responsible = json['USR_DESC'];
	 }else{
	  responsible = '';
	 }	
	$('#responsible').val(responsible);
	
	  //alert(status);
	  //<option name="role" value="Approve">Approve</option>
	  var str = "";
	  if (status == "Created" && type == "supplement"){
	    str = '<option value="">-- Please select --</option><option name="role" value="Endorsed">Endorse</option><option name="role" value="Revision Request">Revision Request</option><option name="role" value="Rejected">Reject</option>'; 
	  }else  if (status == "Created" && type == "reallocation" && FR_MRC_CODE != TO_MRC_CODE){
	    str = '<option value="">-- Please select --</option><option name="role" value="For Review">For Review</option><option name="role" value="Revision Request">Revision Request</option><option name="role" value="Rejected">Reject</option>';  
	  }else  if (status == "Created" && type == "reallocation" && FR_MRC_CODE == TO_MRC_CODE){
	    str = '<option value="">-- Please select --</option><option name="role" value="Endorsed">Endorse</option><option name="role" value="Revision Request">Revision Request</option><option name="role" value="Rejected">Reject</option>';  
	  }else  if (status == "For Review" && type == "reallocation"){
	    str = '<option value="">-- Please select --</option><option name="role" value="Endorsed">Endorse</option><option name="role" value="Revision Request">Revision Request</option><option name="role" value="Rejected">Reject</option>';  
	  }else if (status == "Endorsed"){
		str = '<option value="">-- Please select --</option><option name="role" value="Approved">Approve</option><option name="role" value="Revision Request">Revision Request</option><option name="role" value="Rejected">Reject</option>';
	  }else{
	   str = '<option value="">-- Please select --</option>';
	  }
	  $('#status').html(str);
	  if (fi < 1 && (status == "Endorsed" || status == "Approved")){
	  $('#status').attr('disabled', 'true'); // mark it as read only
	  }
    }
  }
xmlhttp.open("GET","ajax/app-get-budget-movement-info.php?hash="+text+"&id="+id,true);
xmlhttp.send();
}
$(document).ready(function(){

$('.test').click(function() {
$('.test').removeClass('addedclass');
$(this).addClass('addedclass');
})
	  	  
//Error Message
var res = "<?php echo @$res;?>";
$("#ORG_CODE").val("<?php echo $ORG_CODE;?>");
$("#MRC_CODE").val("<?php echo $MRC_CODE;?>");

if(res !=""){
	if (res == "pass"){
		$('.isa_success').show();
		$('.isa_error').hide();
	}else {
		$('.isa_error').show();
		$('.isa_success').hide();
	}
}
	
	var year = '<?php echo $year;?>';
	if (year == 0){
	$("#year_budget")[0].selectedIndex=1;
	}else{
	$("#year_budget").val(year);
	}
	
	 var id = $('#id').val();
	
	 
	 if(id == "" || id == undefined){
	 $('#submit, #status, #remarks').attr("disabled", true);
	 }else{
	 $('#submit, #status, #remarks').attr("disabled", false);
	 }
	 
	
	//YEAR
	$("#year_budget").change(function() {
		var yr = $(this).val();
		if (yr == ""){
		yr = 0;
		}
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=".$user."&ORG_CODE=$ORG_CODE&MRC_CODE=$MRC_CODE&year="; ?>'+yr; 
	});
	
	$("#tabURL").click(function() {
		window.location = 'app-edorsement-approval-item.php<?php echo "?login=".$user."&year=".$year; ?>';
	});
	
	
	$("#ORG_CODE").change(function() {
		var ORG_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&year=$year&ORG_CODE="; ?>'+ORG_CODE; 
	});
	
	$("#MRC_CODE").change(function() {
		var MRC_CODE = $(this).val();
		window.location.href = '<?php echo $_SERVER["PHP_SELF"]."?login=$user&year=$year&ORG_CODE=$ORG_CODE&MRC_CODE="; ?>'+MRC_CODE; 
	});
	
	
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
	
	var user = "<?php echo $user; ?>";
	var year = "<?php echo $year; ?>";
	//Save Button
	$("#tabURL").click(function() {
		window.location = "app-budget-movement-approval-cost.php?login="+user+"&year="+year;
	});
	
	$("#newRecord").click(function() {
		window.location = "app-budget-movement-approval.php?login="+user+"&year="+year;
	});
	
});
</script>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']."?login=".$user."&year=".$year."&ORG_CODE=".$ORG_CODE."&MRC_CODE=".$MRC_CODE; ?>" method="post" name="theForm" enctype="multipart/form-data">
<div class="headerText2"><div id="divText">Approve Re-allocation / Supplement</div></div>
<div class="isa_success"><?php echo $msg; ?></div>
<div class="isa_error"><?php echo $msg; ?></div>
<div class="mainContainer">
<div class="formHeader">
<table class="header" border="0" cellspacing="5px" width="100%">
	<tbody>
		<tr>
			<td class="textLabel">Organization:</td>
			<td class="textField">
					<?php 
							if($fi == 1){
									$tbname = "R5ORGANIZATION";
									$tbfield = "ORG_CODE";
									$tbfield2 = "ORG_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE ORG_CODE != '*'");
							}else{
									$tbname = "R5_VIEW_USERINFO";
									$tbfield = "DISTINCT(ORG_CODE)";
									$tbfield2 = "ORG_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user'");
							}
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
		<tr>
			<td class="textLabel">Department:</td>
			<td class="textField">
					<?php 
							if($fi == 1){
									$tbname = "R5MRCS";
									$tbfield = "MRC_CODE";
									$tbfield2 = "MRC_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE MRC_CODE != '*'");
							}else{
									$tbname = "R5_VIEW_USERINFO";
									$tbfield = "MRC_CODE";
									$tbfield2 = "MRC_DESC";
									$crudapp->optionValue4($conn,$tbname,$tbfield,$tbfield2,"WHERE USR_CODE = '$user' AND ORG_CODE = '$ORG_CODE'");
							}
					?>			
			</td>			
			<td class="textLabel"></td>
			<td class="textField">
			</td>				
		</tr>
	</tbody>
</table>
</div>

<div class="headerText">
<!--<input type="button" class="tabs selected" name="ITEM-BASE" value=" ITEM-BASED ">-->
<!--<input type="button" class="tabs" name="COST-BASE" id="tabURL" value=" COST-BASED ">-->
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
			$requiredFilter = array('id','Source','Destination','year_budget','status','reason');
			$column = array_intersect($requiredField2,$requiredFilter);
			
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

<div class="headerText">Budget Movement Approval</div>
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
				<input type="hidden" class="field" name="id" id="id" spellcheck="false" tabindex="1">
				<input type="hidden" class="field" name="to_mrc" id="to_mrc" spellcheck="false" tabindex="1">
				<input type="hidden" class="field" name="frm_mrc" id="frm_mrc" spellcheck="false" tabindex="1">	
				<select name="status" id="status">
					<option value="">-- Please select --</option>
				</select>
				</td>
				<td class="textLabel">Remarks:</td>
				<td class="textField" colspan="3"><textarea id="remarks" name="remarks"></textarea></td>	
			</tr>
			<tr>				

			
			<td class="textLabel">Responsible:</td>
			<td class="textField" colspan="3"><input type="text" class="field" name="responsible" id="responsible" spellcheck="false" tabindex="1" value="" readonly=""></td>
	
			
				<td class="textLabel"></td>
				<td class="textField" colspan="3"></td>	
			</tr>
		</tbody>
	</table>
	<!--Action Button-->
	<div class="actionButtonCenter">
		<input type="submit" class="bold" name="submit" id="submit" value=" Submit ">&nbsp;&nbsp;
	</div>
	</div>
	
</div>
</form>
</body>
</html>  
