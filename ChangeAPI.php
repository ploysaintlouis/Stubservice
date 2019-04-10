<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-Type:application/json");
$data = json_decode(file_get_contents('php://input'), true);

//echo json_encode($data);
//echo $data['changeRequestInfo[functionNo]'];
$functionNo = $data['changeRequestInfo[functionNo]'];	//functionname = FR01'
$functionVersion = $data['changeRequestInfo[functionVersion]'];	//version V.1
$projectId = $data['projectInfo'];

require_once('database.php');
include('change_func.php');
include('random.php');

	$strsql = "SELECT * FROM m_users  ";
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
	$USER = odbc_fetch_array($objExec);

	//หา FR id
	$strsql = searchFRId($functionNo,$functionVersion,$projectId);
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
	$searchFRId = odbc_fetch_array($objExec);
	$functionId = $searchFRId['functionId'];

	//หา จำนวนของ คำขอ change เพื่อรับค่า
	$strsql = searchNumChange($functionId,$functionVersion);
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
	$num_row = 0;
	$row = odbc_num_rows($objExec);
	//echo $row;
	//echo $ResultFR['ChangeType'];
	while($num_row < $row)
	{
		while($ResultNumChange = odbc_fetch_array($objExec)){
			//echo "HH";
			if($ResultNumChange['changeType'] == 'A'){
				$createUser = $ResultNumChange['createUser'];
				$strsql = searchFRImpact($functionId,$functionVersion,$projectId);
				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
				$x = 1;
				while($ResultFR = odbc_fetch_array($objExec)){
					$typeData[$x]= $ResultFR['typeData'];
					$dataId[$x]= $ResultFR['dataId'];
					$dataName[$x]= $ResultFR['dataName'];
					$schemaVersionId[$x]= $ResultFR['schemaVersionId'];
					$refTableName[$x]= $ResultFR['refTableName'];
					$refColumnName[$x]= $ResultFR['refColumnName'];
					$dataType[$x]= $ResultFR['dataType'];
					$dataLength[$x]= $ResultFR['dataLength'];
					$decimalPoint[$x]= $ResultFR['decimalPoint'];
					$constraintPrimaryKey[$x]= $ResultFR['constraintPrimaryKey'];
					$constraintUnique[$x]= $ResultFR['constraintUnique'];
					$constraintDefault[$x]= $ResultFR['constraintDefault'];
					$constraintNull[$x]= $ResultFR['constraintNull'];
					$constraintMinValue[$x]= $ResultFR['constraintMinValue'];
					$constraintMaxValue[$x]= $ResultFR['constraintMaxValue'];

					$functionNo = ;
					$strsql = InsertNewFRImpact($ResultFR,$createUser,$functionNo);

/*
					echo $x;
					echo $dataId[1];
					echo $dataName[1];
					echo $schemaVersionId[1];
					echo $refTableName[1];
					echo $refColumnName[1];
					echo $dataType[1]."<br/>";
*/
					//echo $x ;
					$x++;
				}
			}
		}
		//echo $num_row;
		$num_row++;
	}

	$returnData = 'HH';
	response($returnData);

//function response($order_id,$amount,$response_code,$response_desc,$retuneData){
function response($returnData){
	/*$response['order_id'] = $order_id;
	$response['amount'] = $amount;
	$response['response_code'] = $response_code;
	$response['response_desc'] = $response_desc;
	$response['order_id'] = "1";
	$response['amount'] = "2";
	$response['response_code'] = "3";
	//TESTCASE
	$response['testCaseNo'] = $returnData['testCaseNo'];

		//HEADER
		$response['ChangeType'] = $returnData['ChangeType'];
		$response['testcaseVersion'] = $returnData['testcaseVersion'];
		$response['testCaseDescription'] = $returnData['testCaseDescription'];
		$response['expectedResult'] = $returnData['expectedResult'];

		//DETAIL
		$response['ChangeType'] = $returnData['ChangeType'];
		$response['testcaseVersion'] = $returnData['testcaseVersion'];
		$response['testCaseDescription'] = $returnData['testCaseDescription'];
		$response['expectedResult'] = $returnData['expectedResult'];		

		$response['testData'] = $returnData['testData'];	
		$response['refdataName'] = $returnData['refdataName'];	*/
	
	//echo $returnData['testCaseNo'];
	//echo $returnData['ChangeType'];
	//echo $returnData['testcaseVersion'];	
	$response = $returnData;
	$json_response = json_encode($response);
	echo "AffectedTestCase"."<br/>";
	echo $json_response."<br/>";
}
?>