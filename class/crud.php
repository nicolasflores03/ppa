<?php
$_GET['login'] = "EAMSYSADMIN";
class crudClass{

/* Update Session*/
public function updateSession($conn,$user)
{
$user = str_replace(" ","",$user);;
$SES_EXPIRES = 0;
$INS_DESC = 0;
$UGR_SESSIONTIMEOUT = 0;
$USR_SESSIONTIMEOUT = 0;
if($user != " "){


		$cnt = 0;
		$sqlcnt = "SELECT COUNT(SES_USER) FROM dbo.R5SESSIONS WHERE SES_USER = ?";
		$paramscnt = array($user);
		$resultcnt = sqlsrv_query($conn,$sqlcnt,$paramscnt);
		if( $resultcnt === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($valcnt = sqlsrv_fetch_array($resultcnt, SQLSRV_FETCH_ASSOC)){
			$cnt = $valcnt[''];
		}
IF($cnt > 0){		
	//$currentTime = time();
	$sql = "SELECT CONVERT(VARCHAR(24),dateadd(MINUTE, -5, SES_LASTUPDATED),120) AS sessionExpires,CONVERT(VARCHAR(24), SES_LASTUPDATED,120) AS sessionExpires2 FROM dbo.R5SESSIONS WHERE SES_USER = '$user'";
	$result = sqlsrv_query($conn,$sql);
	if( $result === false) {
	die( print_r( sqlsrv_errors(), true) );
	}
	while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		$SES_EXPIRES = $val['sessionExpires'];
		$SES_EXPIRES2 = $val['sessionExpires2'];
	}
	
$currentTime = date("Y/m/d H:i:s");

$currentTime = new DateTime($currentTime);
$SES_EXPIRES = new DateTime($SES_EXPIRES);
$SES_EXPIRES2 = new DateTime($SES_EXPIRES2);

	//GET THE R5INSTALL SESSION INTERVAL
	$sql2 = "SELECT INS_DESC FROM dbo.R5INSTALL WHERE INS_CODE = 'SESINTVL'";
	$result2 = sqlsrv_query($conn,$sql2);
	if( $result2 === false) {
	die( print_r( sqlsrv_errors(), true) );
	}
	while($val2 = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC)){
		$INS_DESC = $val2['INS_DESC'];
	}
	
	
	//GET THE R5GROUPS SESSION INTERVAL
	$sql3 = "SELECT UGR_SESSIONTIMEOUT FROM dbo.R5GROUPS WHERE UGR_CODE = 'R5'";
	$result3 = sqlsrv_query($conn,$sql3);
	if( $result3 === false) {
	die( print_r( sqlsrv_errors(), true) );
	}
	while($val3 = sqlsrv_fetch_array($result3, SQLSRV_FETCH_ASSOC)){
		$UGR_SESSIONTIMEOUT = $val3['UGR_SESSIONTIMEOUT'];
	}
	
	
	//GET THE R5USERS SESSION INTERVAL
	$sql4 = "SELECT USR_SESSIONTIMEOUT FROM dbo.R5USERS WHERE USR_CODE = '$user'";
	$result4 = sqlsrv_query($conn,$sql4);
	if( $result4 === false) {
	die( print_r( sqlsrv_errors(), true) );
	}
	while($val4 = sqlsrv_fetch_array($result4, SQLSRV_FETCH_ASSOC)){
		$USR_SESSIONTIMEOUT = $val4['USR_SESSIONTIMEOUT'];
	}
		
	IF ($currentTime > $SES_EXPIRES AND $currentTime < $SES_EXPIRES2){
		$sessionInterval = 0;
		$newSession = 0;
		$additionalTime = 0;
		IF($USR_SESSIONTIMEOUT > 0){
		$sessionInterval = $USR_SESSIONTIMEOUT * 60;
		}
		ELSE IF($UGR_SESSIONTIMEOUT > 0){
		$sessionInterval = $UGR_SESSIONTIMEOUT * 60;
		}ELSE{
		$sessionInterval = $INS_DESC * 60;
		}
		$additionalTime = $sessionInterval + 120;
						
		$stringCurrentTime = $currentTime->format('Y-m-d H:i:s');
				
		$currentDate = strtotime($stringCurrentTime);
		

		$futureDate = $currentDate+$additionalTime;
		$formatDate = date("Y-m-d H:i:s", $futureDate);
		
		//UPDATE SESSION
		$updatesql = "UPDATE dbo.R5SESSIONS SET 
		SES_LASTUPDATED = ?
		WHERE SES_USER = ?";
		$params = array($formatDate,$user);
		$resultsql = sqlsrv_query($conn,$updatesql,$params);
		
		if( $resultsql === false) {
			die( print_r( sqlsrv_errors(), true) );
		}		
	}
}
}
}

/*-------START OF TABLE FIELD LIST-------*/

/* Check if record exist(PROJECT)*/
	    public function matchRecord($conn,$table,$code,$value)
    {			
		$cnt = 0;
		$sql = "SELECT COUNT($code) FROM dbo.$table WHERE $code = ?";
		$params = array($value);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$cnt = $val[''];
		}
		if($result){
			return $cnt;
		}
		else{
			return $cnt;
		}
	}
	
/* Check if record exist(PROJECT)*/
	    public function matchRecord2($conn,$table,$identifier,$condition)
    {			
		$cnt = 0;
		$sql = "SELECT COUNT($identifier) FROM dbo.$table $condition";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$cnt = $val[''];
		}
		if($result){
			return $cnt;
		}
		else{
			return $cnt;
		}
	}
		
/* Insert Record to Table*/
	    public function generateApp($conn,$year,$ORG_CODE)
    {			
		$today = date("m/d/Y H:i");	
		//SELECT APP Version
		$sqlVer = "SELECT TOP 1 version FROM dbo.R5_APP_VERSION WHERE year_budget = ?  AND ORG_CODE = ? ORDER BY version desc";
		$paramsVer = array($year,$ORG_CODE);
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$resultVer = sqlsrv_query($conn,$sqlVer,$paramsVer, $options );
		$version = 0;
		$row_count = sqlsrv_num_rows( $resultVer );

		if($row_count > 0){
			while($valVer = sqlsrv_fetch_array($resultVer, SQLSRV_FETCH_ASSOC)) {	
				$version = $valVer['version'];
				$version =  $version + 1;
			}
		}else{
		$version = 1;
		}
		
		$versionReturn = $version;
		$app_id = "";
		//SELECT APP ID
		$sqlID = "SELECT COUNT(app_id) FROM dbo.R5_APP_VERSION";
		$resultID = sqlsrv_query($conn,$sqlID);
		if( $resultID === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($valID = sqlsrv_fetch_array($resultID, SQLSRV_FETCH_ASSOC)){
			$app_id = $valID[''];
		}
		
		
		//Insert record to AUDIT
		$sqlaudit = "INSERT INTO dbo.R5_CUSTOM_AUDIT_APP (app_id,status_to,updatedAt) VALUES(?,?,?)"; 
		
		$paramsaudit = array($app_id,"Endorsed",$today);
		
		$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
		
		if( $resultaudit === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		
		//ITEM BASED/COST BASE
		$sql = "SELECT reference_no,version FROM dbo.R5_DPP_VERSION WHERE year_budget = ? AND ORG_CODE = ? AND (status = 'Endorsed' OR status = 'Approved')";
		$params = array($year,$ORG_CODE);
		
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

		//PROJECT BASE
		$sqlProject = "SELECT reference_no,version FROM dbo.R5_PROJECT_VERSION WHERE year_budget = ? AND ORG_CODE = ? AND (status = 'Endorsed' OR status = 'Approved')";
		$paramsProject = array($year,$ORG_CODE);
		
		$resultProject = sqlsrv_query($conn,$sqlProject,$paramsProject);
		
		if( $resultProject === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		//Insert record to r5_app_version
		$sql2 = "INSERT INTO dbo.R5_APP_VERSION (app_id,version,ORG_CODE,year_budget,status,updatedAt,createdAt) VALUES(?,?,?,?,?,?,?)"; 
		
		$params2 = array($app_id,$version,$ORG_CODE,$year,"Endorsed",$today,$today);
		
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
			
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {	
			$reference_no = $val['reference_no'];
			$version = $val['version'];
			//Insert record to r5_app_version_details
			$sql3 = "INSERT INTO dbo.R5_APP_VERSION_ITEMBASE_LINES (reference_no,app_id,version) VALUES(?,?,?)"; 
			
			$params3 = array($reference_no,$app_id,$version);
			
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			
			if( $result3 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		}
		
		while($valProject = sqlsrv_fetch_array($resultProject, SQLSRV_FETCH_ASSOC)) {	
			$reference_no_project = $valProject['reference_no'];
			$version_project = $valProject['version'];
			//Insert record to r5_app_version_details
			$sqlProjectInsert = "INSERT INTO dbo.R5_APP_VERSION_PROJECT_LINES (reference_no,app_id,version) VALUES(?,?,?)"; 
			
			$paramsProjectInsert = array($reference_no_project,$app_id,$version_project);
			
			$resultProjectInsert = sqlsrv_query($conn,$sqlProjectInsert,$paramsProjectInsert);
		}
		
		if(@$result3 || @$resultProjectInsert){
			return $versionReturn;
		}
		else{
			return 0;
		}
	} 
	
	/*Read ID from Table*/
    public function readREF($conn,$table)
    {
		$sql = "SELECT MAX(convert(int, reference_no)) FROM $table";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$id = $val[''];
		}
       return $id;
    }
	
	
	/*Read ID from Table*/
    public function readID($conn,$table)
    {
		$sql = "SELECT MAX(id) FROM $table";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$id = $val[''];
		}
       return $id;
    }
	
	/*Read VERSION from Table*/
    public function readVersion($conn,$table,$condition)
    {
		$sql = "SELECT MAX(version) FROM $table WHERE $condition";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$id = $val[''];
		}
       return $id;
    }
	
	
	/*Read ID from Table*/
    public function checkRecordExist($conn,$table,$filter)
    {
		$sql = "SELECT count(id) FROM $table WHERE $filter";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$id = $val[''];
		}
       return $id;
    }
	
	/*Read ID from Table*/
    public function checkStatus($conn,$table,$filter)
    {
		$sql = "SELECT status FROM $table WHERE $filter";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$status = $val['status'];
		}
       return $status;
    }
	
	/*Read EMAIL for Resend*/
    public function checkEmailResend($conn)
    {
		$sql = "SELECT MAE_ID,MAE_SUBJECT,MAE_BODY,MAE_EMAILRECIPIENT FROM dbo.R5_CUSTOM_MAILEVENTS WHERE MAE_RSTATUS = 'E'";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
	   
	   	$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$data['MAE_ID'] = $val['MAE_ID'];
			$data['MAE_SUBJECT'] = $val['MAE_SUBJECT'];
			$data['MAE_BODY'] = $val['MAE_BODY'];
			$data['MAE_EMAILRECIPIENT'] = $val['MAE_EMAILRECIPIENT'];
			array_push($resultArr,$data);
		}
        return $resultArr;	
    }
	
	/*Read ID from Table*/
    public function checkRate($conn,$table,$filter)
    {
		$sql = "SELECT TOP 1(CRR_EXCH) FROM $table WHERE $filter";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		$ex = "";
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$ex = $val['CRR_EXCH'];
		}
	   if ($ex!=""){
	   return $ex;
	   }else{
	   return "none";
	   }
    }
	
	/*Read ID from Table*/
    public function unsaveApp($conn,$reference_no,$version)
    {
		$sql = "SELECT COUNT(id) FROM dbo.R5_EAM_DPP_ITEMBASE_LINES WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params = array($reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$recordCnt = $val[''];
		}

		$sql2 = "SELECT COUNT(id) FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE saveFlag = 0 AND reference_no = ?";
		$params2 = array($reference_no);
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val2 = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC)){
			$recordCnt2 = $val2[''];
		}
		
       return $recordCnt + $recordCnt2;
    }
	

	/*Read ID from Table*/
    public function unsaveProjectApp($conn,$reference_no,$version)
    {
		$sql = "SELECT COUNT(id) FROM dbo.R5_EAM_APP_PROJECTBASE_LINES WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params = array($reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		 while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$recordCnt = $val[''];
		}
		
       return $recordCnt;
    }
	
		/*SAVE DEP APP from Table*/
    public function saveApp($conn,$reference_no,$version)
    {
		//Item Base
		$sql = "UPDATE dbo.R5_EAM_DPP_ITEMBASE_LINES SET 
        saveFlag = 1		
		WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params = array($reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		//Cost Base
		$sql2 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
        saveFlag = 1		
		WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params2 = array($reference_no,$version);
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result && $result2){
		return true;
		}
		else{
		return false;
		}	
    }
	
	/*SAVE Project APP from Table*/
    public function saveProjectApp($conn,$reference_no,$version)
    {
		$sql = "UPDATE dbo.R5_EAM_APP_PROJECTBASE_LINES SET 
        saveFlag = 1		
		WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params = array($reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
    }
	
		/*Endorse DEP APP from Table*/
    public function endorseApp($conn,$data)
    {
		$ORG_CODE = $data[0];
		$MRC_CODE = $data[1];
		$year_budget = $data[2];
		$reference_no = $data[3];
		$version = $data[4];
		
		//Insert Record to R5_DPP_VERSION
		$sql = "UPDATE dbo.R5_DPP_VERSION SET 
		status = 'For Endorsement' 
		WHERE ORG_CODE = ? AND MRC_CODE = ? AND year_budget = ? AND reference_no = ? AND version = ?";
		$params = array($ORG_CODE,$MRC_CODE,$year_budget,$reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}			
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
		
		if($resultCost){
		return true;
		}else{
		return false;
		}	
    }

		/*Submit DEP APP from Table*/
    public function submitApp($conn,$data)
    {
		$ORG_CODE = $data[0];
		$MRC_CODE = $data[1];
		$year_budget = $data[2];
		$reference_no = $data[3];
		$version = $data[4];
		
		//Insert Record to R5_DPP_VERSION
		$sql = "UPDATE dbo.R5_DPP_VERSION SET 
		status = 'Submitted' 
		WHERE ORG_CODE = ? AND MRC_CODE = ? AND year_budget = ? AND reference_no = ? AND version = ?";
		$params = array($ORG_CODE,$MRC_CODE,$year_budget,$reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}			
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
		
		if($resultCost){
		return true;
		}else{
		return false;
		}	
    }
	
//REVISED APP
		/*Submit DEP APP from Table*/
    public function reviseApp($conn,$data)
    {
		$ORG_CODE = $data[0];
		$MRC_CODE = $data[1];
		$year_budget = $data[2];
		$reference_no = $data[3];
		$version = $data[4];
		
		//Insert Record to R5_DPP_VERSION
		$sql = "UPDATE dbo.R5_DPP_VERSION SET 
		status = 'For Revision' 
		WHERE ORG_CODE = ? AND MRC_CODE = ? AND year_budget = ? AND reference_no = ? AND version = ?";
		$params = array($ORG_CODE,$MRC_CODE,$year_budget,$reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}			
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
    }

	/*Endorse Project APP from Table*/
    public function endorseProjectApp($conn,$data)
    {
		$ORG_CODE = $data[0];
		$year_budget = $data[1];
		$reference_no = $data[2];
		$version = $data[3];
		
		//Insert Recoord to R5_PROJECT_VERSION
		$sql = "UPDATE dbo.R5_PROJECT_VERSION SET 
		status = 'For Endorsement' 
		WHERE ORG_CODE = ? AND year_budget = ? AND reference_no = ? AND version = ?";
		$params = array($ORG_CODE,$year_budget,$reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}			
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
    }
	
		/*SAVE DEP APP from Table*/
    public function updateAppStatus($conn,$id,$action,$user)
    {
		//Item Base
		$sql = "UPDATE dbo.R5_APP_VERSION SET 
        status = ?		
		WHERE app_id = ?";
		$params = array($action,$id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($action != "Revision Request"){
			//Item Base
			$sql2 = "UPDATE dbo.R5_DPP_VERSION SET 
			status = ?		
			WHERE id IN (SELECT id FROM dbo.R5_APP_ITEMBASE_VERSION_LOOKUP WHERE app_id = ? AND status = 'Endorsed')";
			$params2 = array($action,$id);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			if( $result2 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			//Project Base
			$sql2 = "UPDATE dbo.R5_PROJECT_VERSION SET 
			status = ?		
			WHERE id IN (SELECT id FROM dbo.R5_APP_PROJECTBASE_VERSION_LOOKUP WHERE app_id = ? AND status = 'Endorsed')";
			$params2 = array($action,$id);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			if( $result2 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
	
	
	
		//Insert to R5PROJBUDCLASSES
			$sql3 = "SELECT PRJ_CODE,PBC_CODE,budget_amount,ORG_CODE,milestone FROM dbo.R5_VIEW_PROJECT_LINES_APPROVED WHERE r5project_id IN (SELECT id FROM dbo.R5_APP_PROJECTBASE_VERSION_LOOKUP WHERE app_id = ? AND status = 'Approved')";
			//echo $sql3;
			$params3 = array($id);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			if( $result3 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($val3 = sqlsrv_fetch_array($result3, SQLSRV_FETCH_ASSOC)){
			$PRJ_CODE = $val3['PRJ_CODE'];
			$PBC_CODE = $val3['PBC_CODE'];
			$budget_amount = $val3['budget_amount'];
			$ORG_CODE = $val3['ORG_CODE'];
			$milestone = $val3['milestone'];
			
			//echo $PRJ_CODE."--".$PBC_CODE."--".$budget_amount."--".$ORG_CODE."--".$milestone;

				$sql4 = "INSERT INTO dbo.R5PROJBUDCLASSES(PCL_PROJECT,PCL_PROJBUD,PCL_AMOUNT,PCL_CLASS,PCL_CLASS_ORG,PCL_DESC) VALUES (?,?,?,?,?,?)";
				echo $sql4;
				$params4 = array($PRJ_CODE,$PBC_CODE,$budget_amount,$ORG_CODE,$ORG_CODE,$milestone);		
				$result4 = sqlsrv_query($conn,$sql4,$params4);
				if( $result4 === false) {
				die( print_r( sqlsrv_errors(), true) );
				}
			}
			//Update R5EVENTS
			/*
			$selectStr = "SELECT description, ORG_CODE, MRC_CODE FROM dbo.R5_VIEW_COSTBASE_WORKORDER WHERE app_id = ?";
			$params = array($id);
			$selectresult = sqlsrv_query($conn,$selectStr,$params);
			
			if( $selectresult === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($seletcors = sqlsrv_fetch_array($selectresult, SQLSRV_FETCH_ASSOC)) {
			$description = $seletcors['description'];
			$ORG_CODE = $seletcors['ORG_CODE'];
			$MRC_CODE = $seletcors['MRC_CODE'];
			
			$sqlinsert = "INSERT INTO dbo.R5EVENTS(EVT_CODE,EVT_TYPE,EVT_DESC,EVT_OBJECT,EVT_OBJECT_ORG,EVT_JOBTYPE,EVT_MRC,EVT_ORG,EVT_STATUS,EVT_DATE,EVT_DURATION,EVT_RTYPE,EVT_RSTATUS,EVT_OBTYPE) VALUES ((SELECT MAX(EVT_CODE)+1 AS EVT_CODE FROM dbo.R5EVENTS),'JOB','$description','A','*','BRKD','$MRC_CODE','$ORG_CODE','Q','2014-03-14 14:48:27.377',1,'JOB','Q','A')"; 
			
				$resultinsert = sqlsrv_query($conn,$sqlinsert);
				
				if( $resultinsert === false) {
				die( print_r( sqlsrv_errors(), true) );
				}
				
			}*/
		}
		
		
		//Insert record to AUDIT
		$today = date("m/d/Y H:i");	
		$sqlaudit = "INSERT INTO dbo.R5_CUSTOM_AUDIT_APP (app_id,status_from,status_to,updatedBy,updatedAt) VALUES(?,?,?,?,?)"; 
		
		$paramsaudit = array($id,'Endorsed',$action,$user,$today);
		
		$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
		
		if( $resultaudit === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		

		if($result){
		return true;
		}
		else{
		return false;
		}	
    }

	
	/*Create a Copy of DPP*/
    public function createCopy($conn,$ORG_CODE,$MRC_CODE,$reference_no,$year,$version,$cost_center,$user)
    {			
	$newVersion = 0;
	$today = date("m/d/Y H:i");
		//SELECT FROM R5_DPP_VERSION
		$sql = "SELECT MAX(version) FROM dbo.R5_DPP_VERSION WHERE ORG_CODE = ? AND MRC_CODE = ? AND reference_no = ? AND year_budget = ?";
		$params = array($ORG_CODE,$MRC_CODE,$reference_no,$year);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$newVersion = $val[''] + 1;
			
			$sql2 = "INSERT INTO dbo.R5_DPP_VERSION(ORG_CODE,MRC_CODE,reference_no,version,year_budget,status,cost_center,createdAt,updatedAt,createdBy,updatedBy) 
			VALUES(?,?,?,?,?,?,?,?,?,?,?)";
			$params2 = array($ORG_CODE,$MRC_CODE,$reference_no,$newVersion,$year,"Unfinish",$cost_center,$today,$today,$user,$user);		
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			if( $result2 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			//Costbase
			$sql3 = "SELECT rowid FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE reference_no = ? AND version = ?";
			$params3 = array($reference_no,$version);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			if( $result3 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($val3 = sqlsrv_fetch_array($result3, SQLSRV_FETCH_ASSOC)){
			$rowid = $val3['rowid'];
			echo $rowid;
				$sql4 = "INSERT INTO dbo.R5_EAM_DPP_COSTBASE_BRIDGE(reference_no,rowid,version) 
				VALUES(?,?,?)";
				$params4 = array($reference_no,$rowid,$newVersion);		
				$result4 = sqlsrv_query($conn,$sql4,$params4);
				if( $result4 === false) {
				die( print_r( sqlsrv_errors(), true) );
				}
			}
			
			//Itembase
			$sql5 = "SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?";
			$params5 = array($reference_no,$version);
			$result5 = sqlsrv_query($conn,$sql5,$params5);
			if( $result5 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($val5 = sqlsrv_fetch_array($result5, SQLSRV_FETCH_ASSOC)){
			$rowid5 = $val5['rowid'];
			echo $rowid5;
				$sql6 = "INSERT INTO dbo.R5_EAM_DPP_ITEMBASE_BRIDGE(reference_no,rowid,version) 
				VALUES(?,?,?)";
				$params6 = array($reference_no,$rowid5,$newVersion);		
				$result6 = sqlsrv_query($conn,$sql6,$params6);
				if( $result6 === false) {
				die( print_r( sqlsrv_errors(), true) );
				}
			}
			
			if (@$result3 || @$result4){
			echo "passed";
			}else{
			echo "failed";
			}
		}
		
		return $newVersion;
	}
	
	
	/*Create a Copy of Project APP*/
    public function createProjectCopy($conn,$ORG_CODE,$reference_no,$year,$version,$user)
    {			
		//SELECT FROM R5_Project_VERSION
		$today = date("m/d/Y H:i");
		$sql = "SELECT MAX(version) FROM dbo.R5_PROJECT_VERSION WHERE ORG_CODE = ? AND reference_no = ? AND year_budget = ?";
		$params = array($ORG_CODE,$reference_no,$year);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$newVersion = $val[''] + 1;
			
			$sql2 = "INSERT INTO dbo.R5_PROJECT_VERSION(ORG_CODE,reference_no,version,year_budget,status,createdAt,updatedAt,createdBy,updatedBy) 
			VALUES(?,?,?,?,?,?,?,?,?)";
			$params2 = array($ORG_CODE,$reference_no,$newVersion,$year,"Unfinish",$today,$today,$user,$user);		
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			if( $result2 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			$sql3 = "SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = ? AND version = ?";
			$params3 = array($reference_no,$version);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			if( $result3 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($val3 = sqlsrv_fetch_array($result3, SQLSRV_FETCH_ASSOC)){
			$rowid = $val3['rowid'];
			echo $rowid;
				$sql4 = "INSERT INTO dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE(reference_no,rowid,version) 
				VALUES(?,?,?)";
				$params4 = array($reference_no,$rowid,$newVersion);		
				$result4 = sqlsrv_query($conn,$sql4,$params4);
				if( $result4 === false) {
				die( print_r( sqlsrv_errors(), true) );
				}
			}
			
			
			if (@$result3){
			echo "passed";
			}else{
			echo "failed";
			}
		}
		
		return $newVersion;
	}
	
	
/*Check Item previous version*/
    public function getLineVersionInfo($conn,$table,$id)
    {			
		//SELECT FROM R5_DPP_VERSION
		$sql = "SELECT rejectFlag FROM $table WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$rejectFlag = $val['rejectFlag'];
		}
			
		return $rejectFlag;
		
	}
	
	
		/*Back DEP from Table*/
    public function backApp($conn,$reference_no,$version)
    {
		//SELECT FROM R5_EAM_DPP_ITEMBASE_LINES
		$sqlItem = "SELECT id FROM dbo.R5_EAM_DPP_ITEMBASE_LINES WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$paramsItem = array($reference_no,$version);
		$resultItem = sqlsrv_query($conn,$sqlItem,$paramsItem);
		if( $resultItem === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		//SELECT FROM R5_EAM_DPP_COSTBASE_LINES
		$sqlCost = "SELECT id FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$paramsCost = array($reference_no,$version);
		$resultCost = sqlsrv_query($conn,$sqlCost,$paramsCost);
		if( $resultCost=== false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		//DELETE FROM R5_EAM_DPP_ITEMBASE_LINES
		$sql2 = "DELETE FROM dbo.R5_EAM_DPP_ITEMBASE_LINES WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params2 = array($reference_no,$version);
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
			
		//DELETE FROM R5_EAM_DPP_COSTBASE_LINES
		$sql3 = "DELETE FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE saveFlag = 0 AND id IN (SELECT rowid FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params3 = array($reference_no,$version);
		$result3 = sqlsrv_query($conn,$sql3,$params3);
		if( $result3 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
				
		while($valItem = sqlsrv_fetch_array($resultItem, SQLSRV_FETCH_ASSOC)){
			//DELETE FROM BUDGET MONTH
			$idItem = $valItem['id'];
			$sql4 = "DELETE FROM dbo.R5_REF_ITEMBASE_BUDGET_MONTH WHERE id = ?";
			$params4 = array($idItem);
			$result4 = sqlsrv_query($conn,$sql4,$params4);
			if( $result4 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			//DELETE FROM R5_EAM_DPP_ITEMBASE_BRIDGE
			$sqlItemBridge = "DELETE FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ? AND rowid = ?";
			$paramsItemBridge = array($reference_no,$version,$idItem);
			$resultItemBridge = sqlsrv_query($conn,$sqlItemBridge,$paramsItemBridge);
			if( $resultItemBridge === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		}
		
		while($valCost = sqlsrv_fetch_array($resultCost, SQLSRV_FETCH_ASSOC)){
			//DELETE FROM BUDGET MONTH
			$idCost = $valCost['id'];
			$sql5 = "DELETE FROM dbo.R5_REF_COSTBASE_BUDGET_MONTH WHERE id = ?";
			$params5 = array($idCost);
			$result5 = sqlsrv_query($conn,$sql5,$params5);
			if( $result5 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			//DELETE FROM R5_EAM_DPP_COSTBASE_BRIDGE
			$sqlCostBridge = "DELETE FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE reference_no = ? AND version = ? AND rowid = ?";
			$paramsCostBridge = array($reference_no,$version,$idCost);
			$resultCostBridge = sqlsrv_query($conn,$sqlCostBridge,$paramsCostBridge);
			if( $resultCostBridge === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		}
		
		if($result4 || $result5){
		return true;
		}
		else{
		return false;
		}
			
	}
	
		/*Back DEP from Table*/
    public function backProjApp($conn,$ORG_CODE,$year_budget)
    {
		$sql = "SELECT project_code
		FROM dbo.R5_EAM_APP_PROJECT_RECORDS
		WHERE saveFlag = 0 AND ORG_CODE = ? AND year_budget = ?";
		
		$params = array($ORG_CODE,$year_budget);
		$result = sqlsrv_query($conn,$sql,$params);

		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$project_code = $val['project_code'];
			//DELETE FROM R5_EAM_APP_PROJECT_RECORDS
			$sql2 = "DELETE FROM dbo.R5_EAM_APP_PROJECT_RECORDS WHERE saveFlag = 0 AND project_code = ?";
			$params2 = array($project_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			if( $result2 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			//DELETE FROM BUDGET MONTH
			$sql3 = "DELETE FROM dbo.R5_EAM_APP_PROJECT_MILESTONE WHERE project_code = ?";
			$params3 = array($project_code);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			if( $result3 === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		}
		if($result2 && $result3){
		return true;
		}
		else{
		return false;
		}	
    }
	
	/*Read Details from Table*/
    public function readColumn($conn,$table)
    {
		$sql = "SELECT COLUMN_NAME
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = ?";
		
		$params = array($table);
		$result = sqlsrv_query($conn,$sql,$params);

		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		$column = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$data = $val['COLUMN_NAME'];
			array_push($column,$data);
		}
       return $column;
    }	
/*-------END OF TABLE FIELD LIST-------*/

/*-------START OF DYNAMIC LISTVIEW-------*/
	/*Read Details from Table*/
    public function listTable($conn,$table,$column,$filter)
    {	
		$field = "";
		$cnt = count($column);
		$ctr = 1;
		
		foreach($column as $fieldName){
		
		if ($fieldName == "foreign_cost" || $fieldName == "unit_cost" || $fieldName == "total_cost" ||$fieldName == "Foreign_Cost" || $fieldName == "Unit_Cost" || $fieldName == "Total_Cost" || $fieldName == "budget_amount" || $fieldName == "Budgeted_Cost"|| $fieldName == "Cost" || $fieldName == "Amount" || $fieldName == "Submitted_Amount"|| $fieldName == "amount"){
		$fieldName = "convert(varchar,cast($fieldName as money),1) AS $fieldName";
		}
			if($ctr == $cnt){
				$field .= $fieldName;
			}else{
				$field .= $fieldName.",";
			}
			$ctr++;
		}

		//Filter records if filter is not null
		if($filter!=""){
			$sql = "SELECT $field FROM $table WHERE $filter";
		}else{
			$sql = "SELECT $field FROM $table";
		}
		
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
		return($resultArr);
    }	
	 public function GetReceiverId($conn,$id)
    {	
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$createdBy = $val['createdBy'];
		}
		
		return($createdBy);
    }
	
	/*Read Details from Table*/
    public function listTableDeadline($conn,$table,$column,$filter)
    {	
		$field = "";
		$cnt = count($column);
		$ctr = 1;
		
		foreach($column as $fieldName){
		
		if ($fieldName == "Foreign_Cost" || $fieldName == "Unit_Cost" || $fieldName == "Total_Cost" || $fieldName == "budget_amount" || $fieldName == "Budgeted_Cost"|| $fieldName == "Cost" || $fieldName == "Amount" || $fieldName == "Submitted_Amount"|| $fieldName == "amount"){
		$fieldName = "convert(varchar,cast($fieldName as money),1) AS $fieldName";
		}
			if($ctr == $cnt){
				$field .= $fieldName;
			}else{
				$field .= $fieldName.",";
			}
			$ctr++;
		}

		//Filter records if filter is not null
		if($filter!=""){
			$sql = "SELECT $field FROM $table WHERE $filter ORDER BY budget_year DESC";
		}else{
			$sql = "SELECT $field FROM $table ORDER BY budget_year DESC";
		}
		
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
		return($resultArr);
    }
	
	
	/*-------START OF DYNAMIC LISTVIEW (GL CODE ONLY)-------*/
	/*Read Details from Table*/
    public function listTable2($conn,$table,$column,$filter)
    {	
		$field = "";
		$cnt = count($column);
		$ctr = 1;
		
		foreach($column as $fieldName){
		
		if ($fieldName == "Foreign_Cost" || $fieldName == "Unit_Cost" || $fieldName == "Total_Cost" || $fieldName == "budget_amount" || $fieldName == "Budgeted_Cost"|| $fieldName == "Cost" || $fieldName == "Amount" || $fieldName == "Submitted_Amount"|| $fieldName == "amount"){
		$fieldName = "convert(varchar,cast($fieldName as money),1) AS $fieldName";
		}
			if($ctr == $cnt){
				$field .= $fieldName;
			}else{
				$field .= $fieldName.",";
			}
			$ctr++;
		}

		//Filter records if filter is not null
		if($filter!=""){
			$sql = "SELECT $field FROM $table WHERE $filter BY CMD_CODE ASC";
		}else{
			$sql = "SELECT $field FROM $table ORDER BY CMD_CODE ASC";
		}
		
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
		return($resultArr);
    }
	
/*-------END OF DYNAMIC LISTVIEW-------*/


	/*-------START OF DYNAMIC LISTVIEW (GL CODE ONLY)-------*/
	/*Read Details from Table*/
    public function listTable3($conn,$table,$column,$filter,$sort)
    {	
		$field = "";
		$cnt = count($column);
		$ctr = 1;
		
		foreach($column as $fieldName){
		
		if ($fieldName == "Foreign_Cost" || $fieldName == "Unit_Cost" || $fieldName == "Total_Cost" || $fieldName == "budget_amount" || $fieldName == "Budgeted_Cost"|| $fieldName == "Cost" || $fieldName == "Amount" || $fieldName == "Submitted_Amount"|| $fieldName == "amount"){
		$fieldName = "convert(varchar,cast($fieldName as money),1) AS $fieldName";
		}
			if($ctr == $cnt){
				$field .= $fieldName;
			}else{
				$field .= $fieldName.",";
			}
			$ctr++;
		}

		//Filter records if filter is not null
		if($filter!=""){
			$sql = "SELECT $field FROM $table WHERE $filter $sort";
		}else{
			$sql = "SELECT $field FROM $table $sort";
		}
		
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
		return($resultArr);
    }

/*-------Generic CRUD-------*/
	/* Insert Record to Table*/
	    public function insertRecord($conn,$data,$table)
    {			
		
		// Extract value of array $data and store to variables
		$fields = "";
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$fields .= $key.", ";
				$values .= "'$value', ";
			}else{
				$fields .= $key;
				$values .= "'$value'";
			}			
			$ctr++;
		}

		$sql = "INSERT INTO $table($fields) 
		VALUES($values)"; 
				
		$result = sqlsrv_query($conn,$sql);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}
	
	
/*SPECIAL HANDLING FOR INSERT MILESTONE*/
	/* Insert Record to Table*/
	    public function insertAddmoreMilestone($conn,$data,$table,$data2,$table2)
    {			
		
		// Extract value of array $data and store to variables
		$fields = "";
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$fields .= $key.", ";
				$values .= "'$value', ";
			}else{
				$fields .= $key;
				$values .= "'$value'";
			}			
			$ctr++;
		}

		$sql = "INSERT INTO $table($fields) 
		VALUES($values)"; 
				
		$result = sqlsrv_query($conn,$sql);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}




		$sqlmilestone = "SELECT TOP 1 milestoneID from dbo.R5_EAM_APP_PROJECTBASE_MILESTONE ORDER BY milestoneID desc";
		
		$resultmilestone = sqlsrv_query($conn,$sqlmilestone);

		if( $resultmilestone === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
        while($valmilestone = sqlsrv_fetch_array($resultmilestone, SQLSRV_FETCH_ASSOC)) {
			$milestoneID = $valmilestone['milestoneID'];
		}

		echo $milestoneID;
		
		
		$data2['to_code'] = $milestoneID;
		print_r($data2);


		// Extract value of array $data and store to variables
		$fields2 = "";
		$values2 = "";
		$cnt2 = count($data2);
		$ctr2 = 1;
		
		foreach ($data2 as $key => $value2){
			if ($cnt2 != $ctr2){
				$fields2 .= $key.", ";
				$values2 .= "'$value2', ";
			}else{
				$fields2 .= $key;
				$values2 .= "'$value2'";
			}			
			$ctr2++;
		}

		$sql2 = "INSERT INTO $table2($fields2) 
		VALUES($values2)"; 
				
		$result2 = sqlsrv_query($conn,$sql2);
		
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}		
					
					
		if($result){
		return $milestoneID;
		}
		else{
		return false;
		}	
	}
	
	/* Insert Record to Table*/
	    public function insertAddmoreMilestone2($conn,$data,$table)
    {			
		
		// Extract value of array $data and store to variables
		$fields = "";
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$fields .= $key.", ";
				$values .= "'$value', ";
			}else{
				$fields .= $key;
				$values .= "'$value'";
			}			
			$ctr++;
		}

		$sql = "INSERT INTO $table($fields) 
		VALUES($values)"; 
				
		$result = sqlsrv_query($conn,$sql);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}




		$sqlmilestone = "SELECT TOP 1 milestoneID from dbo.R5_EAM_APP_PROJECTBASE_MILESTONE ORDER BY milestoneID desc";
		
		$resultmilestone = sqlsrv_query($conn,$sqlmilestone);

		if( $resultmilestone === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
        while($valmilestone = sqlsrv_fetch_array($resultmilestone, SQLSRV_FETCH_ASSOC)) {
			$milestoneID = $valmilestone['milestoneID'];
		}

		echo $milestoneID;
					
		if($result){
		return $milestoneID;
		}
		else{
		return false;
		}	
	}
/*Read Details from Table*/
    public function readRecord($conn,$data,$table,$identifier,$code)
    {
	
		// Extract value of array $data and store to variables
		$fields = "";
		$cnt = count($data);
		$ctr = 1;
		
		foreach ($data as $column){
		
		if ($column == "PAR_BASEPRICE" || $column == "Budget_Amount" || $column == "unit_cost" || $column == "foreign_cost" || $column == "total_cost" || $column == "amount" || $column == "PAR_LASTPRICE"  || $column == "PAR_LASTPRICE"){
		$column = "convert(varchar,cast($column as money),1) AS $column";
		}
		
			if ($cnt != $ctr){
				$fields .= $column.", ";
			}else{
				$fields .= $column;
			}			
			$ctr++;
		}
		$sql = "SELECT $fields FROM $table WHERE $identifier= ?";
		
		$params = array($code);
		
		$result = sqlsrv_query($conn,$sql,$params);

		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		$dataArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		return json_encode($val); 
			//foreach ($data as $column){
			//$dataArr[$column] = $val[$column];
			//}
		}
        //return $dataArr;
    }	

	
	/*Read Details from Table with additional condition*/
    public function readRecord2($conn,$data,$table,$cnd)
    {
	
		// Extract value of array $data and store to variables
		$fields = "";
		$cnt = count($data);
		$ctr = 1;
		
		/*foreach ($data as $column){
			if ($cnt != $ctr){
				$fields .= $column.", ";
			}else{
				$fields .= $column;
			}			
			$ctr++;
		}*/
		
		
		foreach ($data as $column){
		
		if ($column == "PAR_BASEPRICE" || $column == "Budget_Amount" || $column == "unit_cost"  || $column == "foreign_cost" || $column == "total_cost"   || $column == "amount"  || $column == "Amount"  || $column == "PAR_LASTPRICE"){
		$column = "convert(varchar,cast($column as money),1) AS $column";
		}
		
			if ($cnt != $ctr){
				$fields .= $column.", ";
			}else{
				$fields .= $column;
			}			
			$ctr++;
		}
		
		
		$sql = "SELECT $fields FROM $table $cnd";
		
		$result = sqlsrv_query($conn,$sql);

		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		$dataArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		return json_encode($val); 
		}
    }


	/*Read Details from Table*/
    public function readRecord3($conn,$table,$cnd)
    {		
		$sql = "SELECT ORG_CODE,MRC_CODE,year_budget,version,reference_no,rowid,code,PAR_DESC,convert(varchar,cast(available as money),1) AS available,status FROM $table WHERE $cnd";
		
		$result = sqlsrv_query($conn,$sql);

		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$data['ORG_CODE'] = $val['ORG_CODE'];
			$data['MRC_CODE'] = $val['MRC_CODE'];
			$data['version'] = $val['version'];
			$data['reference_no'] = $val['reference_no'];
			$data['rowid'] = $val['rowid'];
			$data['code'] = $val['code'];
			$data['PAR_DESC'] = $val['PAR_DESC'];
			$data['available'] = $val['available'];
			$data['status'] = $val['status'];
			array_push($resultArr,$data);
		}
        return $resultArr;
    }

	/*Read Details from Table*/
    public function readRecord4($conn,$table,$cnd)
    {		
		$sql = "SELECT ORG_CODE,MRC_CODE,year_budget,version,reference_no,rowid,description,convert(varchar,cast(available as money),1) AS available,status FROM $table WHERE $cnd";
		
		$result = sqlsrv_query($conn,$sql);

		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$data['ORG_CODE'] = $val['ORG_CODE'];
			$data['MRC_CODE'] = $val['MRC_CODE'];
			$data['version'] = $val['version'];
			$data['reference_no'] = $val['reference_no'];
			$data['rowid'] = $val['rowid'];
			$data['description'] = $val['description'];
			$data['available'] = $val['available'];
			$data['status'] = $val['status'];
			array_push($resultArr,$data);
		}
        return $resultArr;
    }		
/* Update Record to Table*/
	    public function updateRecord($conn,$data,$table,$field,$tableID)
    {			
		// Extract value of array $data and store to variables
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$values .= $key." = ";
				$values .= "'$value', ";
			}else{
				$values .= $key." = ";
				$values .= "'$value'";
			}			
			$ctr++;
		}
		
		$sql = "UPDATE $table SET 
        $values		
		WHERE $field=?";
		$params = array($tableID);	
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}	
	
/* Update Record to Table*/
	    public function updateRecord2($conn,$data,$table,$condition)
    {			
		// Extract value of array $data and store to variables
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$values .= $key." = ";
				$values .= "'$value', ";
			}else{
				$values .= $key." = ";
				$values .= "'$value'";
			}			
			$ctr++;
		}
		
		$sql = "UPDATE $table SET 
        $values		
		WHERE $condition";
		$result = sqlsrv_query($conn,$sql);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}

/* Update Record to Table*/
	    public function updateRecord3($conn,$data,$table,$cnd)
    {			
		// Extract value of array $data and store to variables
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$values .= $key." = ";
				$values .= "'$value', ";
			}else{
				$values .= $key." = ";
				$values .= "'$value'";
			}			
			$ctr++;
		}
		
		$sql = "UPDATE $table SET 
        $values		
		$cnd";
		$params = array($tableID);	
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}		
	
/* Update Record to Table*/
	    public function updateRecordStatus($conn,$data,$reference_no,$version)
    {			
		// Extract value of array $data and store to variables
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$values .= $key." = ";
				$values .= "'$value', ";
			}else{
				$values .= $key." = ";
				$values .= "'$value'";
			}			
			$ctr++;
		}
		
		$sql = "UPDATE dbo.R5_EAM_DPP_ITEMBASE_LINES SET 
        $values		
		WHERE id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params = array($reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		
		$sql2 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
        $values		
		WHERE id IN (SELECT rowid FROM dbo.R5_EAM_DPP_ITEMBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params2 = array($reference_no,$version);
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}

	
/* Update Record to Table*/
	    public function updateRecordStatus2($conn,$data,$reference_no,$version)
    {			
		// Extract value of array $data and store to variables
		$values = "";
		$cnt = count($data);
		$ctr = 1;
		foreach ($data as $key => $value){
			if ($cnt != $ctr){
				$values .= $key." = ";
				$values .= "'$value', ";
			}else{
				$values .= $key." = ";
				$values .= "'$value'";
			}			
			$ctr++;
		}
		
		$sql = "UPDATE dbo.R5_EAM_APP_PROJECTBASE_LINES SET 
        $values		
		WHERE id IN (SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params = array($reference_no,$version);
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		
		$sql2 = "UPDATE dbo.R5_EAM_APP_PROJECTBASE_MILESTONE SET 
        $values		
		WHERE id IN (SELECT rowid FROM dbo.R5_EAM_DPP_PROJECTBASE_BRIDGE WHERE reference_no = ? AND version = ?)";
		$params2 = array($reference_no,$version);
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}		
	
	/* DELETE Record to Table*/
	    public function deleteRecord($conn,$table,$referenceField,$value)
    {			
		$sql = "DELETE FROM $table WHERE $referenceField = ?";
		$params = array($value);	
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}	
	
	/* DELETE Record to Table*/
	    public function deleteRecord2($conn,$table,$table2,$data)
    {

		$id = $data['id'];
		$reference_no = $data['reference_no'];
		$version = $data['version'];
			
		$sql = "DELETE FROM $table WHERE id = ?";
		$params = array($id);	
		$result = sqlsrv_query($conn,$sql,$params);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		
		
		$sql2 = "DELETE FROM $table2 WHERE rowid = ? AND version = ? AND reference_no = ?";
		$params2 = array($id,$version,$reference_no);	
		$result2 = sqlsrv_query($conn,$sql2,$params2);
		
		if( $result2 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

		
		if($result){
		return true;
		}
		else{
		return false;
		}	
	}	
	
/*-------Generic CRUD-------*/
/*-------REF TABLE CRUD-------*/

	/* Record List from Table*/
	
	//Items
    public function listRefRecord($conn,$table)
    {
		$field1 = "";
		$field2 = "";
		if ($table =="R5PARTS" || $table =="R5_VIEW_PARTS_UOM_INFO" || $table =="R5_VIEW_SERVICE_UOM_INFO"){
			$field1 = "PAR_CODE";
			$field2 = "PAR_DESC";
		}else if ($table == "R5MRCS"){
			$field1 = "MRC_CODE";
			$field2 = "MRC_DESC";
		}else if ($table == "R5_VIEW_COMMODITIES" || $table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
			$field3 = "category";
		}else if ($table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
		}else if ($table == "R5PROJECTS"){
			$field1 = "PRJ_CODE";
			$field2 = "PRJ_DESC";
		}else if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
			$field4 = "milestoneID";
			$field5 = "milestone";
			$field6 = "budget_amount";
		}else if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
		}else{
			$field1 = "ORG_CODE";
			$field2 = "ORG_DESC";
		}
		$sql = "SELECT * FROM $table ORDER BY $field1,$field2 ASC";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$code = $val[$field1];
			$desc = $val[$field2];
			
			$data['code'] = $code;
			$data['desc'] = $desc;
			
			if ($table == "R5_VIEW_COMMODITIES"){
				$category = $val[$field3];
				$data['category'] = $category;
			}
			
			if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
				$project_code = $val[$field3];
				$milestoneID = $val[$field4];
				$milestone = $val[$field5];
				$budget_amount = $val[$field6];
				$data['project_code'] = $project_code;
				$data['milestoneID'] = $milestoneID;
				$data['milestone'] = $milestone;
				$data['budget_amount'] = $budget_amount;
			}
			if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
				$project_code = $val[$field3];
				$data['project_code'] = $project_code;
			}
	
			array_push($resultArr,$data);
		}
        return $resultArr;
    }	
	
	
	public function listRefRecordActive($conn,$table,$cnd)
    {
		$field1 = "";
		$field2 = "";
		if ($table =="R5PARTS" || $table =="R5_VIEW_PARTS_UOM_INFO" || $table =="R5_VIEW_SERVICE_UOM_INFO"){
			$field1 = "PAR_CODE";
			$field2 = "PAR_DESC";
		}else if ($table == "R5MRCS"){
			$field1 = "MRC_CODE";
			$field2 = "MRC_DESC";
		}else if ($table == "R5_VIEW_COMMODITIES" || $table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
			$field3 = "category";
		}else if ($table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
		}else if ($table == "R5PROJECTS"){
			$field1 = "PRJ_CODE";
			$field2 = "PRJ_DESC";
		}else if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
			$field4 = "milestoneID";
			$field5 = "milestone";
			$field6 = "budget_amount";
		}else if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
		}else{
			$field1 = "ORG_CODE";
			$field2 = "ORG_DESC";
		}
		$sql = "SELECT * FROM $table $cnd ORDER BY $field1,$field2 ASC";
		
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$code = $val[$field1];
			$desc = $val[$field2];
			
			$data['code'] = $code;
			$data['desc'] = $desc;
			
			if ($table == "R5_VIEW_COMMODITIES"){
				$category = $val[$field3];
				$data['category'] = $category;
			}
			
			if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
				$project_code = $val[$field3];
				$milestoneID = $val[$field4];
				$milestone = $val[$field5];
				$budget_amount = $val[$field6];
				$data['project_code'] = $project_code;
				$data['milestoneID'] = $milestoneID;
				$data['milestone'] = $milestone;
				$data['budget_amount'] = $budget_amount;
			}
			if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
				$project_code = $val[$field3];
				$data['project_code'] = $project_code;
			}
	
			array_push($resultArr,$data);
		}
        return $resultArr;
    }		
	
	
    public function listRefRecordProject2($conn,$table,$cnd)
    {
		$field1 = "";
		$field2 = "";
		if ($table =="R5PARTS" || $table =="R5_VIEW_PARTS_UOM_INFO" || $table =="R5_VIEW_SERVICE_UOM_INFO"){
			$field1 = "PAR_CODE";
			$field2 = "PAR_DESC";
		}else if ($table == "R5MRCS"){
			$field1 = "MRC_CODE";
			$field2 = "MRC_DESC";
		}else if ($table == "R5_VIEW_COMMODITIES" || $table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
			$field3 = "category";
		}else if ($table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
		}else if ($table == "R5PROJECTS"){
			$field1 = "PRJ_CODE";
			$field2 = "PRJ_DESC";
		}else if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
			$field4 = "milestoneID";
			$field5 = "milestone";
			$field6 = "budget_amount";
		}else if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
		}else{
			$field1 = "ORG_CODE";
			$field2 = "ORG_DESC";
		}
		$sql = "SELECT * FROM $table $cnd ORDER BY $field1,$field2 ASC";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$code = $val[$field1];
			$desc = $val[$field2];
			
			$data['code'] = $code;
			$data['desc'] = $desc;
			
			if ($table == "R5_VIEW_COMMODITIES"){
				$category = $val[$field3];
				$data['category'] = $category;
			}
			
			if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
				$project_code = $val[$field3];
				$milestoneID = $val[$field4];
				$milestone = $val[$field5];
				$budget_amount = $val[$field6];
				$data['project_code'] = $project_code;
				$data['milestoneID'] = $milestoneID;
				$data['milestone'] = $milestone;
				$data['budget_amount'] = $budget_amount;
			}
			if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
				$project_code = $val[$field3];
				$data['project_code'] = $project_code;
			}
	
			array_push($resultArr,$data);
		}
        return $resultArr;
    }	
	
	
	
    public function listRefRecordProject($conn,$table,$cnd)
    {
		$field1 = "";
		$field2 = "";
		if ($table =="R5PARTS" || $table =="R5_VIEW_PARTS_UOM_INFO" || $table =="R5_VIEW_SERVICE_UOM_INFO"){
			$field1 = "PAR_CODE";
			$field2 = "PAR_DESC";
		}else if ($table == "R5MRCS"){
			$field1 = "MRC_CODE";
			$field2 = "MRC_DESC";
		}else if ($table == "R5_VIEW_COMMODITIES" || $table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
			$field3 = "category";
		}else if ($table == "R5_VIEW_R5COMMODITIES"){
			$field1 = "CMD_CODE";
			$field2 = "CMD_DESC";
		}else if ($table == "R5PROJECTS"){
			$field1 = "PRJ_CODE";
			$field2 = "PRJ_DESC";
		}else if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
			$field4 = "milestoneID";
			$field5 = "milestone";
			$field6 = "available2";
		}else if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
			$field1 = "id";
			$field2 = "PRJ_DESC";
			$field3 = "PRJ_CODE";
		}else{
			$field1 = "ORG_CODE";
			$field2 = "ORG_DESC";
		}
		$sql = "SELECT * FROM $table $cnd ORDER BY $field1,$field2 ASC";
		$result = sqlsrv_query($conn,$sql);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		$data = array();
		$resultArr = array();
        while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$code = $val[$field1];
			$desc = $val[$field2];
			
			$data['code'] = $code;
			$data['desc'] = $desc;
			
			if ($table == "R5_VIEW_COMMODITIES"){
				$category = $val[$field3];
				$data['category'] = $category;
			}
			
			if ($table == "R5_VIEW_PROJECT_LINES_APPROVED"){
				$project_code = $val[$field3];
				$milestoneID = $val[$field4];
				$milestone = $val[$field5];
				$available2 = $val[$field6];
				$data['project_code'] = $project_code;
				$data['milestoneID'] = $milestoneID;
				$data['milestone'] = $milestone;
				$data['available2'] = $available2;
			}
			if ($table == "R5_VIEW_PROJECT_HEADER_APPROVED"){
				$project_code = $val[$field3];
				$data['project_code'] = $project_code;
			}
	
			array_push($resultArr,$data);
		}
        return $resultArr;
    }
	
/*-------REF TABLE CRUD-------*/ 
    public function optionValue($conn,$tbname,$tbfield)
    {
		$code = $tbfield."_CODE";
		$desc = $tbfield."_DESC";
		$table = "dbo.".$tbname;
		$str = "SELECT $code,$desc FROM $table ORDER BY $desc";
		$result = sqlsrv_query($conn,$str);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select name='".$tbfield."' id='".$code."'>";
	    $selection .= "<option value=''>-- Please select --</option>";
        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$selection .= "<option name='". $tbfield . "' value='" . $ors[$code] . "'>". $ors[$desc] . "</option>";
		}
    
		$selection .= "</select>";
		echo $selection;
        return $this;
    }
	
    public function optionValueMilestone($conn)
    {
		$code = "PBC_CODE";
		$desc = "PBC_DESC";
		$str = "SELECT $code,$desc FROM dbo.R5PROJBUDCODES ORDER BY $desc";
		$result = sqlsrv_query($conn,$str);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select class='milestone' name='milestone[]' id='milestone'>";
	    $selection .= "<option value=''>-- Please select --</option>";
        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$selection .= "<option name='pbc_code' value='" . $ors[$code] . "'>". $ors[$desc] . "</option>";
		}
    
		$selection .= "</select>";
		echo $selection;
        return $this;
    }
	
    public function optionValueUpdateMilestone($conn,$milestone)
    {
		$code = "PBC_CODE";
		$desc = "PBC_DESC";
		$str = "SELECT $code,$desc FROM dbo.R5PROJBUDCODES ORDER BY $desc";
		$result = sqlsrv_query($conn,$str);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select class='milestone updatemilestone' name='updatemilestone[]' id='updatemilestone'>";
	    $selection .= "<option value=''>-- Please select --</option>";
        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			if($ors[$code] == $milestone){
				$selection .= "<option name='pbc_code' value='" . $ors[$code] . "' selected>". $ors[$desc] . "</option>";
			}else{
				$selection .= "<option name='pbc_code' value='" . $ors[$code] . "'>". $ors[$desc] . "</option>";
			}
		}
    
		$selection .= "</select>";
        return $selection;
    }
	
   public function optionValue2($conn,$tbname,$tbfield,$cnd)
    {
		$str = "SELECT $tbfield FROM $tbname $cnd ORDER BY $tbfield ";
		$result = sqlsrv_query($conn,$str);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select name='".$tbfield."' id='".$tbfield."'>";
	    $selection .= "<option value=''>-- Please select --</option>";
        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			$fieldVal = str_replace(" ","",$ors[$tbfield]);
			if ($fieldVal != ""){
			 $selection .= "<option name='". $tbfield . "' value='" . $fieldVal . "'>". $fieldVal . "</option>";
			}
		}
    
		$selection .= "</select>";
		echo $selection;
        return $this;
    }
	

   public function optionValue3($conn,$tbname,$tbfield,$cnd)
    {
		$str = "SELECT $tbfield FROM $tbname $cnd ORDER BY $tbfield ";
		$result = sqlsrv_query($conn,$str);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select name='costcenterfr' id='costcenterfr'>";
	    $selection .= "<option value=''>-- Please select --</option>";

        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {	
			$fieldVal = str_replace(" ","",$ors[$tbfield]);
		if ($fieldVal != ""){
			 $selection .= "<option name='costcenterfr' value='" . $fieldVal . "'>". $fieldVal . "</option>";
		}
		}
    
		$selection .= "</select>";
		echo $selection;
        return $this;
    }


   public function optionValue4($conn,$tbname,$tbfield,$tbfield2,$cnd)
    {
		$str = "SELECT $tbfield,$tbfield2 FROM $tbname $cnd ORDER BY $tbfield2";
		$result = sqlsrv_query($conn,$str);
		
		$tbfield = str_replace("DISTINCT","",$tbfield);
		$tbfield = str_replace("(","",$tbfield);
		$tbfield = str_replace(")","",$tbfield);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select name='".$tbfield."' id='".$tbfield."'>";
	    $selection .= "<option value=''>-- Please select --</option>";
        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {		
		$fieldVal = str_replace(" ","",$ors[$tbfield]);
		$fieldVal2 = str_replace(" "," ",$ors[$tbfield2]);
		if ($fieldVal != "" && $fieldVal2 != ""){
			  $selection .= "<option name='". $tbfield . "' value='" . $fieldVal . "'>". $fieldVal2 . "</option>";
		}
		}
    
		$selection .= "</select>";
		echo $selection;
        return $this;
    }


   public function optionValue42($conn,$tbname,$tbfield,$tbfield2,$cnd)
    {
		$str = "SELECT $tbfield,$tbfield2 FROM $tbname $cnd ORDER BY $tbfield2";
		$result = sqlsrv_query($conn,$str);
		
		$tbfield = str_replace("DISTINCT","",$tbfield);
		$tbfield = str_replace("(","",$tbfield);
		$tbfield = str_replace(")","",$tbfield);
		
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}

        //Render them in drop down box	
        $selection = "";
	    $selection .= "<select name='".$tbfield."' id='".$tbfield."'>";
	    $selection .= "<option value=''>-- Please select --</option>";
        while($ors = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {		
		$fieldVal = str_replace(" ","",$ors[$tbfield]);
		$fieldVal2 = str_replace(" "," ",$ors[$tbfield2]);
		if ($fieldVal != "" && $fieldVal2 != ""){
			  $selection .= "<option name='". $tbfield . "' value='" . $fieldVal . "'>". $fieldVal. " - " .$fieldVal2 . "</option>";
		}
		}
    
		$selection .= "</select>";
		echo $selection;
        return $this;
    }	
	/*Budget Movement*/
    public function updateBudgetMovement($conn,$id)
    {
		$amount = 0;
		$fr_code= "";
		$to_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$fr_table= $val['fr_table'];
			$fr_code= $val['fr_code'];
			$to_table= $val['to_table'];
			$to_code= $val['to_code'];
			$type= $val['type'];
		}
				
		//echo $type;	
		$fr_table = str_replace(" ","",$fr_table);
		$to_table = str_replace(" ","",$to_table);		
		
		if ($type == 'supplement'){
		$table = "";
			if ($to_table == 'IB'){
			echo "here";
				$table = "dbo.R5_EAM_DPP_ITEMBASE_LINES";
			}else{
			echo "here2";
				$table = "dbo.R5_EAM_DPP_COSTBASE_LINES";
			}
			//TO
			echo "SUPPLEMENT";
			
			$saveFlag = 0;
			$selectItem = "SELECT saveFlag FROM $table WHERE id = ?";
			$paramsItem = array($to_code);
			$resultItem = sqlsrv_query($conn,$selectItem,$paramsItem);
			if( $resultItem === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($valItem = sqlsrv_fetch_array($resultItem, SQLSRV_FETCH_ASSOC)){
				$saveFlag = $valItem['saveFlag'];
			}
		
			$sql2 = "";
			if ($saveFlag > 0){
				$sql2 = "UPDATE $table SET 
				available = ? + (SELECT available FROM $table WHERE id = ?),
				adjustments = ? + (SELECT adjustments FROM $table WHERE id = ?)
				WHERE id = ?";
				$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			}else{
				$sql2 = "UPDATE $table SET 
				adjustments = ? + (SELECT adjustments FROM $table WHERE id = ?),
				saveFlag = 1,
				available = ?
				WHERE id = ?";	
				$params2 = array($amount,$to_code,$amount,$to_code);				
			}
			
			//$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}


			//BALANCE to
			$updatebalancesql = "UPDATE dbo.R5_BUDGET_MOVEMENT SET 
			to_available_amount =(SELECT available FROM $table WHERE id = ?)
			WHERE id = ?";
			$paramsbalance = array($to_code,$id);
			$resultbalance = sqlsrv_query($conn,$updatebalancesql,$paramsbalance);
			
			if( $resultbalance === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE to	
			
			if( $result2) {
			return true;
			}else{
			return false;
			}
		}else{
		echo "REALLOCATE";
			//TO
			$table = "";
			if ($to_table == 'IB'){
				$table = "dbo.R5_EAM_DPP_ITEMBASE_LINES";
			}else{
				$table = "dbo.R5_EAM_DPP_COSTBASE_LINES";
			}
			
			$table2 = "";
			if ($fr_table == 'IB'){
				$table2 = "dbo.R5_EAM_DPP_ITEMBASE_LINES";
			}else{
				$table2 = "dbo.R5_EAM_DPP_COSTBASE_LINES";
			}
			
			$sql2 = "UPDATE $table SET 
			available = ? + (SELECT available FROM $table WHERE id = ?),
			adjustments = ? + (SELECT adjustments FROM $table WHERE id = ?),
			saveFlag = 1
			WHERE id = ?";
			$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			} 
			
			//FROM
			$sql3 = "UPDATE $table2 SET 
			available =(SELECT available FROM $table2 WHERE id = ?) - ?,
			adjustments =(SELECT adjustments FROM $table2 WHERE id = ?) - ?			
			WHERE id = ?";
			$params3 = array($fr_code,$amount,$fr_code,$amount,$fr_code);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			
			if( $result3 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}

		
			//BALANCE to
			$updatebalancesql2 = "UPDATE dbo.R5_BUDGET_MOVEMENT SET 
			to_available_amount =(SELECT available FROM $table WHERE id = ?)
			WHERE id = ?";
			$paramsbalance2 = array($to_code,$id);
			$resultbalance2 = sqlsrv_query($conn,$updatebalancesql2,$paramsbalance2);
			
			if( $resultbalance2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE to

			//BALANCE FROM
			$updatebalancesql3 = "UPDATE dbo.R5_BUDGET_MOVEMENT SET 
			fr_available_amount =(SELECT available FROM $table2 WHERE id = ?)
			WHERE id = ?";
			$paramsbalance3 = array($fr_code,$id);
			$resultbalance3 = sqlsrv_query($conn,$updatebalancesql3,$paramsbalance3);
			
			if( $resultbalance3 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE FROM				
						
			
			if( $result2 && $result3) {
			return true;
			}else{
			return false;
			}
		}
    }
	
	
	/*Budget Movement PROJECT*/
    public function updateBudgetMovementProject($conn,$id)
    {
		$amount = 0;
		$fr_code= "";
		$to_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT_PROJECT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$fr_code= $val['fr_code'];
			$to_code= $val['to_code'];
			$type= $val['type'];
		}	
		
		if ($type == 'supplement'){
		$table = "dbo.R5_EAM_APP_PROJECTBASE_MILESTONE";
			//TO
			echo "SUPPLEMENT";
			
			$saveFlag = 0;
			$selectItem = "SELECT saveFlag FROM dbo.R5_VIEW_PROJECT_LINES_APPROVED WHERE milestoneID = ?";
			$paramsItem = array($to_code);
			$resultItem = sqlsrv_query($conn,$selectItem,$paramsItem);
			if( $resultItem === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($valItem = sqlsrv_fetch_array($resultItem, SQLSRV_FETCH_ASSOC)){
				$saveFlag = $valItem['saveFlag'];
			}
		
			$sql2 = "";
			if ($saveFlag > 0){
				$sql2 = "UPDATE $table SET 
				available = ? + (SELECT available FROM $table WHERE milestoneID = ?),
				adjustments = ? + (SELECT adjustments FROM $table WHERE milestoneID = ?)
				WHERE milestoneID = ?";
				$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			}else{
				$sql2 = "UPDATE $table SET 
				adjustments = ? + (SELECT adjustments FROM $table WHERE milestoneID = ?),
				saveFlag = 1,
				available = ?
				WHERE milestoneID = ?";	
				$params2 = array($amount,$to_code,$amount,$to_code);				
			}
			
			//$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}


			//BALANCE to
			$updatebalancesql = "UPDATE dbo.R5_BUDGET_MOVEMENT_PROJECT SET 
			to_available_amount =(SELECT available FROM $table WHERE milestoneID = ?)
			WHERE id = ?";
			$paramsbalance = array($to_code,$id);
			$resultbalance = sqlsrv_query($conn,$updatebalancesql,$paramsbalance);
			
			if( $resultbalance === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE to	
			
			if( $result2) {
			return true;
			}else{
			return false;
			}
		}else{
		echo "REALLOCATE";
		echo "to = $to_code";
		echo "to = $amount";
			//TO
			$table = "dbo.R5_EAM_APP_PROJECTBASE_MILESTONE";
						
			$sql2 = "UPDATE $table SET 
			available = ? + (SELECT available FROM $table WHERE milestoneID = ?),
			adjustments = ? + (SELECT adjustments FROM $table WHERE milestoneID = ?),
			saveFlag = 1
			WHERE milestoneID = ?";
			$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			} 
			
			//FROM
			$sql3 = "UPDATE $table SET 
			available =(SELECT available FROM $table WHERE milestoneID = ?) - ?,
			adjustments =(SELECT adjustments FROM $table WHERE milestoneID = ?) - ?			
			WHERE milestoneID = ?";
			$params3 = array($fr_code,$amount,$fr_code,$amount,$fr_code);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			
			if( $result3 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}

		
			//BALANCE to
			$updatebalancesql2 = "UPDATE dbo.R5_BUDGET_MOVEMENT_PROJECT SET 
			to_available_amount =(SELECT available FROM $table WHERE milestoneID = ?)
			WHERE id = ?";
			$paramsbalance2 = array($to_code,$id);
			$resultbalance2 = sqlsrv_query($conn,$updatebalancesql2,$paramsbalance2);
			
			if( $resultbalance2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE to

			//BALANCE FROM
			$updatebalancesql3 = "UPDATE dbo.R5_BUDGET_MOVEMENT_PROJECT SET 
			fr_available_amount =(SELECT available FROM $table WHERE milestoneID = ?)
			WHERE id = ?";
			$paramsbalance3 = array($fr_code,$id);
			$resultbalance3 = sqlsrv_query($conn,$updatebalancesql3,$paramsbalance3);
			
			if( $resultbalance3 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE FROM				
						
			
			if( $result2 && $result3) {
			return true;
			}else{
			return false;
			}
		}
    }
	
	//CHECK IF SOURCE HAS ENOUGH BUDGET
	public function checkBudgetMovementBalance($conn,$id)
    {
		$amount = 0;
		$fr_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$fr_table= $val['fr_table'];
			$fr_code= $val['fr_code'];
			$type= $val['type'];
		}
	$fr_table = str_replace(" ","",$fr_table);			
		if ($type == 'reallocation'){
			
			$available = 0;
			$selectItem = "";
			if ($fr_table == "IB"){
				$selectItem = "SELECT available FROM dbo.R5_EAM_DPP_ITEMBASE_LINES WHERE id = ?";
			}else{
				$selectItem = "SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?";
			}
			
			$paramsItem = array($fr_code);
			$resultItem = sqlsrv_query($conn,$selectItem,$paramsItem);
			if( $resultItem === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($valItem = sqlsrv_fetch_array($resultItem, SQLSRV_FETCH_ASSOC)){
				$available = $valItem['available'];
			}
			//echo $amount."--".$available;
			if ($available < $amount){
			}else{
				return true;
			}
		}else{
		return true;
		}
    }
	
	//CHECK IF SOURCE HAS ENOUGH BUDGET PROJECT
	public function checkBudgetMovementBalanceProject($conn,$id)
    {
		$amount = 0;
		$fr_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT_PROJECT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$fr_code= $val['fr_code'];
			$type= $val['type'];
		}		
		if ($type == 'reallocation'){
			
			$available = 0;

			$selectItem = "SELECT available FROM dbo.R5_EAM_APP_PROJECTBASE_MILESTONE WHERE milestoneID = ?";

			
			$paramsItem = array($fr_code);
			$resultItem = sqlsrv_query($conn,$selectItem,$paramsItem);
			if( $resultItem === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($valItem = sqlsrv_fetch_array($resultItem, SQLSRV_FETCH_ASSOC)){
				$available = $valItem['available'];
			}
			//echo $amount."--".$available;
			if ($available < $amount){
			}else{
				return true;
			}
		}else{
		return true;
		}
    }
	
	/*REJECTED BUDGET SUPPLEMENT*/
    public function updateBudgetMovementRejected($conn,$id)
    {
		$amount = 0;
		$to_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$to_table= $val['to_table'];
			$to_code= $val['to_code'];
			$type= $val['type'];
		}
	$to_table = str_replace(" ","",$to_table);				
		if ($type == 'supplement'){
			//TO

		$table = "";
		$table2 = "";
		$table3 = "";
		if ($to_table == 'IB'){
			$table = "dbo.R5_VIEW_ITEMBASE_LINES";
			$table2 = "dbo.R5_EAM_DPP_ITEMBASE_LINES";
			$table3 = "dbo.R5_EAM_DPP_ITEMBASE_BRIDGE";		
		}else{
			$table = "dbo.R5_VIEW_COSTBASE_LINES";
			$table2 = "dbo.R5_EAM_DPP_COSTBASE_LINES";
			$table3 = "dbo.R5_EAM_DPP_COSTBASE_BRIDGE";
		}
		
		$sql4 = "SELECT saveFlag,Description FROM $table WHERE id = ?";
		$params4 = array($to_code);
		$result4 = sqlsrv_query($conn,$sql4,$params4);
		if( $result4 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val4 = sqlsrv_fetch_array($result4, SQLSRV_FETCH_ASSOC)){
			$saveFlag = $val4['saveFlag'];
			$description = $val4['Description'];
		}
		
		
	    //BALANCE to
	    $updatebalancesql2 = "UPDATE dbo.R5_BUDGET_MOVEMENT SET 
	    to_description = ?
	    WHERE id = ?";
	    $paramsbalance2 = array($description,$id);
	    $resultbalance2 = sqlsrv_query($conn,$updatebalancesql2,$paramsbalance2);
	    
	    if( $resultbalance2 === false) {
	    	die( print_r( sqlsrv_errors(), true) );
	    }
			
		
		
		
			$sql2 = "DELETE FROM $table2 WHERE id = ? AND saveFlag = 0";
			$params2 = array($to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			
			
			if($saveFlag == 0){
			$sql3 = "DELETE FROM $table3 WHERE rowid = ?";
			$params3 = array($to_code);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			
				if( $result3 === false) {
					die( print_r( sqlsrv_errors(), true) );
				}
				
			}
			
			if( $result2) {
			return true;
			}else{
			return false;
			}
		}
    }
	
	/*REJECTED BUDGET SUPPLEMENT*/
    public function updateBudgetMovementRejectedCost($conn,$id)
    {
		$amount = 0;
		$to_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT_COST WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$to_code= $val['to_code'];
			$type= $val['type'];
		}
					
		if ($type == 'supplement'){
			//TO

			
		$sql4 = "SELECT saveFlag,Description FROM dbo.R5_VIEW_COSTBASE_LINES WHERE id = ?";
		$params4 = array($to_code);
		$result4 = sqlsrv_query($conn,$sql4,$params4);
		if( $result4 === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val4 = sqlsrv_fetch_array($result4, SQLSRV_FETCH_ASSOC)){
			$saveFlag = $val4['saveFlag'];
			$description = $val4['Description'];
		}
		
		echo $description;
	    //BALANCE to
	    $updatebalancesql2 = "UPDATE dbo.R5_BUDGET_MOVEMENT_COST SET 
	    to_description = ?
	    WHERE id = ?";
	    $paramsbalance2 = array($description,$id);
	    $resultbalance2 = sqlsrv_query($conn,$updatebalancesql2,$paramsbalance2);
	    
	    if( $resultbalance2 === false) {
	    	die( print_r( sqlsrv_errors(), true) );
	    }
			
		
		
		
			$sql2 = "DELETE FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ? AND saveFlag = 0";
			$params2 = array($to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			
			
			if($saveFlag == 0){
			$sql3 = "DELETE FROM dbo.R5_EAM_DPP_COSTBASE_BRIDGE WHERE rowid = ?";
			$params3 = array($to_code);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			
				if( $result3 === false) {
					die( print_r( sqlsrv_errors(), true) );
				}	
			}
			
			if( $result2) {
			return true;
			}else{
			return false;
			}
		}
    }
	
	/*REJECTED BUDGET SUPPLEMENT*/
    public function updateBudgetMovementRejectedProject($conn,$id)
    {
		$amount = 0;
		$to_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT_PROJECT WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$to_code= $val['to_code'];
			$type= $val['type'];
		}				
		if ($type == 'supplement'){
			//TO

			$table = "dbo.R5_EAM_APP_PROJECTBASE_MILESTONE";
			
			$sql2 = "DELETE FROM $table WHERE milestoneID = ? AND saveFlag = 0";
			$params2 = array($to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			
			if( $result2) {
			return true;
			}else{
			return false;
			}
		}
    }	
	
	
	
	
	
	/*Budget Movement COST*/
    public function updateBudgetMovementCost($conn,$id)
    {
		$amount = 0;
		$fr_code= "";
		$to_code= "";
		$type= "";
					
		$sql = "SELECT * FROM dbo.R5_BUDGET_MOVEMENT_COST WHERE id = ?";
		$params = array($id);
		$result = sqlsrv_query($conn,$sql,$params);
		if( $result === false) {
		die( print_r( sqlsrv_errors(), true) );
		}
		while($val = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$amount = $val['amount'];
			$fr_code= $val['fr_code'];
			$to_code= $val['to_code'];
			$type= $val['type'];
		}
				
		echo $type;		
		if ($type == 'supplement'){
			//TO
			echo "SUPPLEMENT";


			$saveFlag = 0;
			$selectCost = "SELECT saveFlag FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?";
			$paramsCost = array($to_code);
			$resultCost = sqlsrv_query($conn,$selectCost,$paramsCost);
			if( $resultCost === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			while($valCost = sqlsrv_fetch_array($resultCost, SQLSRV_FETCH_ASSOC)){
				$saveFlag = $valCost['saveFlag'];
			}
		
			$sql2 = "";
			if ($saveFlag > 0){
				$sql2 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
				available = ? + (SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?),
				adjustments = ? + (SELECT adjustments FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?)
				WHERE id = ?";
				$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			}else{
				$sql2 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
				adjustments = ? + (SELECT adjustments FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?),
				saveFlag = 1
				WHERE id = ?";	
				$params2 = array($amount,$to_code,$to_code);				
			}

			
			/*$sql2 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
			available = ? + (SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?),
			adjustments = ? + (SELECT adjustments FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?)
			WHERE id = ?";*/
			
			//$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}


			//BALANCE to
			$updatebalancesql = "UPDATE dbo.R5_BUDGET_MOVEMENT_COST SET 
			to_available_amount =(SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?)
			WHERE id = ?";
			$paramsbalance = array($to_code,$id);
			$resultbalance = sqlsrv_query($conn,$updatebalancesql,$paramsbalance);
			
			if( $resultbalance === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE to	
			
			if( $result2) {
			return true;
			}else{
			return false;
			}
		}else{
		echo "REALLOCATE";
			//TO
			$sql2 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
			available = ? + (SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?),
			adjustments = ? + (SELECT adjustments FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?),
			saveFlag = 1
			WHERE id = ?";
			$params2 = array($amount,$to_code,$amount,$to_code,$to_code);
			$result2 = sqlsrv_query($conn,$sql2,$params2);
			
			if( $result2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			} 
			
			//FROM
			$sql3 = "UPDATE dbo.R5_EAM_DPP_COSTBASE_LINES SET 
			available =(SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?) - ?,
			adjustments =(SELECT adjustments FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?) - ?			
			WHERE id = ?";
			$params3 = array($fr_code,$amount,$fr_code,$amount,$fr_code);
			$result3 = sqlsrv_query($conn,$sql3,$params3);
			
			if( $result3 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}

		
			//BALANCE to
			$updatebalancesql2 = "UPDATE dbo.R5_BUDGET_MOVEMENT_COST SET 
			to_available_amount =(SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?)
			WHERE id = ?";
			$paramsbalance2 = array($to_code,$id);
			$resultbalance2 = sqlsrv_query($conn,$updatebalancesql2,$paramsbalance2);
			
			if( $resultbalance2 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE to

			//BALANCE FROM
			$updatebalancesql3 = "UPDATE dbo.R5_BUDGET_MOVEMENT_COST SET 
			fr_available_amount =(SELECT available FROM dbo.R5_EAM_DPP_COSTBASE_LINES WHERE id = ?)
			WHERE id = ?";
			$paramsbalance3 = array($fr_code,$id);
			$resultbalance3 = sqlsrv_query($conn,$updatebalancesql3,$paramsbalance3);
			
			if( $resultbalance3 === false) {
				die( print_r( sqlsrv_errors(), true) );
			}
			//END BALANCE FROM				
						
			
			if( $result2 && $result3) {
			return true;
			}else{
			return false;
			}
		}
    }
	
	/*SEND EMAIL*/
    public function sentEmail($conn,$from,$to,$subject,$message)
    {   
	$status = '';
	$errorMsg = '';
         //check if the email address is invalid
		  $field=filter_var($to, FILTER_SANITIZE_EMAIL);
       
         //filter_var() validates the e-mail
         //address using FILTER_VALIDATE_EMAIL
         if(filter_var($field, FILTER_VALIDATE_EMAIL))
           {
           $mailcheck = TRUE;
           }
         else
           {
           $mailcheck =  FALSE;
           }
         if ($mailcheck==FALSE)
           {
			$status = 'E';
			$errorMsg = 'Invalid Email Address';
			//Insert record to R5_CUSTOM_MAILEVENTS
			$sqlaudit = "INSERT INTO dbo.R5_CUSTOM_MAILEVENTS (MAE_SUBJECT,MAE_BODY,MAE_EMAILRECIPIENT,MAE_RSTATUS,MAE_ERROR) VALUES(?,?,?,?,?)"; 
			
			$paramsaudit = array($subject,$message,$to,$status,$errorMsg);
			
			$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
			
			if( $resultaudit === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		   return "<p>&nbsp;&nbsp;You have entered an Invalid Email Address</p>";
           }
         else
           {//send email
           $email = $to ;
           $subject = $subject ;
           $message = $message ;
		   
		   $headers = "From: " . $from . "\r\n";
		   $headers .= "Reply-To: ". strip_tags($from) . "\r\n";
		   $headers .= "MIME-Version: 1.0\r\n";
		   $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			
           //mail("$to", "Subject: $subject",
           //$message, $headers );
		   

		   
		   
		   if(@mail("$to", "Subject: $subject",
           $message, $headers ))
			{
			echo "Mail Sent Successfully";
					   $status = 'S';
		   $errorMsg = 'Mail Sent Successfully';
			}else{
			echo "Mail Not Sent";
		   echo "Mail Not Sent";
		   $status = 'E';
		   $errorMsg = 'Mail Not Sent';
			}
			
			
		   } 

		//Insert record to R5_CUSTOM_MAILEVENTS
		$sqlaudit = "INSERT INTO dbo.R5_CUSTOM_MAILEVENTS (MAE_SUBJECT,MAE_BODY,MAE_EMAILRECIPIENT,MAE_RSTATUS,MAE_ERROR) VALUES(?,?,?,?,?)"; 
		
		$paramsaudit = array($subject,$message,$to,$status,$errorMsg);
		
		$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
		
		if( $resultaudit === false) {
		die( print_r( sqlsrv_errors(), true) );
		}		   
		   //error_get_last()		   
     }	
	 
	 public function sentEmailCC($conn,$from,$to,$subject,$message)
    {   
	$status = '';
	$errorMsg = '';
         //check if the email address is invalid
		  $field=filter_var($to, FILTER_SANITIZE_EMAIL);
       
         //filter_var() validates the e-mail
         //address using FILTER_VALIDATE_EMAIL
         if(filter_var($field, FILTER_VALIDATE_EMAIL))
           {
           $mailcheck = TRUE;
           }
         else
           {
           $mailcheck =  FALSE;
           }
         if ($mailcheck==FALSE)
           {
			$status = 'E';
			$errorMsg = 'Invalid Email Address';
			//Insert record to R5_CUSTOM_MAILEVENTS
			$sqlaudit = "INSERT INTO dbo.R5_CUSTOM_MAILEVENTS (MAE_SUBJECT,MAE_BODY,MAE_EMAILRECIPIENT,MAE_RSTATUS,MAE_ERROR) VALUES(?,?,?,?,?)"; 
			
			$paramsaudit = array($subject,$message,$to,$status,$errorMsg);
			
			$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
			
			if( $resultaudit === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		   return "<p>&nbsp;&nbsp;You have entered an Invalid Email Address</p>";
           }
         else
           {//send email
           $email = $to ;
		   $subject = $subject ;
           $message = $message ;
		   
		   $headers = "From: " . $from . "\r\n";
		   $headers .= "Reply-To: ". strip_tags($from) . "\r\n";
		   $headers .= "MIME-Version: 1.0\r\n";
		   $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
		   $to = $to.","."anthony.cortez@fdcutilities.com";
		   if(@mail("$to", "Subject: $subject",
           $message, $headers ))
			{
			echo "Mail Sent Successfully";
			$status = 'S';
		   $errorMsg = 'Mail Sent Successfully';
			}else{
			echo "Mail Not Sent";
		   echo "Mail Not Sent";
		   $status = 'E';
		   $errorMsg = 'Mail Not Sent';
			}
			
		   } 

		//Insert record to R5_CUSTOM_MAILEVENTS
		$sqlaudit = "INSERT INTO dbo.R5_CUSTOM_MAILEVENTS (MAE_SUBJECT,MAE_BODY,MAE_EMAILRECIPIENT,MAE_RSTATUS,MAE_ERROR) VALUES(?,?,?,?,?)"; 
		
		$paramsaudit = array($subject,$message,$to,$status,$errorMsg);
		
		$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
		
		if( $resultaudit === false) {
		die( print_r( sqlsrv_errors(), true) );
		}		   
		   //error_get_last()		   
     }
	/*SEND EMAIL2*/
    public function sentEmail2($conn,$id,$from,$to,$subject,$message)
    {   
	$status = '';
	$errorMsg = '';
         //check if the email address is invalid
		  $field=filter_var($to, FILTER_SANITIZE_EMAIL);
       
         //filter_var() validates the e-mail
         //address using FILTER_VALIDATE_EMAIL
         if(filter_var($field, FILTER_VALIDATE_EMAIL))
           {
           $mailcheck = TRUE;
           }
         else
           {
           $mailcheck =  FALSE;
           }
         if ($mailcheck==FALSE)
           {
			$status = 'E';
			$errorMsg = 'Invalid Email Address';
			//Insert record to R5_CUSTOM_MAILEVENTS
			$sqlaudit = "UPDATE 
		dbo.R5_CUSTOM_MAILEVENTS SET 
		MAE_SUBJECT = ?,
		MAE_BODY = ?,
		MAE_EMAILRECIPIENT = ?,
		MAE_RSTATUS = ?,
		MAE_ERROR = ? WHERE MAE_ID = $id"; 
		
			$paramsaudit = array($subject,$message,$to,$status,$errorMsg);
			
			$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
			
			if( $resultaudit === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
		   return "<p>&nbsp;&nbsp;You have entered an Invalid Email Address</p>";
           }
         else
           {//send email
           $email = $to ;
           $subject = $subject ;
           $message = $message ;
		   
		   $headers = "From: " . $from . "\r\n";
		   $headers .= "Reply-To: ". strip_tags($from) . "\r\n";
		   $headers .= "MIME-Version: 1.0\r\n";
		   $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			
           //mail("$to", "Subject: $subject",
           //$message, $headers );
		   

		   
		   
		   if(@mail("$to", "Subject: $subject",
           $message, $headers ))
			{
			echo "Mail Sent Successfully";
					   $status = 'S';
		   $errorMsg = 'Mail Sent Successfully';
			}else{
			echo "Mail Not Sent";
		   echo "Mail Not Sent";
		   $status = 'E';
		   $errorMsg = 'Mail Not Sent';
			}


		   
		   } 

		
		
		//Insert record to R5_CUSTOM_MAILEVENTS
		$sqlaudit = "UPDATE 
		dbo.R5_CUSTOM_MAILEVENTS SET 
		MAE_SUBJECT = ?,
		MAE_BODY = ?,
		MAE_EMAILRECIPIENT = ?,
		MAE_RSTATUS = ?,
		MAE_ERROR = ? WHERE MAE_ID = $id"; 
		
		$paramsaudit = array($subject,$message,$to,$status,$errorMsg);
		
		$resultaudit = sqlsrv_query($conn,$sqlaudit,$paramsaudit);
		
		if( $resultaudit === false) {
		die( print_r( sqlsrv_errors(), true) );
		}		   
		   //error_get_last()		   
     }
}
?>