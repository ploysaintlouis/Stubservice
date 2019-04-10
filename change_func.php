<?php
include('database.php');
 //Search FR No
function searchFRId($functionNo,$functionVersion,$projectId) {
	$strsql = "SELECT distinct functionId FROM M_FN_REQ_DETAIL
		where functionVersion = '$functionVersion' 
		and activeflag = '1' 
		and functionNo = '$functionNo'
		and projectId = '$projectId' 
			";
			//echo $strsql ;
		return $strsql ;
 } 

 function searchNumChange($functionId,$functionVersion) {
	$strsql = "SELECT createUser,case changeType WHEN 'add' THEN 'A' WHEN 'edit' THEN 'B' WHEN 'delete' THEN 'C' END AS changeType
	   FROM T_TEMP_CHANGE_LIST
		where functionVersion = '$functionVersion' 
		and functionId = '$functionId'
		and confirmflag = '1' 
		order by changeType
			";
		//	echo $strsql ;
		return $strsql ;
 } 
//หา DETAIL ของ FR ที่ IMPACT เฉพาะ FR นั้น
 function searchFRImpact($functionId,$functionVersion,$projectId) {
	$strsql = "SELECT *
	   FROM M_FN_REQ_DETAIL
		where functionVersion = '$functionVersion' 
		and functionId = '$functionId'
		and activeflag = '1' 
		and projectId = '$projectId' 
			";
		//	echo $strsql ;
		return $strsql ;
 } 
 						$strsql = " select max(testCaseNo) AS Max_TCNO from M_TESTCASE_HEADER ";
						$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
						$Max_TCNO  = odbc_fetch_array($objExec);
 function InsertNewFRImpact($param,$createUser,$functionNo) {
	$currentDateTime = date('Y-m-d H:i:s');
	$strsql = "INSERT INTO M_FN_REQ_HEADER (functionNo, functionDescription, projectId, createDate, createUser, updateDate, updateUser,functionversion,activeflag) 
	VALUES ('$functionNo', '{$param->functionDescription}', {$param->projectId}, '$currentDateTime', '{$createUser}', '$currentDateTime', '{$createUser}','1','1')";
	//	echo $strsql ;
	return $strsql ;
 } 
?>