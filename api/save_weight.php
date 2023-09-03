<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['status'], $post['customerName'], $post['product'], $post['weight'], $post['tare']
, $post['net'], $post['shift'], $post['staffName'], $post['createdDatetime'])){

	$status = $post['status'];
	$customerName = $post['customerName'];
	$product = $post['product'];
	$weight = $post['weight'];
	$tare = $post['tare'];
	$net = $post['net'];
	$shift = $post['shift'];
	$staffName = $post['staffName'];
	$createdDatetime = $post['createdDatetime'];
	$serialNo = '0';
	$today = date("Y-m-d 00:00:00");

	if(!isset($post['serialNo']) || $post['serialNo'] == null || $post['serialNo'] == ''){
		$serialNo = date("Ymd");

		if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM weighing WHERE created_datetime >= ?")) {
            $select_stmt->bind_param('s', $today);
            
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

                for($i=0; $i<(4-(int)$charSize); $i++){
                    $serialNo.='0';  // S0000
                }
        
                $serialNo .= strval($count);  //S00009
			}
		}
	}

	if(isset($post['id']) && $post['id'] != null && $post['id'] != ''){
		if ($update_stmt = $db->prepare("UPDATE weight SET vehicleNo=?, lotNo=?, batchNo=?, invoiceNo=?, deliveryNo=?, purchaseNo=?, customer=?, productName=?, package=?
		, unitWeight=?, currentWeight=?, tare=?, totalWeight=?, actualWeight=?, currency=?, moq=?, unitPrice=?, totalPrice=?, remark=?, supplyWeight=?, varianceWeight=?, status=?, 
		dateTime=?, manual=?, manualVehicle=?, manualOutgoing=?, reduceWeight=?, outGDateTime=?, inCDateTime=?, pStatus=?, variancePerc=?, transporter=?, updated_by=? WHERE id=?")){
			$update_stmt->bind_param('ssssssssssssssssssssssssssssssssss', $vehicleNo, $lotNo, $batchNo, $invoiceNo, $deliveryNo, $purchaseNo, $customerNo, $product,
			$package, $unitWeight, $currentWeight, $tareWeight, $totalWeight, $actualWeight, $currency, $moq, $unitPrice, $totalPrice, $remark, $supplyWeight, $varianceWeight, 
			$status, $dateTime, $manual, $manualVehicle, $manualOutgoing, $reduceWeight, $outGDateTime, $inCDateTime, $pStatus, $variancePerc, $transporter, $userId, $_POST['id']);
		
			// Execute the prepared query.
			if (! $update_stmt->execute()){
				echo json_encode(
					array(
						"status"=> "failed", 
						"message"=> $update_stmt->error
					)
				);
			} 
			else{
				$update_stmt->close();
				$db->close();
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Updated Successfully!!" 
					)
				);
			}
		}
		else{
			echo json_encode(
				array(
					"status"=> "failed", 
					"message"=> $insert_stmt->error
				)
			);
		}
	}
	else{
		if ($insert_stmt = $db->prepare("INSERT INTO weighing (serial_no, status, customer_name, product, weight, tare, net, shift, staff_name, created_datetime) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){
		    $insert_stmt->bind_param('ssssssssss', $serialNo, $status, $customerName, $product, $weight, $tare, $net, $shift, $staffName, $createdDatetime);
								
			// Execute the prepared query.
			if (! $insert_stmt->execute()){
				echo json_encode(
					array(
						"status"=> "failed", 
						"message"=> $insert_stmt->error
					)
				);
			} 
			else{
				$insert_stmt->close();
				$db->close();
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Added Successfully!!",
						"serial"=> $serialNo
					)
				);
			}
		}
		else{
			echo json_encode(
				array(
					"status"=> "failed", 
					"message"=> "cannot prepare statement"
				)
			);  
		}
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