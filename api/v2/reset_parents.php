<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

$services = 'Reset_Parents';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$today = date("Y-m-d 00:00:00");
$currentDateTime = new DateTime();
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
		$shiftCode = 'D';
		$currentTime = date('H'); // Get current hour in 24-hour format

        if ($currentTime >= 8 && $currentTime < 17) {
            $shiftCode = 'D'; // Day shift
        } 
        else {
            $shiftCode = 'N'; // Night shift
        }
		
		$serialNo = $serialNumber.$shiftCode;
    
		if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM weighing WHERE mother_serials=? AND created_datetime >= ?")) {
            $select_stmt->bind_param('ss', $serialNumber, $today);
            
            // Execute the prepared query.
            if (! $select_stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Failed to get latest count"
                    )); 
            }
            else{
                $result = $select_stmt->get_result();
                $count = 1;
                
                if ($row = $result->fetch_assoc()) {
                    $count = (int)$row['COUNT(*)'] + 1;
                    $select_stmt->close();
                }

                $charSize = strlen(strval($count));

                for($i=0; $i<(3-(int)$charSize); $i++){
                    $serialNo.='0';  // S00
                }
        
                $serialNo .= strval($count);  //S009
			}
		}
		$response = json_encode(
			array(
				"status" => "success", 
				"message" => "Added Successfully!!",
				"serial" => $serialNumber,
				"serialNo" => $serialNo
			)
		);
		$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        $stmtU->bind_param('ss', $response, $invid);
        $stmtU->execute();

		$db->close();
		echo $response;
	}
}

?>