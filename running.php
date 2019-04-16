<?php
include('database.php');
function searchCHNO($projectId) {
	$strsql = "SELECT changeRequestNo,changeRequestId
         FROM M_RUNNING_CH
		where projectId = '$projectId' 
			";
		//echo $strsql ;
		return $strsql ;
 } 

?>