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
	VALUES ({$projectId}, {$New_TCNO},{$New_testcaseversion},{$testcaseDescription},{$expectedResult}
    ,'$currentDateTime', '{$username}', '$currentDateTime', '{$username}','1')";
	//echo $strsql ;
	return $strsql ;
} 
?>