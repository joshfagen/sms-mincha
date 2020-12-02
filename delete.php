<?php

include('db.php');
include("function.php");

if(isset($_POST["user_id"]))
{

	$statement = $connection->prepare(
		"DELETE FROM member WHERE uid = :uid"
	);
	$result = $statement->execute(
		array(
			':uid'	=>	$_POST["user_id"]
		)
	);
	
	if(!empty($result))
	{
		echo 'Data Deleted successfully';
	}
}



?>