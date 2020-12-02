<?php
include('db.php');
include('function.php');
if(isset($_POST["user_id"]))
{
	$output = array();
	$statement = $connection->prepare(
		"SELECT * FROM member 
		WHERE uid = '".$_POST["user_id"]."' 
		LIMIT 1"
	);
	$statement->execute();
	$result = $statement->fetchAll();

	foreach($result as $row)
	{
		$output["name"] = $row["name"];
		$output["email"] = $row["email"];
		$output["phone"] = $row["phone"];
		$output["is_active"] = $row["is_active"];
		$output["get_call"] = $row["get_call"];

	}
	echo json_encode($output);
}
?>