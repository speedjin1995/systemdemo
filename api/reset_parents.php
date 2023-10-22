<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);
$today = date("Y-m-d 00:00:00");
$serialNumber = $currentDateTime->format('ymdHi');

if ($insert_stmt = $db->prepare("INSERT INTO mother_rolls (serial_no) VALUES (?)")){
	$insert_stmt->bind_param('s', $serialNumber);

	// Execute the prepared query.
	if (! $insert_stmt->execute()){
		echo json_encode(
			array(
				"status" => "failed", 
				"message" => $insert_stmt->error
			)
		);
	} 
	else {
		$insert_stmt->close();
		$db->close();

		echo json_encode(
			array(
				"status" => "success", 
				"message" => "Added Successfully!!",
				"serial" => $serialNumber
			)
		);
	}
}

?>