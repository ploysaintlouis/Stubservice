<?php
include('database.php');
function searchFRMAXTCNo() {

 	$strsql = " SELECT max(testCaseNo) AS Max_TCNO 
	 			FROM M_TESTCASE_HEADER ";

	//echo $strsql;
	return $strsql;
} 
function InsertNewTC_HEADER($testcaseDescription,$New_testcaseversion,$New_TCNO,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');
	$strsql = "INSERT INTO M_TESTCASE_HEADER (projectId, testCaseNo,testcaseVersion,testcaseDescription,expectedResult,
    createDate, createUser, updateDate, updateUser,activeflag) 
	VALUES ('{$projectId}', '{$New_TCNO}','{$New_testcaseversion}','{$testcaseDescription}','Valid'
    ,'$currentDateTime', '{$username}', '$currentDateTime', '{$username}','1')";
	//echo $strsql ;
	return $strsql ;
} 
//update activeflag = '0' TC เก่า
function UpdateTC_HEADER($testcaseVersion,$testcaseNo,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "UPDATE M_TESTCASE_HEADER
			set updateDate = '$currentDateTime',
			updateUser = '$username',
			activeFlag = '0'	
			WHERE testcaseVersion = '$testcaseVersion' 
			ANd testcaseNo = '$testcaseNo'
			and activeflag = '1' 
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 }  
//หา testcaseId ของ FR ใหม่
function searchFR_NEW($New_TCNO,$testcaseVersion,$projectId) {
	$strsql = "SELECT testcaseId,testcaseVersion
	   FROM M_TESTCASE_HEADER 
		where testcaseVersion = '$testcaseVersion' 
		ANd testcaseNo = '$New_TCNO'
		and activeflag = '1' 
		and projectid = '$projectId' 
			";
		//echo $strsql ;
		return $strsql ;
 } 
 function InsertTC_DETAIL($testCaseNo,$testcaseVersion,$New_testcaseId,$New_testcaseversion,$New_FRNO,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "INSERT INTO M_FN_REQ_DETAIL 
	SELECT '$projectId','$New_functionId','$New_FRNO','$New_functionversion',b.typeData,b.dataName,
	b.schemaVersionId,b.refTableName,b.refColumnName,b.dataType,b.dataLength,b.decimalPoint,b.constraintPrimaryKey,
	b.constraintUnique,b.constraintDefault,b.constraintNull,b.constraintMinValue,b.constraintMaxValue,'$currentDateTime',
	NULL,'1','$currentDateTime','$username','$currentDateTime','$username'
	FROM M_FN_REQ_DETAIL b
			WHERE b.functionVersion = '$functionVersion' 
			AND b.functionNo = '$functionNo'
			AND b.activeflag = '1' 
			AND b.projectid = '$projectId' 
	";
	//echo $strsql;
	return $strsql ;
} 
?>