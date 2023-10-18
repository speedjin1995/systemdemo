<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['weighing'], $post['createdDatetime'])){

	$weighing = json_decode($post['weighing'], true);
	$createdDatetime = $post['createdDatetime'];
	$jobProduct = $weighing['jobs_data'];
	$availablility = '1';
	$success = true;

	for($i=0; $i<count($jobProduct); $i++){
		$jobProduct = $jobProduct[$i]['weigh_data'];
		$id = $jobProduct['id'];

		if ($update_stmt = $db->prepare("UPDATE weighing SET availablility=? WHERE id=?")){
			$update_stmt->bind_param('ss', $availablility, $id);
		
			// Execute the prepared query.
			if (! $update_stmt->execute()){
				$success = false;
			} 
			
		}
	}
	
	if($success){
		$update_stmt->close();
		$db->close();

		echo json_encode(
			array(
				"status"=> "success", 
				"message"=> "Reveted Successfully!!" 
			)
		);
	}
	else{
		$update_stmt->close();
		$db->close();
		
		echo json_encode(
			array(
				"status"=> "failed", 
				"message"=> "something went wrong when revert" 
			)
		);
	}
} 
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );     
}

?>