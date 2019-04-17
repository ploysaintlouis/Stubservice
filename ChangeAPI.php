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

				$strsql1  = searchFRMAXFuncNo();
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
				$Max_FRNO  = odbc_fetch_array($objExec1);
				//echo $Max_FRNO['Max_FRNO'];
				//echo substr($Max_FRNO['Max_FRNO'],6,7)+1 ;
				$New_FRNO = substr($Max_FRNO['Max_FRNO'],0,7).(substr($Max_FRNO['Max_FRNO'],7,7)+1);
				$New_functionversion = '1';

				//Insert New FR_HEADER
				$strsql1 = InsertNewFR_HEADER($functionDescription,$New_functionversion,$New_FRNO,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);
				//UPDATE วันที่ FR เก่า
				$strsql1 = UpdateFR_HEADER($functionVersion,$functionNo,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");

				$strsql1 = searchFR_NEW($New_FRNO,$functionVersion,$projectId);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				$New_ResultFR = odbc_fetch_array($objExec1);
				$New_functionId = $New_ResultFR['functionId'];

				//Insert New FR_DETAIL
				$strsql1 = InsertFR_DETAIL($functionNo,$functionVersion,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);
				//INSERT FR ใหม่ ด้วย filed ใหม่ที่ Add
				$strsql1 = InsertNewFR_DETAIL($ResultNumChange,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);
				//UPDATE วันที่ FR เก่า
				$strsql1 = UpdateFR_DETAIL($functionVersion,$functionNo,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);

				//######TEST CASE#########
				$strsql1  = searchFRMAXTCNo();
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
				$Max_TCNO  = odbc_fetch_array($objExec1);
				//echo $Max_FRNO['Max_TCNO'];
				//echo substr($Max_FRNO['Max_TCNO'],6,7)+1 ;
				$New_TCNO = substr($Max_TCNO['Max_TCNO'],0,7).(substr($Max_TCNO['Max_TCNO'],7,7)+1);

				//Insert New TC_HEADER
				$strsql1 = InsertNewTC_HEADER($functionDescription,$New_functionversion,$New_FRNO,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);
				//UPDATE วันที่ TC เก่า
				$strsql1 = UpdateTC_HEADER($functionVersion,$functionNo,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");

				if ($ResultNumChange['$typeData'] == 1)
				{
					$x[$num_row]['typeData'] = 'Input';
				}else{
					$x[$num_row]['typeData'] = 'Output';
				}	
				$typeData = $x[$num_row]['typeData'];
				$functionNo_arr = rtrim($functionNo);
				$dataName =rtrim($ResultNumChange['dataName']);

				//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
				$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
				$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

				//Test Case
				//insert ลง temp เก็บการเปลี่ยนแปลงที่เกิด impact 
				/*$strsql1 = InsertFR_IMPACT_HEADER($returnData,$projectId);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);*/

			}else if($ResultNumChange['CType'] == 'B'){	//รายการ change = delete
				//echo "C";
				if (isset($New_functionId)){
					$strsql1 = DeleteFRField_DETAIL($ResultNumChange,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
					if ($ResultNumChange['$typeData'] == 1)
					{
						$x[$num_row]['typeData'] = 'Input';
					}else{
						$x[$num_row]['typeData'] = 'Output';
					}	
					$typeData1 = $x[$num_row]['typeData'];

					$functionNo_arr = rtrim($functionNo);
					$dataName = rtrim($ResultNumChange['dataName']);

					$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
					$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
					$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);

				}else
				{//delete เป็นรายการแรก
					$strsql1  = searchFRMAXFuncNo();
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
					$Max_FRNO  = odbc_fetch_array($objExec1);
					//echo $Max_FRNO['Max_FRNO'];
					//echo substr($Max_FRNO['Max_FRNO'],6,7)+1 ;
					$New_FRNO = substr($Max_FRNO['Max_FRNO'],0,7).(substr($Max_FRNO['Max_FRNO'],7,7)+1);
					$New_functionversion = '1';	

					//Insert New FR_HEADER
					$strsql1 = InsertNewFR_HEADER($functionDescription,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
					//UPDATE วันที่ FR เก่า
					$strsql1 = UpdateFR_HEADER($functionVersion,$functionNo,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);

					$strsql1 = searchFR_NEW($New_FRNO,$functionVersion,$projectId);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					$New_ResultFR = odbc_fetch_array($objExec1);
					$New_functionId = $New_ResultFR['functionId'];
			
					//Insert New FR_DETAIL
					$strsql1 = InsertFR_DETAIL($functionNo,$functionVersion,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
					//Delete FR DETAIL ด้วย filed ใหม่ที่ delete
					$strsql1 = DeleteFRField_DETAIL($ResultNumChange,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
					//UPDATE วันที่ FR เก่า
					$strsql1 = UpdateFR_DETAIL($functionVersion,$functionNo,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
					
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
					$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
					$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
					$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
					$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);
	
				}
					//หา Impact จากการ delete กับ FR อื่นๆ
					$strsql1 = searchFRImpact($ResultNumChange,$functionId,$functionVersion,$projectId);
					$objExec1 = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");	
					$row_FR = odbc_num_rows($objExec1);
					if ($row_FR != 0){
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

						//Insert New FR_HEADER
						$strsql1 = InsertNewFR_HEADER($functionDescription,$New_functionversion,$New_FRNO,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
						//UPDATE วันที่ FR เก่า
						$strsql1 = UpdateFR_HEADER($delete_FRVer,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);	

						$strsql1 = searchFR_NEW($New_FRNO,$functionVersion,$projectId);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						$New_ResultFR = odbc_fetch_array($objExec1);
						$New_functionId = $New_ResultFR['functionId'];
		
						//Insert New FR_DETAIL
						$strsql1 = InsertFR_DETAIL($functionNo,$functionVersion,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
						//Delete FR DETAIL ด้วย filed ใหม่ที่ delete
						$strsql1 = DeleteFRField_DETAIL($ResultNumChange,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
						//UPDATE วันที่ FR เก่า activeflag ='0'
						$strsql1 = UpdateFR_DETAIL($functionVersion,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);

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
						$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
						$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
						$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
						$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);
							
				}	
					//echo $returnData['$dataName'][$num_row];
						//echo $num_row;
			}
			else if($ResultNumChange['CType'] == 'C'){	//รายการ change =edit
			//echo 'B';
			//UPDATE FR เฉพาะ field
				if (isset($New_functionId)){
					$strsql1 = UpdateFRField_DETAIL($ResultNumChange,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);

					//หา Impact จากการ edit กับ FR อื่นๆ
					$strsql1 = searchFRImpact($ResultNumChange,$functionId,$functionVersion,$projectId);
					$objExec1 = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");	
					$row_FR = odbc_num_rows($objExec1);

					if ($row_FR != 0){	//มี impact กับ FR อื่น
						//UPDATE วันที่ FR DETAIL เก่า ที่impact จากการ edit นี้
						$Result_FRDETAIL = odbc_fetch_array($objExec1);
						$strsql1 = UpdateFRField_DETAIL($ResultNumChange,$Result_FRDETAIL['functionVersion'],$Result_FRDETAIL['functionNo'],$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);		

						//UPDATE วันที่ FR HEADER เก่าที่impact จากการ edit นี้
						$strsql1 = UpdateFR_HEADER($Result_FRDETAIL['functionVersion'],$Result_FRDETAIL['functionNo'],$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);

						if ($ResultNumChange['typeData'] == 1)
						{
							$x[$num_row]['typeData'] = 'Input';
						}else{
							$x[$num_row]['typeData'] = 'Output';
						}	
						$typeData = $x[$num_row]['typeData'];
						$functionNo_arr = rtrim($Result_FRDETAIL['functionNo']);
						$dataName =rtrim($ResultNumChange['dataName']);
	
						//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
						$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($Result_FRDETAIL['functionVersion']);
						$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
						$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
						$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);
								
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

							$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
							$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
							$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);
		
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
						
						//Insert New FR_HEADER
						$strsql1 = InsertNewFR_HEADER($functionDescription,$Max_FRVer,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
						//UPDATE วันที่ FR เก่า
						$strsql1 = UpdateFR_HEADER($functionVersion,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);

						//Insert New FR_DETAIL
						$strsql1 = InsertFR_DETAIL($functionNo,$functionVersion,$functionId,$Max_FRVer,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
					}
				//UPDATE วันที่ FR เก่า
				$strsql1 = UpdateFRField_DETAIL($ResultNumChange,$Max_FRVer,$functionNo,$projectId,$username);
				$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
				odbc_free_result($objExec1);
				echo $ResultNumChange['typeData'];
				if ($ResultNumChange['typeData'] == 1)
				{
					$x[$num_row]['typeData'] = 'Input';
				}else{
					$x[$num_row]['typeData'] = 'Output';
				}	
				$typeData = $x[$num_row]['typeData'];
				$functionNo_arr = rtrim($Result_FRDETAIL['functionNo']);
				$dataName =rtrim($ResultNumChange['dataName']);

				//$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($functionVersion);
				$returnData['AffectedRequirement'][$functionNo_arr]['functionVersion'] = rtrim($Result_FRDETAIL['functionVersion']);
				$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['changeType'] = rtrim($ResultNumChange['changeType']);
				$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['tableName'] = rtrim($ResultNumChange['tableName']);
				$returnData['AffectedRequirement'][$functionNo_arr][$typeData][$dataName]['columnName'] = rtrim($ResultNumChange['columnName']);
	
				}
						
				$num_row++;
				response($returnData,$row);
				//var_dump($returnData);
			}
		}
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

		$json_response = json_encode($returnData,JSON_PRETTY_PRINT);
		var_dump($json_response);
		echo $json_response;
		return $json_response;
		
	//$json_response = json_encode($response);
	//echo "AffectedRequirement"."<br/>";
	//var_dump($json_response);
	//echo $json_response."<br/>";
	//return $json_response;
}
?>