<?php
include('database.php');
function searchRTM($functionId,$functionversion,$projectId) {

 	$strsql = " SELECT a.testCaseId,a.testCaseversion,b.testCaseNo,b.expectedResult,b.testCaseDescription
        FROM M_RTM_VERSION a, M_TESTCASE_HEADER b
        WHERE a.testCaseversion = b.testcaseVersion
        AND a.testCaseId = b.testCaseId
        AND a.functionversion = '$functionversion'
        AND a.functionId = '$functionId' 
        AND a.activeflag = '1'
        AND b.activeflag = '1'
        AND a.projectId = '$projectId' ";

	//echo $strsql;
	return $strsql;
} 

?>