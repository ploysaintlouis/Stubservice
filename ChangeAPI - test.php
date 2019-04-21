<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-Type:application/json");
$data = json_decode(file_get_contents('php://input'));
//$data = json_decode(file_get_contents('php://input'),true);

//echo  $data->projectInfo;

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

$arr = array(
	"projectInfo" => "2",
	"affectedSchema" => array(
		array(
		"tableName" => "ORDER_DETAILS",
		"columnName" => "DISCOUNT",
		"affectedAction" => "edit"
		),
		array(
			"tableName" => "ORDER_DETAILS",
			"columnName" => "UNIT_PRICE",
			"affectedAction" => "edit"	
		)
	),
	"affectedRequirement" =>array(
	"OS_FR_03"=>array(
	"functionVersion"=>"1",
		"Input"=>array(
		"dId"=>array(
		"tableName" => "",
		"columnName" => "",
		"changeType"=> "add"
		),
		"dDiscount"=>array(
		"tableName" => "ORDER_DETAILS",
		"columnName" => "DISCOUNT",
		"changeType"=> "edit"
		),
		"dUnit Price"=>array(
			"tableName" => "ORDER_DETAILS",
			"columnName" => "UNIT_PRICE",
			"changeType"=> "edit"
		),		
		"dPrice"=>array(
		"tableName" => "",
		"columnName" => "",
		"changeType"=> "delete"
		)
		))),
	"AffectedTestCase" => array(
		"OS_TC_03" => array(
			"changeType" => "delete", 
			"testCaseVersion" => "1", 
			"testCaseDescription" => "", 
			"ExpectedResult" => "Valid"
		),
		"OS_TC_04"=> array(
			"changeType" => "add", 
			"testCaseVersion"=> "1", 
			"testCaseDescription"=> "", 
			"ExpectedResult"=> "Valid", 
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
	)
	);
	echo json_encode($arr);

	//response($arr);
		//echo $num_row;
	//$returnData = 'HH';
	//response($returnData,$row);

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

	//var_dump($returnData);
	//var_dump($returnData);
	//$response = $returnData;
	$response['projectInfo'] = $returnData['projectInfo'];

	//$json_arr = json_encode($returnData,JSON_PRETTY_PRINT);
	//var_dump($json_arr);
	//echo json_encode($returnData);

	//$json_arr = json_encode($response,JSON_PRETTY_PRINT);
	//var_dump($json_arr);
	echo json_encode($response);	
	//$json_response = json_encode($response);
	//echo "AffectedRequirement"."<br/>";
	//var_dump($json_response);
	//echo $json_response."<br/>";
	//return $json_response;
}

?>