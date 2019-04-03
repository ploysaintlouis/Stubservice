<?php

//defined('BASEPATH') OR exit('No direct script access allowed');
define("BASEPATH",true); 
header("Content-Type:application/json");
include('database.php');

$data = json_decode(file_get_contents('php://input'), true);
//echo json_encode($data);


	$dsn=	 'Driver={SQL Server Native Client 11.0};server=DESKTOP-71LOP0E\SQLEXPRESS;Database=test';
	$username = 'sa';
	$password = 'password';	

	$objConnect = odbc_connect($dsn,$username,$password);
	if($objConnect)
	{
            $strsql = "SELECT * FROM M_FN_REQ_DETAIL WHERE functionNo = 'OS_FR_03 ' AND activeflag = '1' AND functionVersion = '1 ' ";
         //   echo $strsql;

	$objExec = odbc_exec($objConnect, $strsql) or die ("Error Execute [".$strsql."]");
	while($objResult = odbc_fetch_array($objExec))
	{
	echo $objResult["dataId"];
	};
	}
	else
	{
		echo "Database Connect Failed.";
	}

	odbc_close($objConnect);

if (isset($_GET['projectInfo']) && $_GET['projectInfo']!="") {
/*
            $strsql = "SELECT * FROM M_PROJECT where projectId = '$projectInfo' ";
            echo $strsql;
            $objQuery = $this->db->query($strsql);
            if(!$objQuery){
                echo "<script language='javascript'>alert('Code Correct.');</script>";
            }
            return $objQuery->result_array();	*/
}else{
           response(NULL, NULL, 400,"Invalid Request",$data);
}
function response($order_id,$amount,$response_code,$response_desc,$data){
	/*$response['order_id'] = $order_id;
	$response['amount'] = $amount;
	$response['response_code'] = $response_code;
	$response['response_desc'] = $response_desc;
	$response['order_id'] = "1";
	$response['amount'] = "2";
	$response['response_code'] = "3";
	$response['response_desc'] = $response_desc;*/
	//$response['projectInfo'] =$data['projectInfo'];
	//$response['typeData'] =$data['changeRequestInfo[inputs][1][typeData]'];
	$response['projectInfo'] = $data['projectInfo'];

	$response = $data['changeRequestInfo[inputs][1][typeData]'];
	$json_response = json_encode($response);
	//$json_response= str_replace(/,\/,/,\n/,$json_response);
	echo "$json_response";


	
}
?>