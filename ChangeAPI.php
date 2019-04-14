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

require_once('database.php');
include('change_func.php');
include('random.php');

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
	//$ResultNumChange = odbc_fetch_row($objExec);
	//echo $ResultNumChange['CType'];
	//echo $row;
	//while($num_row <= $row)
	/*
	while(($ResultNumChange = odbc_fetch_array($objExec)) and ($num_row <= $row)){
		echo $ResultNumChange['CType'];
		echo $num_row ;
		$num_row++;
	}*/
	if (null!=$row)
	{
		while(($ResultNumChange = odbc_fetch_array($objExec)) and ($num_row <= $row)){
			//echo $ResultNumChange['CType'];
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

				$returnData['$functionVersion'] = $functionVersion;
				$returnData['$functionNo'] = $functionNo;
				$returnData['$dataName'] = $ResultNumChange['dataName'];
				$returnData['$changeType'] = $ResultNumChange['changeType'];
				$returnData['$tableName'] = $ResultNumChange['tableName'];
				$returnData['$columnName'] = $ResultNumChange['columnName'];

				//echo $ResultNumChange['typeData'];
				if ($ResultNumChange['typeData'] == 1)
				{
					$returnData['$typeData'] = 'Input';
				}else{
					$returnData['$typeData'] = 'Output';
				}
				//echo $returnData['$typeData'];
			
			}else if($ResultNumChange['CType'] == 'B'){	//รายการ change =edit
				//echo 'B';
				//UPDATE FR เฉพาะ field
				if (null !=$New_functionId){
					$strsql1 = UpdateFRField_DETAIL($ResultNumChange,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);

					//หา Impact จากการ edit กับ FR อื่นๆ
					$strsql1 = searchFRImpact($ResultNumChange,$functionId,$functionVersion,$projectId);
					$objExec1 = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");	
					$row_FR = odbc_num_rows($objExec1);

					if ($row_FR != 0){
						//UPDATE วันที่ FR DETAIL เก่า ที่impact จากการ edit นี้
						$Result_FRDETAIL = odbc_fetch_array($objExec1);
						$strsql1 = UpdateFRField_DETAIL($ResultNumChange,$Result_FRDETAIL['functionVersion'],$Result_FRDETAIL['functionNo'],$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);		

						//UPDATE วันที่ FR HEADER เก่าที่impact จากการ edit นี้
						$strsql1 = UpdateFR_HEADER($Result_FRDETAIL['functionVersion'],$Result_FRDETAIL['functionNo'],$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
					}

				}
				else{
					if ($num_row == '1'){ // ทำเฉพาะรายการ EDIT รายการแรก รายการต่อไป update เฉพาะ fleid
						$strsql1  = searchFRMAXFuncVer($functionNo);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql."]");
						$Max_FRVer  = odbc_fetch_array($objExec1);
						//echo $Max_FRNO['Max_FRNO'];
						//echo substr($Max_FRNO['Max_FRNO'],6,7)+1 ;
						$Max_FRVer = $Max_FRVer+1;
						
						//Insert New FR_HEADER
						$strsql1 = InsertNewFR_HEADER($functionDescription,$Max_FRVer,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
						//UPDATE วันที่ FR เก่า
						$strsql1 = UpdateFR_HEADER($functionVersion,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);

						//Insert New FR_DETAIL
						$strsql1 = InsertFR_DETAIL($functionNo,$functionVersion,$New_functionId,$Max_FRVer,$functionNo,$projectId,$username);
						$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
						odbc_free_result($objExec1);
					}
					//UPDATE วันที่ FR เก่า
					$strsql1 = UpdateFRField_DETAIL($ResultNumChange,$Max_FRVer,$functionNo,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);
				}
			}else if($ResultNumChange['CType'] == 'C'){	//รายการ change = delete
				//echo "C";
				if (null !=$New_functionId){
					$strsql1 = DeleteFRField_DETAIL($ResultNumChange,$New_functionversion,$New_FRNO,$projectId,$username);
					$objExec1  = odbc_exec($objConnect, $strsql1) or die ("Error Execute [".$strsql1."]");
					odbc_free_result($objExec1);

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
					}					
				}
			}
			$num_row++;
		}
	}
		//echo $num_row;

	//$returnData = 'HH';
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

	$functionNo = $returnData['$functionNo'];
	$Typedata = $returnData['$typeData'];
	$dataName = $returnData['$dataName'];

	$response = array(
					"AffectedRequirement"=>array(
						array(
							"$functionNo"=>array(
							"functionVersion"=>$returnData['$functionVersion'],
							"$Typedata"=>array(
								"$dataName"=>array(
									"refTableName"=>$returnData['$tableName'],
									"refColumnName"=>$returnData['$columnName'],
									"changeType"=>$returnData['$changeType']
									)
								)
							)
						)
					)
				);

	$json_response = json_encode($response);
	//echo "AffectedRequirement"."<br/>";
	var_dump($json_response);
	echo $json_response."<br/>";
}
?>