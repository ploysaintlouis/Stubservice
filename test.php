<?php
$funno = 'HC_FR_01';
$x[1] = '101';
$x[2] = '102';

	$arr = array(
	"affectedRequirement" =>array(
	"$funno"=>array(
	"functionVersion"=>"1",
		"Output"=>array(
		"dId"=>array(
		"tableName" => "",
		"columnName" => "",
		"changeType"=> "add"
		),
		"dUnit Price"=>array(
		"tableName" => "ORDER_DETAILS",
		"columnName" => "UNIT_PRICE",
		"changeType"=> "edit"
		),
		"dDiscount"=>array(
		"tableName" => "ORDER_DETAILS",
		"columnName" => "DISCOUNT",
		"changeType"=> "edit"
		),
		"dPrice"=>array(
		"tableName" => "",
		"columnName" => "",
		"changeType"=> "delete"
		)
		)))
	);

	//var_dump($arr);

	$json_arr = json_encode($arr,JSON_PRETTY_PRINT);
	var_dump($json_arr);
	echo $json_arr;
?>

