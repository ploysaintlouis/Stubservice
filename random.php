<?php

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
function random_round($len){
	$chars = "0123456789";
	$ret_char = "";
	$num = strlen($chars);
	for($i = 0; $i < $len; $i++) {
		$ret_char.= $chars[rand()%$num];
		$ret_char.= ""; 
	}
	return $ret_char; 
}
?>