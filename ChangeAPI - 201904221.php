<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-Type:application/json");
$data = json_decode(file_get_contents('php://input'), true);
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
	$num_row = 1;
	$row = odbc_num_rows($objExec);

	if (null!=$row)
	{
		while(($ResultNumChange = odbc_fetch_array($objExec)) and ($num_row <= $row)){
			//echo $ResultNumChange['typeData'];
			//echo $num_row ;
			if($ResultNumChange['CType'] == 'A'){
				//echo 'A';
				$createUser = $ResultNumChange['createUser'];

				if ($ResultNumChange['typeData'] == 1)
				{
					$x[$num_row]['typeData'] = 'Input';
				}else{
					$x[$num_row]['typeData'] = 'Output';
				}	
				$typeData = $x[$num_row]['typeData'];
				$functionNo_arr = rtrim($functionNo);
				$dataName =rtrim($ResultNumChange['dataName']);

				//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				//$returnData['ProjectInfo'] = $projectId;
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

				//Test Case
				//insert ลง temp เก็บการเปลี่ยนแปลงที่เกิด impact 
				/*$strsql1 = InsertFR_IMPACT_HEADER($returnData,$projectId);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);*/

			}else if($ResultNumChange['CType'] == 'B'){	//รายการ change = delete
				//echo "C";
				if ($num_row!=1){ 

					if ($ResultNumChange['typeData'] == 1)
					{
						$x[$num_row]['typeData'] = 'Input';
					}else{
						$x[$num_row]['typeData'] = 'Output';
					}	
					$typeData1 = $x[$num_row]['typeData'];

					$functionNo_arr = rtrim($functionNo);
					$dataName = rtrim($ResultNumChange['dataName']);

					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

				}else
				{//delete เป็นรายการแรก
					$strsql1  = searchFRMAXFuncNo();
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
					$Max_FRNO  = odbc_fetch_array($objExec1);
					//echo $Max_FRNO['Max_FRNO'];
					//echo substr($Max_FRNO['Max_FRNO'],6,7)+1 ;
					$New_FRNO = substr($Max_FRNO['Max_FRNO'],0,7).(substr($Max_FRNO['Max_FRNO'],7,7)+1);
					$New_functionversion = '1';	

					$strsql1 = searchFR_NEW($New_FRNO,$functionVersion,$projectId);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					$New_ResultFR = odbc_fetch_array($objExec1);
					$New_functionId = $New_ResultFR['functionId'];
					
					if ($ResultNumChange['typeData'] == 1)
					{
						$x[$num_row]['typeData'] = 'Input';
					}else{
						$x[$num_row]['typeData'] = 'Output';
					}	
					$typeData = $x[$num_row]['typeData'];
					$functionNo_arr = rtrim($functionNo);
					$dataName =rtrim($ResultNumChange['dataName']);

					//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
					$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);
	
/*
					//Insert New TC_HEADER
					$strsql1 = InsertNewTC_HEADER($testCaseDescription,1,$New_TCNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
					//UPDATE วันที่ TC_HEADER เก่า
					$strsql1 = UpdateTC_HEADER($testcaseVersion,$testCaseNo,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
*/

				}
					//หา Impact จากการ delete กับ FR อื่นๆ
					$strsql1 = searchFRImpact($ResultNumChange,$functionId,$functionVersion,$projectId);
					$objExec1 = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");	
					$row_FR = odbc_num_rows($objExec1);
					if ($row_FR != 0){ //มี impact กับ FR อื่น
						//UPDATE วันที่ FR DETAIL เก่า ที่impact จากการ delete นี้
						$Delete_FRDETAIL = odbc_fetch_array($objExec1);

						$strsql1  = searchFRMAXFuncNo();
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
						$Max_FRNO  = odbc_fetch_array($objExec1);
						//echo $Max_FRNO['Max_FRNO'];
						//echo substr($Max_FRNO['Max_FRNO'],6,7)+1 ;
						$New_FRNO = substr($Max_FRNO['Max_FRNO'],0,7).(substr($Max_FRNO['Max_FRNO'],7,7)+1);
						$New_functionversion = '1';
						$delete_FRVer = $Delete_FRDETAIL['functionVersion'];
						$Delete_FRNo = $Delete_FRDETAIL['functionNo'];
						$Delete_FRId = $Delete_FRDETAIL['functionId'];

						$strsql1 = searchFR_NEW($New_FRNO,$functionVersion,$projectId);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						$New_ResultFR = odbc_fetch_array($objExec1);
						$New_functionId = $New_ResultFR['functionId'];

						if ($ResultNumChange['typeData'] == 1)
						{
							$x[$num_row]['typeData'] = 'Input';
						}else{
							$x[$num_row]['typeData'] = 'Output';
						}	
						$typeData = $x[$num_row]['typeData'];
						$functionNo_arr = rtrim($functionNo);
						$dataName =rtrim($ResultNumChange['dataName']);
	
						//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

				}	
			}
			else if($ResultNumChange['CType'] == 'C'){	//รายการ change =edit
			//echo 'B';
			//UPDATE FR เฉพาะ field
				if ($num_row!=1){
					//หา Impact จากการ edit กับ FR อื่นๆ
					$strsql1 = searchFRImpact($ResultNumChange,$functionId,$functionVersion,$projectId);
					$objExec1 = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");	
					$row_FR = odbc_num_rows($objExec1);

					if ($row_FR != 0){	//มี impact กับ FR อื่น
						//UPDATE วันที่ FR DETAIL เก่า ที่impact จากการ edit นี้
						$Result_FRDETAIL = odbc_fetch_array($objExec1);	

						if ($ResultNumChange['typeData'] == 1)
						{
							$x[$num_row]['typeData'] = 'Input';
						}else{
							$x[$num_row]['typeData'] = 'Output';
						}	
						$typeData = $x[$num_row]['typeData'];
						$functionNo_arr = rtrim($Result_FRDETAIL['functionNo']);
						$dataName =rtrim($ResultNumChange['dataName']);
						$FROther_Ver = Result_FRDETAIL['functionVersion'];
						$FROther_Id = Result_FRDETAIL['functionId'];

						//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($Result_FRDETAIL['functionVersion']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

					}else{
						//echo $ResultNumChange['typeData'];
						if ($ResultNumChange['typeData'] == 1)
						{
							$x[$num_row]['typeData'] = 'Input';
						}else{
							$x[$num_row]['typeData'] = 'Output';
						}	
						$typeData = $x[$num_row]['typeData'];
//echo $typeData;
						$functionNo_arr = rtrim($functionNo);
						$dataName = rtrim($ResultNumChange['dataName']);

						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
						$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

						}				
					//echo $returnData['$dataName'][$num_row];
					//echo $num_row;
				}else{
					
					if ($num_row == '1'){ // ทำเฉพาะรายการ EDIT รายการแรก รายการต่อไป update เฉพาะ fleid
						$strsql1  = searchFRMAXFuncVer($functionNo);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
						$Max_FRVer  = odbc_fetch_array($objExec1);
						//echo $Max_FRVer['Max_FRVer']+1;
						//echo substr($Max_FRVer['Max_FRVer'],6,7)+'1' ;
						$Max_FRVer = $Max_FRVer['Max_FRVer']+1;
					}

				//echo $ResultNumChange['typeData'];
				if ($ResultNumChange['typeData'] == 1)
				{
					$x[$num_row]['typeData'] = 'Input';
				}else{
					$x[$num_row]['typeData'] = 'Output';
				}	
				$typeData = $x[$num_row]['typeData'];
				$functionNo_arr = rtrim($functionNo);
				$dataName =rtrim($ResultNumChange['dataName']);

				//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
				$returnData['ProjectInfo']['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

				}
				//echo $num_row;	
				$num_row++;
				//var_dump($returnData);
			}
			//echo $num_row;
		}
		//2.TEST CASE
		$strsql = searchFRId($functionNo,$functionVersion,$projectId);
		$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
		$searchFRId = odbc_fetch_array($objExec);
		$functionId = $searchFRId['functionId'];

		$sql = searchRTM($functionId,$functionVersion,$projectId);
		$obj  = odbc_exec($objConnect, $sql) or die ("Error Execute [".$sql."]");
		$rowrtm = odbc_num_rows($obj);		
//echo $rowrtm;
		//หา จำนวนของ คำขอ change เพื่อรับค่า
		$yy=1;
		while(($yy<=$rowrtm) and ($TC_Result  = odbc_fetch_array($obj)) ){
			$testcaseVersion = $TC_Result['testCaseversion'];
			$testCaseNo = $TC_Result['testCaseNo'];
			$TC_expectedResult = $TC_Result['expectedResult'];
			$testCaseId = $TC_Result['testCaseId'];
			$testCaseDescription = $TC_Result['testCaseDescription']; 

			$strsql = searchNumChange($functionId,$functionVersion);
			$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");	
			$row_tc = odbc_num_rows($objExec);		
//echo $New_functionId;
			$xx=1;
			while(($ResultChange = odbc_fetch_array($objExec)) and ($xx <= $row)){
				$ResultChange['newDataType'] = rtrim($ResultChange['newDataType']);
//echo $ResultChange['CType'];
				if($ResultChange['CType'] == 'A'){
					$strsql1  = searchFRMAXTCNo();
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
					$Max_TCNO  = odbc_fetch_array($objExec1);
					//echo $Max_FRNO['Max_TCNO'];
					//echo substr($Max_FRNO['Max_TCNO'],6,7)+1 ;
					$New_TCNO = substr($Max_TCNO['Max_TCNO'],0,7).(substr($Max_TCNO['Max_TCNO'],7,7)+1);

					if(($ResultChange['newDataType'] == 'int') || ($ResultChange['newDataType'] == 'INT') ){
						if (isset($ResultChange['NewMaxValue']) or ($ResultChange['NewMaxValue'] == null )){
							$ResultChange['NewMaxValue'] = '100';
						}
						if (isset($ResultChange['NewMinValue']) or ($ResultChange['NewMinValue'] == null )){
							$ResultChange['NewMaxValue'] = '0';
						}
						$new_testdata = randInt(0,$ResultChange['NewMaxValue']);
					}
					if (($ResultChange['newDataType'] == 'decimal') || ($ResultChange['newDataType'] == 'DECIMAL')
					|| ($ResultChange['newDataType'] == 'float') || ($ResultChange['newDataType'] == 'FLOAT')
					|| ($ResultChange['newDataType'] == 'double') || ($ResultChange['newDataType'] == 'DOUBLE')){
						$new_testdata = '';
						while(($new_testdata <= $ResultChange['NewMaxValue']) and($new_testdata >= $ResultChange['NewMixValue'])){
							$first_num = random_round($ResultChange['NewDataLength']);
							$round = random_round($ResultChange['NewScaleLength']);
							$new_testdata = $first_num.".".$round;
							echo $new_testdata;
						}
					}
					if(($ResultChange['newDataType'] == 'date') || ($ResultChange['newDataType'] == 'DATE') ){
						$new_testdata = '';
					}

					if (($ResultChange['newDataType'] == 'char') || ($ResultChange['newDataType'] == 'CHAR')
					|| ($ResultChange['newDataType'] == 'varchar') || ($ResultChange['newDataType'] == 'VARCHAR')){
						$new_testdata = randChar($ResultChange['newDataLength']);
					}

					$testcaseNo_arr = rtrim($testCaseNo);
					$dataName =rtrim($ResultChange['dataName']);
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['changeType'] = 'delete';
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseVersion'] = rtrim($testcaseVersion);
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDescription'] = rtrim($testCaseDescription);
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['ExpectedResult'] = rtrim($TC_expectedResult);

					//echo $new_testdata;
					$testcaseNo_arr = rtrim($New_TCNO);
					$dataName =rtrim($ResultChange['dataName']);
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['changeType'] = 'add';
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseVersion'] = '1';
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDescription'] = rtrim($testCaseDescription);
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['ExpectedResult'] = rtrim($TC_expectedResult);
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['changeType'] = 'add';
					$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['testData'] = $new_testdata;
				}else if($ResultChange['CType'] == 'B'){	//รายการ change = delete
					if ($xx!=1){
						$dataName =rtrim($ResultChange['dataName']);
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['changeType'] = 'delete';
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['testData'] = '';		
					}else{
						if ($num_row == '1'){
							$testcaseNo_arr = rtrim($testCaseNo);
							$dataName =rtrim($ResultChange['dataName']);
							$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['changeType'] = 'delete';
							$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseVersion'] = rtrim($testcaseVersion);
							$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDescription'] = rtrim($testCaseDescription);
							$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['ExpectedResult'] = rtrim($TC_expectedResult);
						}
					}
				}else if($ResultChange['CType'] == 'C'){	//รายการ change =edit
					//echo "C";
					$new_testdata = '';
					if (isset($ResultChange['newMaxValue']) or ($ResultChange['newMaxValue'] == null )){
						$ResultChange['newMaxValue'] = '100';
					}
					if (isset($ResultChange['newMinValue']) or ($ResultChange['newMinValue'] == null )){
						$ResultChange['newMinValue'] = '0';
					}
					$new_testdata = randInt(0,$ResultChange['newMaxValue']);
						
					if (($ResultChange['newDataType'] == 'decimal') || ($ResultChange['newDataType'] == 'DECIMAL')
					|| ($ResultChange['newDataType'] == 'float') || ($ResultChange['newDataType'] == 'FLOAT')
					|| ($ResultChange['newDataType'] == 'double') || ($ResultChange['newDataType'] == 'DOUBLE')){
						if(($new_testdata == null) || ($new_testdata<$ResultChange['newMaxValue']) || ($new_testdata > $ResultChange['newMinValue'])){
							$first_num = random_round($ResultChange['newDataLength']);
							$round = random_round($ResultChange['newScaleLength']);
							$new_testdata = $first_num.".".$round;
							//echo $new_testdata;
						}
					}	
					if(($ResultChange['newDataType'] == 'date') || ($ResultChange['newDataType'] == 'DATE') ){
						$new_testdata = '';
					}
	
					if (($ResultChange['newDataType'] == 'char') || ($ResultChange['newDataType'] == 'CHAR')
					|| ($ResultChange['newDataType'] == 'varchar') || ($ResultChange['newDataType'] == 'VARCHAR')){
						$new_testdata = randChar($ResultChange['newDataLength']);
					}

					if ($xx!=1){
						//echo $new_testdata;
						$dataName =rtrim($ResultChange['dataName']);
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['changeType'] = 'add';
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['testData'] = $new_testdata;		
					}else{
						$testcaseNo_arr = rtrim($testCaseNo);
						$dataName =rtrim($ResultChange['dataName']);
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['changeType'] = 'edit';
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseVersion'] = rtrim($testcaseVersion);
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDescription'] = rtrim($testCaseDescription);
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['ExpectedResult'] = rtrim($TC_expectedResult);
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['changeType'] = 'edit';
						$returnData['ProjectInfo']['AffectedTestCase'][$testcaseNo_arr]['testCaseDetails'][$dataName]['testData'] = $new_testdata;		
					}
				}
			$xx++;
			}
			$yy++;
		}
		response($returnData,$row);
	}
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
	//echo "AffectedRequirement"."<br/>";
	//var_dump($json_response);
	//echo $json_response."<br/>";
	//return $json_response;
}
?>