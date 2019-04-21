<?php

$funno = 'OS_FR_03';
$x[1] = '101';
$x[2] = '102';
$db[] = array();
$db[0] = array("tableName" => "ORDER_DETAILS","columnName" => "DISCOUNT","affectedAction" => "edit");
$db[1] = array("tableName" => "ORDER_DETAILS","columnName" => "UNIT_PRICE","affectedAction" => "edit");

	$arr = array(
	"projectInfo" => "2",
	"affectedSchema" => 
		$db
	,
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
	$json = file_get_contents('data.json');
	$data = json_decode($json);
	echo '<pre>';
	var_dump($data);
	print_r($data);
	echo '</pre>';
	$json_arr = json_encode($data);
	//var_dump($json_arr);
	echo json_encode($data,JSON_PRETTY_PRINT);

	writeJsonFile($arr);

	function writeJsonFile($inputData){
		try{
			$datetime = date('YmdHis',strtotime('+ 5 hours'));
			echo $datetime;
			$encodedString = json_encode($inputData);
			$inputFileName = "log/change/responseDataJson_25_".$datetime.".txt";
			file_put_contents($inputFileName, $encodedString);
			$destination = "../ThesisProject/log/change/responseDataJson_25_".$datetime.".txt";
			rename($inputFileName, $destination);
		}catch(Exception $e){
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

?>

