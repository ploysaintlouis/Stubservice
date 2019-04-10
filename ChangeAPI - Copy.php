<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-Type:application/json");
$data = json_decode(file_get_contents('php://input'), true);
include('change_func.php');
//echo json_encode($data);
//echo $data['changeRequestInfo[functionNo]'];
$functionNo = $data['changeRequestInfo[functionNo]'];	//functionname = FR01'
$functionVersion = $data['changeRequestInfo[functionVersion]'];	//version V.1
$projectId = $data['projectInfo'];

//echo $functionNo;
$dsn	= 'Driver={SQL Server Native Client 11.0};server=107-NANNAPHAT\SQL2018;Database=test';
//$dsn=	 'Driver={SQL Server Native Client 11.0};server=DESKTOP-71LOP0E\SQLEXPRESS;Database=test';
$username = 'sa';
$password = 'password';	

$objConnect = odbc_connect($dsn,$username,$password);
if($objConnect)
{
	$strsql = "SELECT * FROM M_USERS ";
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
	$USER = odbc_fetch_array($objExec);
	
		//หา จำนวนของ คำขอ change ที่ relate DB เพื่อรับค่า
		$strsql = "SELECT * FROM T_TEMP_CHANGE_LIST a,M_FN_REQ_DETAIL b
				where b.functionNo = '$functionNo'
				and a.functionVersion = '$functionVersion' 
				and a.dataId = b.dataId
				and a.functionId  = b.functionId
				and a.functionVersion = b.functionVersion
				and (b.refTableName is not null or b.refTableName = ' ')
				and (b.refColumnName is not null or b.refColumnName = ' ')
				and b.schemaVersionId is not null
				and a.confirmflag = '1' 
				and b.activeflag = '1'
				and b.projectId = '$projectId' 
				";
		//echo $strsql;
		/*
		$strsql = "SELECT * FROM T_TEMP_CHANGE_LIST a,M_FN_REQ_DETAIL b
				where b.functionNo = '$functionNo'
				and a.functionVersion = '$functionVersion' 
				and a.dataId = b.dataId
				and a.functionId  = b.functionId
				and a.functionVersion = b.functionVersion
				and (b.refTableName is not null or b.refTableName = ' ')
				and (b.refColumnName is not null or b.refColumnName = ' ')
				and b.schemaVersionId is not null
				and b.schemaVersionId is null
				and a.confirmflag = '1' 
				and b.projectId = '$projectId' 
				";		
*/
	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
	$num_row = 0;
	$row = odbc_num_rows($objExec);
	//echo $row;
	while($num_row < $row)
	{
		$currentDateTime = date('Y-m-d H:i:s');

		//รับค่า
		$changeType[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][changeType]']);
		$dataName[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][dataName]']);
		$typeData[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][typeData]']);
		$dataId[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][dataId]']);
		$dataType[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][dataType]']);
		$dataLength[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][dataLength]']);
		$scale[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][scale]']);
		$unique[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][unique]']);
		$notNull[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][notNull]']);
		$default[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][default]']);
		$min[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][min]']);
		$max[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][max]']);
		
		if ($data['changeRequestInfo[inputs]['.$num_row.'][tableName]'] == null){
			$tableName[$num_row] = '00';
		}else{
			$tableName[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][tableName]']);
		}
		if ($data['changeRequestInfo[inputs]['.$num_row.'][columnName]'] == null){
			$columnName[$num_row] = '00';
		}else{
			$columnName[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][columnName]']);
		}
		$modifyFlag[$num_row]= rtrim($data['changeRequestInfo[inputs]['.$num_row.'][modifyFlag]']);

		//echo $tableName[$num_row];
		
		// หา impact ของ functional requirement จากการ change ที่ relate DB
		if (($tableName[$num_row] != '00') and ($columnName[$num_row] != '00')){
				$strsql = "SELECT functionId,functionVersion,dataType FROM M_FN_REQ_DETAIL
				where refTableName = '$tableName[$num_row]'
				and refColumnName = '$columnName[$num_row]'
				and schemaVersionId is not null
				and activeflag = '1'
				and projectId = '$projectId' 
				";
				//echo $strsql;	
				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");

				while($ResultFR = odbc_fetch_array($objExec)) //เอาค่าที่เอ็กซ์คิวเก็บไว้เป็น Array
				{
					$ResultFRfunctionVersion= $ResultFR['functionVersion'];
					$ResultFRfunctionId= $ResultFR['functionId'];
					$ResultFRdataType = $ResultFR['dataType'];
				} 
				//echo $ResultFRdataType;
			//ต้องแสดง FR ที่กระทบ
		
			//หา TC ที่สัมพันธ์กับ FR ข้างบน จาก RTM
			$strsql = "SELECT a.testCaseId,b.testCaseNo from M_RTM_VERSION a,M_TESTCASE_HEADER b
			where a.testCaseId = b.testCaseId
			and a.testCaseversion = b.testcaseVersion
			and a.projectId = b.projectId
			and a.functionId = '$ResultFRfunctionId'
			and a.functionVersion = '$ResultFRfunctionVersion'
			and a.activeflag = '1'
			and a.projectId = '$projectId' 	"	;	

				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
				$ResultTC = odbc_fetch_array($objExec);
				$ResultTCtestCaseNo = $ResultTC['testCaseNo'];
				$ResultTCtestCaseId = $ResultTC['testCaseId'];
				$changeType[$num_row] = rtrim($changeType[$num_row]);  //ตัดช่องว่างทางด้านขวาออก

				if ($changeType[$num_row] == 'edit'){
					$strsql = "SELECT a.testCaseId,a.testcaseVersion,a.typeData,a.refdataId,a.refdataName,a.testData,
					b.testCaseDescription,b.expectedResult
					FROM M_TESTCASE_DETAIL a,M_TESTCASE_HEADER b
					WHERE a.testCaseNo = '$ResultTCtestCaseNo'
					AND a.refdataId = '$dataId[$num_row]'
					AND a.testcaseVersion = '$ResultFRfunctionVersion'
					AND a.activeflag = '1'
					AND a.testCaseNo = b.testCaseNo
					AND a.testcaseVersion = b.testcaseVersion "	;	
			
					//echo $strsql;
					$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
					$ImpactTC = odbc_fetch_array($objExec);

					$ImpactTC['testData'] = rtrim($ImpactTC['testData']);  //ตัดช่องว่างทางด้านขวาออก
					//echo $ImpactTC['testData'];
					$length_testdata = strlen($ImpactTC['testData']);
//echo $length_testdata;
					$dataLength[$num_row] = rtrim($dataLength[$num_row]);  //ตัดช่องว่างทางด้านขวาออก
					
					//ส่งค่า TC ที่ปรับปรุงไปยัง json
					if ($length_testdata >= $dataLength[$num_row]){  //testdate ต้องทำการแก้ไข
						//HEADER
						$returnData['testCaseNo'] = $ResultTCtestCaseNo;
						$returnData['ChangeType'] = 'edit';
						$returnData['testcaseVersion'] = $ImpactTC['testcaseVersion']+1;
						$returnData['testCaseDescription'] = rtrim($ImpactTC['testCaseDescription']);
						$returnData['expectedResult'] = $ImpactTC['expectedResult'];
						//echo $returnData['testcaseVersion'];

						//DETAIL  CHANGE DATANAME
						if( $ImpactTC['refdataName'] != $data['changeRequestInfo[inputs]['.$num_row.'][dataName]'] ){
							$returnData['refdataName'] = $data['changeRequestInfo[inputs]['.$num_row.'][dataName]'];
						}
						else{
							$returnData['refdataName'] = $ImpactTC['refdataName'];
						}
					//echo $ResultFR['dataType'];
						//DETAIL  edit testdata
						if ($dataType[$num_row] == NULL){
							$dataType[$num_row] = $ResultFRdataType;
						} 
						
						if((rtrim($dataType[$num_row]) == 'int') or (rtrim($dataType[$num_row]) == 'INT')){
							$returnData['testData'] = randInt($min[$num_row],$max[$num_row]);
						}
						if((rtrim($dataType[$num_row]) == 'char') || (rtrim($dataType[$num_row]) == 'CHAR')
						||(rtrim($dataType[$num_row]) == 'varchar') || (rtrim($dataType[$num_row]) == 'VARCHAR')){
							$returnData['testData'] = randChar();
						}												
					}
					else{	//ไม่กระทบ ไม่ต้องทำการแก้ไข
						$returnData = null ;
					}
				}
		}
		else if ($changeType[$num_row] == 'edit'){
				//หา FR version ของ FR ที่เปลี่ยนแปลง
				$strsql = "SELECT * FROM T_TEMP_CHANGE_LIST a,M_FN_REQ_DETAIL b
				where b.functionNo = '$functionNo'
				and a.functionVersion = '$functionVersion' 
				and a.dataId = b.dataId
				and a.functionId  = b.functionId
				and a.functionVersion = b.functionVersion
				and b.schemaVersionId is null
				and a.confirmflag = '1' 
				and b.projectId = '$projectId' 
				";					
				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
				//echo $strsql;
				while($ResultFR_NOT = odbc_fetch_array($objExec)) //เอาค่าที่เอ็กซ์คิวเก็บไว้เป็น Array
				{
					$ResultFR_NOTfunctionVersion= $ResultFR_NOT['functionVersion'];
					$ResultFR_NOTfunctionId= $ResultFR_NOT['functionId'];
					//echo $ResultFR_NOTfunctionVersion;
				} 

				//หา TC ที่สัมพันธ์กับ FR ข้างบน จาก RTM
				$strsql = "SELECT a.testCaseId,b.testCaseNo from M_RTM_VERSION a,M_TESTCASE_HEADER b
				where a.testCaseId = b.testCaseId
				and a.testCaseversion = b.testcaseVersion
				and a.projectId = b.projectId
				and a.activeflag = '1'
				and a.projectId = '$projectId' 	
				AND a.functionVersion = '$ResultFR_NOTfunctionVersion
				AND a.functionId = '$ResultFR_NOTfunctionId' "	;		
echo $strsql;
				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
				$ResultTC_NOT = odbc_fetch_array($objExec);

				$ResultTC_NOTtestCaseNo = $ResultTC_NOT['testCaseNo'];
				$ResultTC_NOTtestCaseId = $ResultTC_NOT['testCaseId'];
				//echo $ResultTC_NOT['testCaseNo'];
				//echo strlen($ResultTC_NOT['testCaseId']);  //นับความยาวข้อมูล
				$changeType[$num_row] = rtrim($changeType[$num_row]);  //ตัดช่องว่างทางด้านขวาออก
				//echo strlen($changeType[$num_row]);
				//หาว่ากระทบใน TC นั้นหรือไม่
				if ($changeType[$num_row] == 'edit'){
					$strsql = "SELECT a.testCaseId,a.testcaseVersion,a.typeData,a.refdataId,a.refdataName,a.testData,
					b.testCaseDescription,b.expectedResult
					FROM M_TESTCASE_DETAIL a,M_TESTCASE_HEADER b
					WHERE a.testCaseNo = '$ResultTC_NOTtestCaseNo'
					AND a.refdataId = '$dataId[$num_row]'
					AND a.testcaseVersion = '$ResultFR_NOTfunctionVersion'
					AND a.activeflag = '1' 
					AND b.activeflag = '1'
					AND a.testCaseNo = b.testCaseNo
					AND a.testcaseVersion = b.testcaseVersion "	;	
			
					//echo $strsql;
					$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
					$ImpactTC_NOT = odbc_fetch_array($objExec);

					$ImpactTC_NOT['testData'] = rtrim($ImpactTC_NOT['testData']);  //ตัดช่องว่างทางด้านขวาออก
					echo $ImpactTC_NOT['testData'];
					$length_testdata = strlen($ImpactTC_NOT['testData']);
//echo $length_testdata;
					$dataLength[$num_row] = rtrim($dataLength[$num_row]);  //ตัดช่องว่างทางด้านขวาออก

					if ($length_testdata >= $dataLength[$num_row]){  //testdate ต้องทำการแก้ไข
						$returnData['testCaseNo'] = $ResultTC_NOTtestCaseNo;
					}
					else{	//ไม่กระทบ ไม่ต้องทำการแก้ไข
						$returnData['testCaseNo'] = $ResultTC_NOTtestCaseNo;
					}

				}
			}else if($changeType[$num_row] == 'add'){
					//หา FR version ของ FR ที่เปลี่ยนแปลง
					$strsql = "SELECT * FROM M_FN_REQ_HEADER
							where functionNo = '$functionNo'
							and functionVersion = '$functionVersion' 
							and projectId = '$projectId' 
							and activeflag = '1'
							";					
					$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
					$ResultFR_NOT = odbc_fetch_array($objExec) ;    //เอาค่าที่เอ็กซ์คิวเก็บไว้เป็น Array
					$ResultFR_NOTfunctionVersion= $ResultFR_NOT['functionversion'];
					$ResultFR_NOTfunctionId= $ResultFR_NOT['functionId'];
						//echo $ResultFR_NOTfunctionVersion;
					
					//หา TC ที่สัมพันธ์กับ FR ข้างบน จาก RTM
					$strsql = "SELECT a.testCaseId,b.testCaseNo,b.testCaseDescription from M_RTM_VERSION a,M_TESTCASE_HEADER b
					where a.testCaseId = b.testCaseId
					and a.testCaseversion = b.testcaseVersion
					and a.projectId = b.projectId
					and a.activeflag = '1'
					and a.projectId = '$projectId' 	
					AND a.functionVersion = '$ResultFR_NOTfunctionVersion'
					AND a.functionId = '$ResultFR_NOTfunctionId' "	;		
	//echo $strsql;
					$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
					$ResultTC_NOT = odbc_fetch_array($objExec);
	
					$ResultTC_NOTtestCaseNo = $ResultTC_NOT['testCaseNo'];
					$ResultTC_NOTtestCaseId = $ResultTC_NOT['testCaseId'];
					//echo $ResultTC_NOT['testCaseNo'];
					//echo strlen($ResultTC_NOT['testCaseId']);  //นับความยาวข้อมูล
					$changeType[$num_row] = rtrim($changeType[$num_row]);  //ตัดช่องว่างทางด้านขวาออก
	
					//แก้ไข TCใหม่ เพิ่ม
					//ส่งค่า TC ที่ปรับปรุงไปยัง json
						//HEADER
						$strsql = " select max(testCaseNo) AS Max_TCNO from M_TESTCASE_HEADER ";
						$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
						$Max_TCNO  = odbc_fetch_array($objExec);
	//echo substr($Max_TCNO['Max_TCNO'],7,7)+1 ;
						$TCNO = substr($Max_TCNO['Max_TCNO'],7,7)+1;
						//echo $TCNO ;
						$returnData['testCaseNo'] = substr($ResultTC_NOTtestCaseNo,0,7).$TCNO ;
						//echo $returnData['testCaseNo'];
						$returnData['ChangeType'] = 'add';
						$returnData['testcaseVersion'] = 1;
						$returnData['testCaseDescription'] = rtrim($ResultTC_NOT['testCaseDescription']);
						$returnData['expectedResult'] = 'Valid';
						//echo $returnData['testcaseVersion'];

						
						//DETAIL  CHANGE DATANAME
						$returnData['refdataName'] = $data['changeRequestInfo[inputs]['.$num_row.'][dataName]'];

					//echo $ResultFR['dataType'];
						//DETAIL  testdata						
						if((rtrim($dataType[$num_row]) == 'int') or (rtrim($dataType[$num_row]) == 'INT')){
							$returnData['testData'] = randInt($min[$num_row],$max[$num_row]);
						}
						if((rtrim($dataType[$num_row]) == 'char') || (rtrim($dataType[$num_row]) == 'CHAR')
						||(rtrim($dataType[$num_row]) == 'varchar') || (rtrim($dataType[$num_row]) == 'VARCHAR')){
							$returnData['testData'] = randChar($dataLength[$num_row]);
							//echo $returnData['testData'];
						}												
			}
			else if($changeType[$num_row] == 'delete'){
				//หา FR version ของ FR ที่เปลี่ยนแปลง
				$strsql = "SELECT * FROM M_FN_REQ_HEADER
						where functionNo = '$functionNo'
						and functionVersion = '$functionVersion' 
						and projectId = '$projectId' 
						and activeflag = '1'
						";					
				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
				$ResultFR_NOT = odbc_fetch_array($objExec) ;    //เอาค่าที่เอ็กซ์คิวเก็บไว้เป็น Array
				$ResultFR_NOTfunctionVersion= $ResultFR_NOT['functionversion'];
				$ResultFR_NOTfunctionId= $ResultFR_NOT['functionId'];
					//echo $ResultFR_NOTfunctionVersion;
				
				//หา TC ที่สัมพันธ์กับ FR ข้างบน จาก RTM
				$strsql = "SELECT a.testCaseId,b.testCaseNo,b.testCaseDescription from M_RTM_VERSION a,M_TESTCASE_HEADER b
				where a.testCaseId = b.testCaseId
				and a.testCaseversion = b.testcaseVersion
				and a.projectId = b.projectId
				and a.activeflag = '1'
				and a.projectId = '$projectId' 	
				AND a.functionVersion = '$ResultFR_NOTfunctionVersion'
				AND a.functionId = '$ResultFR_NOTfunctionId' "	;		
	//echo $strsql;
				$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
				$ResultTC_NOT = odbc_fetch_array($objExec);

				$ResultTC_NOTtestCaseNo = $ResultTC_NOT['testCaseNo'];
				$ResultTC_NOTtestCaseId = $ResultTC_NOT['testCaseId'];
				//echo $ResultTC_NOT['testCaseNo'];
				//echo strlen($ResultTC_NOT['testCaseId']);  //นับความยาวข้อมูล
				$changeType[$num_row] = rtrim($changeType[$num_row]);  //ตัดช่องว่างทางด้านขวาออก

				//แก้ไข TCใหม่ เพิ่ม
				//ส่งค่า TC ที่ปรับปรุงไปยัง json
					//HEADER
					$strsql = "SELECT a.testCaseId,a.testcaseVersion,a.typeData,a.refdataId,a.refdataName,a.testData,
					b.testCaseDescription,b.expectedResult
					FROM M_TESTCASE_DETAIL a,M_TESTCASE_HEADER b
					WHERE a.testCaseNo = '$ResultTC_NOTtestCaseNo'
					AND a.testcaseVersion = '$ResultFR_NOTfunctionVersion'
					AND a.activeflag = '1' 
					AND b.activeflag = '1'
					AND a.testCaseNo = b.testCaseNo
					AND a.testcaseVersion = b.testcaseVersion "	;	
			
					//echo $strsql;
					$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
					$x=0;
					while($ImpactTC_NOT = odbc_fetch_array($objExec)){
						$ImpactTC_NOT['typeData'][$x];
						$ImpactTC_NOT['refdataId'][$x];
						$ImpactTC_NOT['testData'][$x];
						$ImpactTC_NOT['refdataName'][$x];
echo $ImpactTC_NOT['refdataName'][$x];
						$x++;
					}
										
					$strsql = " select max(testCaseNo) AS Max_TCNO from M_TESTCASE_HEADER ";
					$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
					$Max_TCNO  = odbc_fetch_array($objExec);
	//echo substr($Max_TCNO['Max_TCNO'],7,7)+1 ;
					$TCNO = substr($Max_TCNO['Max_TCNO'],7,7)+1;
					//echo $TCNO ;
					$returnData['testCaseNo'] = substr($ResultTC_NOTtestCaseNo,0,7).$TCNO ;
					//echo $returnData['testCaseNo'];
					$returnData['ChangeType'] = 'add';
					$returnData['testcaseVersion'] = 1;
					$returnData['testCaseDescription'] = rtrim($ResultTC_NOT['testCaseDescription']);
					$returnData['expectedResult'] = 'Valid';
					//echo $returnData['testcaseVersion'];
					
					//DETAIL  CHANGE DATANAME
					$returnData['refdataName'] = $data['changeRequestInfo[inputs]['.$num_row.'][dataName]'];

				//echo $ResultFR['dataType'];
					//DETAIL  testdata						
					if((rtrim($dataType[$num_row]) == 'int') or (rtrim($dataType[$num_row]) == 'INT')){
						$returnData['testData'] = randInt($min[$num_row],$max[$num_row]);
					}
					if((rtrim($dataType[$num_row]) == 'char') || (rtrim($dataType[$num_row]) == 'CHAR')
					||(rtrim($dataType[$num_row]) == 'varchar') || (rtrim($dataType[$num_row]) == 'VARCHAR')){
						$returnData['testData'] = randChar($dataLength[$num_row]);
						//echo $returnData['testData'];
					}		
			}
		//echo $returnData['testCaseVersion'];
		$num_row++;
		response($returnData);
	}
}
else
{
	echo "Database Connect Failed.";
}

odbc_close($objConnect);
/*
if (isset($_GET['projectInfo']) && $_GET['projectInfo']!="") {

            $strsql = "SELECT * FROM M_PROJECT where projectId = '$projectInfo' ";
            echo $strsql;
            $objQuery = $this->db->query($strsql);
            if(!$objQuery){
                echo "<script language='javascript'>alert('Code Correct.');</script>";
            }
            return $objQuery->result_array();	
}else{
            response(NULL, NULL, 400,"Invalid Request");
}*/
//random ตัวเลข int
function randInt($min,$max){
	//DETAIL  edit testdata
	if(isset($min) or $min == NULL ){
		$min = 0;
	}	
	return rand($min,$max);
}
//ramdom char
function randChar($length) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$value = '';
	for ( $i = 0; $i < $length; $i++ )
	   $value .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	return $value;
 } 
//function response($order_id,$amount,$response_code,$response_desc,$retuneData){
function response($returnData){
	/*$response['order_id'] = $order_id;
	$response['amount'] = $amount;
	$response['response_code'] = $response_code;
	$response['response_desc'] = $response_desc;
	$response['order_id'] = "1";
	$response['amount'] = "2";
	$response['response_code'] = "3";*/
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
		$response['refdataName'] = $returnData['refdataName'];	
	
	//echo $returnData['testCaseNo'];
	//echo $returnData['ChangeType'];
	//echo $returnData['testcaseVersion'];	

	$json_response = json_encode($response);
	echo "AffectedTestCase"."<br/>";
	echo $json_response."<br/>";
}
?>