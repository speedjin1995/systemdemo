<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);
$test = array();

/*echo json_encode([
    "status" => "failed",
    "message" => $post['weighing'][0]['weigh_data'][0]
]);*/

if (isset($post['weighing'])) {
    $product = $post['weighing'];
    //$createdDatetime = $post['createdDatetime'];
    $completed = true;
	$job_id = "0";
	$warehouse = "1";
    
    for($i=0; $i<count($post['weighing']); $i++){
        $job = $post['weighing'][$i];
		$job_id = $post['weighing'][$i]['job_id'];

		if((int)$job['completed'] < (int)$job['quantity']){
			$completed = false;
		}

		$weigh_data = $job['weigh_data'];
		$success = true;
	
		foreach ($weigh_data as $item) {
		    $id = $item['id'];
		    
		    if(!in_array($id, $test)){
    			array_push($test, $id);
    	
    			$check_stmt = $db->prepare("SELECT net, product, diameter, width, grade FROM weighing WHERE id = ?");
    			$check_stmt->bind_param('s', $id);
    			$check_stmt->execute();
    			$check_stmt->store_result();
    
    			if ($check_stmt->num_rows > 0) {
    				$check_stmt->bind_result($net, $product_id, $diameter, $width, $grade);
    				$check_stmt->fetch();
    				$check_stmt->close();
    
    				$check_stmt2 = $db->prepare("SELECT id, quantity, weight FROM inventory WHERE product_id = ? AND diameter = ? AND width = ? AND class = ? AND warehouse = ?");
					$check_stmt2->bind_param('ssssss', $product_id, $basis_weight, $diameter, $width, $grade, $warehouse);
					$check_stmt2->execute();
					$check_stmt2->store_result();
    
    				if ($check_stmt2->num_rows > 0) {
    					$check_stmt2->bind_result($inventoryId, $quantity, $oriWeight);
    					$check_stmt2->fetch();
    					$check_stmt2->close();
    
    					$newQuantity = (int)$quantity - 1;
    					$newWeight = (float)$oriWeight - (float)$net;
    
    					$update_stmt = $db->prepare("UPDATE inventory SET quantity = ?, weight = ? WHERE id = ?");
    					$update_stmt->bind_param('sss', $newQuantity, $newWeight, $inventoryId);
    					$update_stmt->execute();
    					$update_stmt->close();
    				}
    			}
		    }
		}
    }

	if($completed){
	    $newStatus = "Picked";
		$update_stmt2 = $db->prepare("UPDATE jobs SET status = ? WHERE id = ?");
		$update_stmt2->bind_param('ss', $newStatus, $job_id);
		$update_stmt2->execute();
		$update_stmt2->close();
	}
	else{
	    $newStatus = "Picking";
		$update_stmt2 = $db->prepare("UPDATE jobs SET status = ? WHERE id = ?");
		$update_stmt2->bind_param('ss', $newStatus, $job_id);
		$update_stmt2->execute();
		$update_stmt2->close();
	}

	if ($success) {
        //$update_stmt->close();
        $db->close();

        echo json_encode([
            "status" => "success",
            "message" => "Updated Successfully!!"
        ]);
    } 
    else {
        $update_stmt->close();
        $db->close();

        echo json_encode([
            "status" => "failed",
            "message" => "Something went wrong when updating"
        ]);
    }
} else {
    echo json_encode([
        "status" => "failed",
        "message" => "Please fill in all the fields"
    ]);
}
?>