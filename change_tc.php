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
function searchTC_NEW($New_TCNO,$testcaseVersion,$projectId) {
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
 function InsertTC_DETAIL($testCaseNo,$testcaseVersion,$New_testcaseId,$New_testcaseversion,$New_TCNO,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "INSERT INTO M_TESTCASE_DETAIL 
	SELECT '$projectId','$New_testcaseId','$New_TCNO','$New_testcaseversion',typeData,refdataId,refdataName,
	testData,'$currentDateTime',NULL,'1','$currentDateTime','$username','$currentDateTime','$username'
	FROM M_TESTCASE_DETAIL
			WHERE testcaseversion = '$testcaseVersion' 
			AND testCaseNo = '$testCaseNo'
			AND activeflag = '1' 
			AND projectid = '$projectId' 
	";
	//echo $strsql;
	return $strsql ;
} 
function InsertNewTC_DETAIL($new_testdata,$ResultNumChange,$New_testcaseId,$New_testcaseVersion,$New_TCNO,$projectId,$username){
	$currentDateTime = date('Y-m-d H:i:s');
		
	$strsql = "INSERT INTO M_TESTCASE_DETAIL (testCaseId, typeData, refdataId, refdataName, testData, 
	effectiveStartDate,effectiveEndDate, activeFlag, createDate, createUser, updateDate, updateUser,
	projectId,testCaseNo,testcaseVersion) 
	VALUES ('{$New_testcaseId}','$ResultNumChange[typeData]', '{$ResultNumChange['refdataId']}', '$ResultNumChange[dataName]', '{$new_testdata}',
	'{$currentDateTime}',NULL, '1', '{$currentDateTime}', '$username', '{$currentDateTime}', '$username',
	'{$projectId}','{$New_TCNO}', '{$New_testcaseVersion}')";
	//echo $strsql;
	return $strsql ;
}
//update activeflag = '0' TC เก่า
function UpdateTC_DETAIL($testcaseVersion,$testcaseNo,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "UPDATE M_TESTCASE_DETAIL
			set updateDate = '$currentDateTime',
			updateUser = '$username',
			effectiveEndDate = '$currentDateTime',
			activeFlag = '0'	
			WHERE testcaseVersion = '$testcaseVersion' 
			ANd testcaseNo = '$testcaseNo'
			and activeflag = '1' 
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 } 
 function DeleteTCField_DETAIL($refdataId,$ResultNumChange,$New_functionId,$functionVersion,$functionNo,$projectId,$username) {
					
	$strsql = "DELETE FROM M_TESTCASE_DETAIL
			WHERE testcaseNo = '$functionNo' 
			AND testcaseVersion = '$functionVersion'
			and activeflag = '1' 
			AND refdataName = '$ResultNumChange[dataName]'
			AND refdataId = '$refdataId'
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 } 
 function UpdateTCField_DETAIL($refdataId,$new_testdata,$ResultNumChange,$testcaseVersion,$testcaseNo,$projectId,$username) {
	//echo $ResultNumChange['dataName'];
	$strsql = "UPDATE M_TESTCASE_DETAIL
			testData = '$new_testdata'
			WHERE testcaseVersion = '$testcaseVersion' 
			AND testcaseNo = '$testcaseNo'
			and activeflag = '1' 
			AND refdataName = '$ResultNumChange[dataName]'
			AND refdataId = '$refdataId'
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 }  				
 function SearchTC_DETAIL($ResultNumChange,$testcaseVersion,$testCaseNo,$projectId){
	//echo $ResultNumChange['dataName'];
	$strsql = "SELECT * FROM M_TESTCASE_DETAIL
			WHERE testcaseVersion = '$testcaseVersion' 
			AND testcaseNo = '$testcaseNo'
			and activeflag = '1' 
			AND refdataName != '$ResultNumChange[dataName]'
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 }  
?>