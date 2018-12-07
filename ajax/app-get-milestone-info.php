<?php
//Include external Files
include("../include/connect.php");
include("../class/crud.php");
$id=$_GET["id"];
$status=$_GET["status"];
$htmlContent = ''; 
$crudapp = new crudClass();
//Milestone per project
$cnd = "id = '$id'";
$column = array('milestone','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','budget_amount','id','milestoneID');

		$sql = "SELECT milestone,convert(varchar,cast(Jan as money),1) AS Jan,convert(varchar,cast(Feb as money),1) AS Feb,convert(varchar,cast(Mar as money),1) AS Mar,convert(varchar,cast(Apr as money),1) AS Apr,convert(varchar,cast(May as money),1) AS May,convert(varchar,cast(Jun as money),1) AS Jun,convert(varchar,cast(Jul as money),1) AS Jul,convert(varchar,cast(Aug as money),1) AS Aug,convert(varchar,cast(Sep as money),1) AS Sep,convert(varchar,cast(Oct as money),1) AS Oct,convert(varchar,cast(Nov as money),1) AS Nov,convert(varchar,cast(Dec as money),1) AS Dec,convert(varchar,cast(budget_amount as money),1) AS budget_amount,id,milestoneID FROM dbo.R5_EAM_APP_PROJECTBASE_MILESTONE WHERE $cnd";
		$result = sqlsrv_query($conn,$sql);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {			
			foreach($column as $fieldName){
				$data["$fieldName"] = $val["$fieldName"];
			}	
			array_push($resultArr,$data);
		}
	
			$ctr = 1;	
	foreach($resultArr AS $data){
		$td = "";	
		$milestoneID = $data['milestoneID'];
		$htmlContent .= "<tr class='tr_$milestoneID'>";
		$milestone = $data['milestone'];
		$Jan = $data['Jan'];
		$Feb = $data['Feb'];
		$Mar = $data['Mar'];
		$Apr = $data['Apr'];
		$May = $data['May'];
		$Jun = $data['Jun'];
		$Jul = $data['Jul'];
		$Aug = $data['Aug'];
		$Sep = $data['Sep'];
		$Oct = $data['Oct'];
		$Nov = $data['Nov'];
		$Dec = $data['Dec'];
		$budget_amount = $data['budget_amount'];
		
		$tbname = "R5PROJBUDCODES";
		$tbfield = "PBC";
		$a = $crudapp->optionValueUpdateMilestone($conn,$milestone);
		//action button depends on status
		if($status=="For Endorsement"){
			$td = "<td><input type='hidden' name='updateid[]' value='$milestoneID'/>$a</td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJan[]' id='january' spellcheck='false' tabindex='1' value='$Jan'  onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateFeb[]' id='february' spellcheck='false' tabindex='1' value='$Feb' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMar[]' id='march' spellcheck='false' tabindex='1' value='$Mar' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateApr[]' id='april' spellcheck='false' tabindex='1' value='$Apr' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMay[]' id='may' spellcheck='false' tabindex='1' value='$May' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJun[]' id='june' spellcheck='false' tabindex='1' value='$Jun' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJul[]' id='july' spellcheck='false' tabindex='1' value='$Jul' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateAug[]' id='august' spellcheck='false' tabindex='1' value='$Aug' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateSep[]' id='september' spellcheck='false' tabindex='1' value='$Sep' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateOct[]' id='october' spellcheck='false' tabindex='1' value='$Oct' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateNov[]' id='november' spellcheck='false' tabindex='1' value='$Nov' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateDec[]' id='december' spellcheck='false' tabindex='1' value='$Dec' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='total' id='total' spellcheck='false' tabindex='1' value='$budget_amount' disabled></td>";
		}else if($status=="Endorsed"){
			$td = "<td><input type='hidden' name='updateid[]' value='$milestoneID'/>$a</td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJan[]' id='january' spellcheck='false' tabindex='1' value='$Jan' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateFeb[]' id='february' spellcheck='false' tabindex='1' value='$Feb' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMar[]' id='march' spellcheck='false' tabindex='1' value='$Mar' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateApr[]' id='april' spellcheck='false' tabindex='1' value='$Apr' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMay[]' id='may' spellcheck='false' tabindex='1' value='$May' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJun[]' id='june' spellcheck='false' tabindex='1' value='$Jun' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJul[]' id='july' spellcheck='false' tabindex='1' value='$Jul' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateAug[]' id='august' spellcheck='false' tabindex='1' value='$Aug' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateSep[]' id='september' spellcheck='false' tabindex='1' value='$Sep' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateOct[]' id='october' spellcheck='false' tabindex='1' value='$Oct' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateNov[]' id='november' spellcheck='false' tabindex='1' value='$Nov' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateDec[]' id='december' spellcheck='false' tabindex='1' value='$Dec' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatetotal".$ctr."' name='total' id='total' spellcheck='false' tabindex='1' value='$budget_amount' disabled></td>";
		}else if($status=="Revision Request"){
			$td = "<td><input type='hidden' name='updateid[]' value='$milestoneID'/>$a</td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJan[]' id='january' spellcheck='false' tabindex='1' value='$Jan' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateFeb[]' id='february' spellcheck='false' tabindex='1' value='$Feb' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMar[]' id='march' spellcheck='false' tabindex='1' value='$Mar' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateApr[]' id='april' spellcheck='false' tabindex='1' value='$Apr' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMay[]' id='may' spellcheck='false' tabindex='1' value='$May' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJun[]' id='june' spellcheck='false' tabindex='1' value='$Jun' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJul[]' id='july' spellcheck='false' tabindex='1' value='$Jul' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateAug[]' id='august' spellcheck='false' tabindex='1' value='$Aug' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateSep[]' id='september' spellcheck='false' tabindex='1' value='$Sep' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateOct[]' id='october' spellcheck='false' tabindex='1' value='$Oct' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateNov[]' id='november' spellcheck='false' tabindex='1' value='$Nov' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateDec[]' id='december' spellcheck='false' tabindex='1' value='$Dec' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatetotal".$ctr."' name='total' id='total' spellcheck='false' tabindex='1' value='$budget_amount' disabled></td>";
		}else{
			$td = "<td><input type='hidden' name='updateid[]' value='$milestoneID'/>$a</td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJan[]' id='january' spellcheck='false' tabindex='1' value='$Jan' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateFeb[]' id='february' spellcheck='false' tabindex='1' value='$Feb' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMar[]' id='march' spellcheck='false' tabindex='1' value='$Mar' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateApr[]' id='april' spellcheck='false' tabindex='1' value='$Apr' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateMay[]' id='may' spellcheck='false' tabindex='1' value='$May' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJun[]' id='june' spellcheck='false' tabindex='1' value='$Jun' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateJul[]' id='july' spellcheck='false' tabindex='1' value='$Jul' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateAug[]' id='august' spellcheck='false' tabindex='1' value='$Aug' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateSep[]' id='september' spellcheck='false' tabindex='1' value='$Sep' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateOct[]' id='october' spellcheck='false' tabindex='1' value='$Oct' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateNov[]' id='november' spellcheck='false' tabindex='1' value='$Nov' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatescheduleField".$ctr."' name='updateDec[]' id='december' spellcheck='false' tabindex='1' value='$Dec' onchange ='getTotal(this);' onkeypress='return numbersonly(this, event)' onblur='round(this,2);'></td>".
		"<td><input type='text' class='scheduleField updatetotal".$ctr."' name='total' id='total' spellcheck='false' tabindex='1' value='$budget_amount' disabled></td><td><a href='javascript:void(0);' onClick='remCFupdate($milestoneID);'>Remove</a></td>";
		}	
		$htmlContent .= $td;		
		$htmlContent .= '</tr>';
		$ctr++;
	}
echo $htmlContent;
?>