<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-Type:application/json");
$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);
/*
foreach ($data as $key => $value) {
	if(is_object($value)){
		echo $key .'=' . $value->changeRequestInfo;
	} else {
		echo $key .'='.$value .'<br/>';
	}
}*/
//echo $value->changeRequestInfo;
//echo json_encode($data);
//echo $data['changeRequestInfo[functionNo]'];
$functionNo = $data['changeRequestInfo[functionNo]'];	//functionname = FR01'
$functionVersion = $data['changeRequestInfo[functionVersion]'];	//version V.1
$projectId = $data['projectInfo'];
$functionDescription = $data['FRHeader['.$functionNo.'][functionDesc]'];
$testCaseNo = $data['RTM[testCaseNo]'];
$testCaseversion = $data['RTM[testCaseversion]'];

require_once('database.php');
include('change_func.php');
include('change_RTM.php');
include('change_tc.php');
include('running.php');
include('random.php');
/*
	$strsql = searchCHNO($projectId);
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
	$RUNNING_CH = odbc_fetch_array($objExec);
*/
	$strsql = "SELECT * FROM m_users  ";
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
	$USER = odbc_fetch_array($objExec);
	//echo $USER['username'];
	$username = $USER['username'];
	//หา FR id
	$strsql = searchFRId($functionNo,$functionVersion,$projectId);
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
	$searchFRId = odbc_fetch_array($objExec);
	$functionId = $searchFRId['functionId'];

	//หา จำนวนของ คำขอ change เพื่อรับค่า
	$strsql = searchNumChange($functionId,$functionVersion);
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
	$zz = 1;
	$row = odbc_num_rows($objExec);
	$db[] = array();
	$db[0] = array("tableName" => "ORDER_DETAILS","columnName" => "DISCOUNT","affectedAction" => "edit");
	$db[1] = array("tableName" => "ORDER_DETAILS","columnName" => "UNIT_PRICE","affectedAction" => "edit");
	
	$rtm[] = array();
	$rtm[0] = array("changeType" => "delete","functionNo"=>"OS_FR_03","testCaseNo"=>"OS_TC_03");

		$arr = array(
		"projectInfo" => "2",
		"affectedSchema" => 
			$db
		,
		"affectedRequirement" =>array(
		"OS_FR_03"=>array(
		"functionVersion"=>"1",
			"functionData"=>array(
			"dId"=>array(
			"tableName" => "",
			"columnName" => "",
			"changeType"=> "add",
			"typeData"=> "1",
			"dataType"=>"VARCHAR"
			),
			"dDiscount"=>array(
			"tableName" => "ORDER_DETAILS",
			"columnName" => "DISCOUNT",
			"changeType"=> "edit",
			"typeData"=> "1",
			"dataType"=>""
			),
			"dUnit Price"=>array(
				"tableName" => "ORDER_DETAILS",
				"columnName" => "UNIT_PRICE",
				"changeType"=> "edit",
				"typeData"=> "1",
				"dataType"=>"DECIMAL"
			),
			"dPrice"=>array(
			"tableName" => "",
			"columnName" => "",
			"changeType"=> "delete",
			"typeData"=> "2",
			"dataType"=>"DECIMAL"
			)
	),
		)),
		"affectedTestCase" => array(
			"OS_TC_04"=> array(
				"changeType" => "add", 
				"testCaseVersion"=> "1", 
				"testCaseDesc"=> "", 
				"expectedResult"=> "Valid", 
				"testCaseDetails" =>array( 
					"dId" => array(
						"changeType"=> "add", 
						"testData"=> "MBsn5M6pg2P6Er5XEuXu"
					),
					"dPrice"=> array( 
						"changeType"=> "delete",
						"testData"=> ""
					), 
					"dDiscount"=> array(
						"changeType"=> "add", 
						"testData"=> 87 
					), 
					"dUnit Price"=>array(
						"changeType"=> "add", 
						"testData"=> "0.47"
					)
				)
			)	
		),		
		"affectedRTM" => array(
			"details" => $rtm,
			)	
		);
		//print_r($returnData);
		print_r (json_encode($arr));

		//response($returnData,$row);
	
		//echo $num_row;
//var_dump($returnData);
	//$returnData = 'HH';
	//response($returnData,$row);

//function response($order_id,$amount,$response_code,$response_desc,$retuneData){
function response($returnData,$row){
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
	echo '<pre>';
	var_dump($returnData);echo '</pre>';
		$json_response = json_encode($returnData,JSON_PRETTY_PRINT);
		//var_dump($json_response);
		echo $json_response;
		return $json_response;
		
	//$json_response = json_encode($response);
	//echo "affectedRequirement"."<br/>";
	//var_dump($json_response);
	//echo $json_response."<br/>";
	//return $json_response;
}
?>