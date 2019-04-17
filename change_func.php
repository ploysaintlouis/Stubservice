<?php
include('database.php');
 //Search FR No
function searchFRId($functionNo,$functionVersion,$projectId) {
	$strsql = "SELECT distinct functionId FROM M_FN_REQ_DETAIL
		where functionVersion = '$functionVersion' 
		and activeflag = '1' 
		and functionNo = '$functionNo'
		and projectId = '$projectId' 
			";
			//echo $strsql ;
		return $strsql ;
 } 

 function searchNumChange($functionId,$functionVersion) {
	$strsql = "SELECT case changeType WHEN 'add' THEN 'A' WHEN 'edit' THEN 'C' WHEN 'delete' THEN 'B' END AS CType,*
	   FROM T_TEMP_CHANGE_LIST
		where functionVersion = '$functionVersion' 
		and functionId = '$functionId'
		and confirmflag = '1' 
		order by CType
			";
		//echo $strsql ;
		return $strsql ;
 } 
//หา DETAIL ของ FR ที่ IMPACT เฉพาะ FR นั้น
 function searchFRImpact($ResultNumChange,$functionId,$functionVersion,$projectId) {
	$strsql = "SELECT a.*,b.functionDescription
	   FROM M_FN_REQ_DETAIL a,M_FN_REQ_HEADER b
		where a.functionVersion != '$functionVersion' 
		and a.functionId != '$functionId'
		AND a.dataName = '$ResultNumChange[dataName]'
		and a.activeflag = '1' 
		and a.projectid = '$projectId' 
		AND a.projectid = b.projectid
		AND a.functionNo = b.functionNo 
		AND a.functionId = b.functionId
		AND a.functionVersion = b.functionVersion
			";
		//echo $strsql ;
		return $strsql ;
 } 
 function searchFRMAXFuncNo() {

 	$strsql = " SELECT max(functionNo) AS Max_FRNO 
	 			FROM M_FN_REQ_HEADER ";
	//echo $strsql;
	return $strsql;
} 
function searchFRMAXFuncVer($functionNo) {

	$strsql = " SELECT max(functionVersion) AS Max_FRVer
				FROM M_FN_REQ_HEADER 
				WHERE functionNo = '$functionNo' ";

   //echo $strsql;
   return $strsql;
} 
//update activeflag = '0' FR เก่า
function UpdateFR_HEADER($functionVersion,$functionNo,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "UPDATE M_FN_REQ_HEADER
			set updateDate = '$currentDateTime',
			updateUser = '$username',
			activeFlag = '0'	
			WHERE functionVersion = '$functionVersion' 
			ANd functionNo = '$functionNo'
			and activeflag = '1' 
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 }  						
function InsertNewFR_HEADER($functionDescription,$New_functionversion,$New_FRNO,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');
	$strsql = "INSERT INTO M_FN_REQ_HEADER (functionNo, functionDescription, projectId, createDate, createUser, updateDate, updateUser,functionversion,activeflag) 
	VALUES ('$New_FRNO', '{$functionDescription}', {$projectId}, '$currentDateTime', '{$username}', '$currentDateTime', '{$username}','$New_functionversion','1')";
	//echo $strsql ;
	return $strsql ;
} 
//หา functionId -อง FR ใหม่
function searchFR_NEW($New_FRNO,$functionVersion,$projectId) {
	$strsql = "SELECT functionId
	   FROM M_FN_REQ_HEADER 
		where functionVersion = '$functionVersion' 
		ANd functionNo = '$New_FRNO'
		and activeflag = '1' 
		and projectid = '$projectId' 
			";
		//echo $strsql ;
		return $strsql ;
 } 

function InsertFR_DETAIL($functionNo,$functionVersion,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "INSERT INTO M_FN_REQ_DETAIL 
	SELECT '$projectId','$New_functionId','$New_FRNO','$New_functionversion',b.typeData,b.dataName,
	b.schemaVersionId,b.refTableName,b.refColumnName,b.dataType,b.dataLength,b.decimalPoint,b.constraintPrimaryKey,
	b.constraintUnique,b.constraintDefault,b.constraintNull,b.constraintMinValue,b.constraintMaxValue,'$currentDateTime',
	NULL,'1','$currentDateTime','$username','$currentDateTime','$username'
	FROM M_FN_REQ_DETAIL b
			WHERE b.functionVersion = '$functionVersion' 
			AND b.functionNo = '$functionNo'
			AND b.activeflag = '1' 
			AND b.projectid = '$projectId' 
	";
	//echo $strsql;
	return $strsql ;
} 
//update activeflag = '0' FR เก่า
function UpdateFR_DETAIL($functionVersion,$functionNo,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "UPDATE M_FN_REQ_DETAIL
			set effectiveEndDate = '$currentDateTime',
			updateDate = '$currentDateTime',
			updateUser = '$username',
			activeFlag = '0'	
			WHERE functionVersion = '$functionVersion' 
			ANd functionNo = '$functionNo'
			and activeflag = '1' 
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 }  
 function InsertNewFR_DETAIL($ResultNumChange,$New_functionId,$New_functionversion,$New_FRNO,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');

	$strsql = "INSERT INTO M_FN_REQ_DETAIL 
	values('$projectId','$New_functionId','$New_FRNO','$New_functionversion','$ResultNumChange[typeData]','$ResultNumChange[dataName]',
	'$ResultNumChange[schemaVersionId]','$ResultNumChange[tableName]','$ResultNumChange[columnName]','$ResultNumChange[newDataType]',
	'$ResultNumChange[newDataLength]',	'$ResultNumChange[newScaleLength]','N','$ResultNumChange[newUnique]','$ResultNumChange[newDefaultValue]',
	'$ResultNumChange[newNotNull]',	'$ResultNumChange[newMinValue]','$ResultNumChange[newMaxValue]',
	'$currentDateTime',	NULL,'1','$currentDateTime','$username','$currentDateTime','$username')
	";
	//echo $strsql;
	return $strsql ;
} 
function UpdateFRField_DETAIL($ResultNumChange,$functionVersion,$functionNo,$projectId,$username) {
	$currentDateTime = date('Y-m-d H:i:s');
	$fieldName = '';
	if($ResultNumChange['newDataType'] != null){
		$fieldName = " dataType = '$ResultNumChange[newDataType]' ,";
	}
	if($ResultNumChange['newDataLength']!= null){
		$fieldName .= " dataLength = '$ResultNumChange[newDataLength]' ,";
	}
	if($ResultNumChange['newScaleLength'] != null){
		$fieldName .= " decimalPoint = '$ResultNumChange[newScaleLength]' ,";
	}
	if($ResultNumChange['newDefaultValue'] != null){
		$fieldName .= " constrraintDefault = '$ResultNumChange[newDefaultValue]' ,";
	}
	if($ResultNumChange['newMinValue'] != null){
		$fieldName .= " ConstraintMinValue = '$ResultNumChange[newMinValue]' ,";
	}	
	if($ResultNumChange['newMaxValue'] != null){
		$fieldName .= " ConstraintMaxValue = '$ResultNumChange[newMaxValue]' ,";
	}					
	$condition = $fieldName;
	$strsql = "UPDATE M_FN_REQ_DETAIL
			set $condition 
			constraintUnique = '$ResultNumChange[newUnique]',
			constraintNull = '$ResultNumChange[newNotNull]'
			WHERE functionVersion = '$functionVersion' 
			AND functionNo = '$functionNo'
			and activeflag = '1' 
			AND dataName = '$ResultNumChange[dataName]'
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 }  				

 function DeleteFRField_DETAIL($ResultNumChange,$New_functionId,$functionVersion,$functionNo,$projectId,$username) {
					
	$strsql = "DELETE FROM M_FN_REQ_DETAIL
			WHERE functionNo = '$functionNo' 
			AND functionVersion = '$functionVersion'
			and activeflag = '1' 
			AND dataName = '$ResultNumChange[dataName]'
			and projectid = '$projectId' ";
		//echo $strsql ;
		return $strsql ;
 } 
 function InsertFR_IMPACT_HEADER($returnData,$projectId) {
	
	$strsql = "INSERT INTO M_FR_IMPACT_HEADER (projectInfo,functionNo, functionVersion, Typedata) 
	VALUES ('$New_FRNO', '{$functionDescription}', {$projectId}, '$currentDateTime', '{$username}', '$currentDateTime', '{$username}','$New_functionversion','1')";
	//echo $strsql ;
	return $strsql ;
} 
?>