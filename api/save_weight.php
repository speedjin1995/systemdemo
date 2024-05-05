<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['status'], $post['product'], $post['weight'], $post['tare'], $post['mothers']
, $post['net'], $post['shift'], $post['staffName'], $post['createdDatetime'], $post['grade']
, $post['productCode'], $post['productCode'], $post['diameter'], $post['width'], $post['basis_weight'])){
	$mothers = $post['mothers'];
	$status = $post['status'];
	$product = $post['product'];
	$weight = $post['weight'];
	$tare = $post['tare'];
	$net = $post['net'];
	$grade = $post['grade'];
	$shift = $post['shift'];
	$shiftCode = 'D';

	if($shift == 'Morning'){
		$shiftCode = 'D';
	}
	else{
		$shiftCode = 'N';
	}

	$staffName = $post['staffName'];
	$createdDatetime = $post['createdDatetime'];
	$productCode = $post['productCode'];
	$diameter = $post['diameter'];
	$width = $post['width'];
	$basis_weight = $post['basis_weight'];
	$warehouse = '1';
	$serialNo = '0';
	$today = date("Y-m-d 00:00:00");
	$currentDateTime = new DateTime();
    $serialNumber = $currentDateTime->format('ymdHi');
    
    if(!isset($post['mothers']) || $post['mothers'] == null || $post['mothers'] == ''){
        if ($insert_stmt_mother = $db->prepare("INSERT INTO mother_rolls (serial_no) VALUES (?)")){
        	$insert_stmt_mother->bind_param('s', $serialNumber);
            $insert_stmt_mother->execute();
    		$insert_stmt_mother->close();
    		$mothers = $serialNumber;
        }
    }

	if(!isset($post['serialNo']) || $post['serialNo'] == null || $post['serialNo'] == ''){
		$serialNo = $mothers.$shiftCode;

		if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM weighing WHERE mother_serials=? AND created_datetime >= ?")) {
            $select_stmt->bind_param('ss', $mothers, $today);
            
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
	}

	if ($insert_stmt = $db->prepare("INSERT INTO weighing (mother_serials, serial_no, status, product, diameter, width, grade, weight, tare, net, shift, staff_name, created_datetime) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){
		$insert_stmt->bind_param('sssssssssssss', $mothers, $serialNo, $status, $product, $diameter, $width, $grade, $weight, $tare, $net, $shift, $staffName, $createdDatetime);

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

			// Check if the product and warehouse combination exists in the inventory table.
			$check_stmt = $db->prepare("SELECT id, quantity, weight FROM inventory WHERE product_id = ? AND basis_weight = ? AND diameter = ? AND width = ? AND class = ? AND warehouse = ?");
			$check_stmt->bind_param('ssssss', $product, $basis_weight, $diameter, $width, $grade, $warehouse);
			$check_stmt->execute();
			$check_stmt->store_result();

			if ($check_stmt->num_rows > 0) {
				// Product and warehouse combination exists, update quantity.
				$check_stmt->bind_result($inventoryId, $quantity, $oriWeight);
				$check_stmt->fetch();
				$check_stmt->close();

				$newQuantity = (int)$quantity + 1;
				$newWeight = (float)$oriWeight + (float)$net;

				$update_stmt = $db->prepare("UPDATE inventory SET quantity = ?, weight = ? WHERE id = ?");
				$update_stmt->bind_param('sss', $newQuantity, $newWeight, $inventoryId);
				$update_stmt->execute();
				$update_stmt->close();
			} 
			else {
				$newQuantity = 1;
				$insert_inventory_stmt = $db->prepare("INSERT INTO inventory (product_id, basis_weight, diameter, width, class, warehouse, quantity, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
				$insert_inventory_stmt->bind_param('ssssssss', $product, $basis_weight, $diameter, $width, $grade, $warehouse, $newQuantity, $net);
				$insert_inventory_stmt->execute();
				$insert_inventory_stmt->close();
			}

			$db->close();

			echo json_encode(
				array(
					"status" => "success", 
					"message" => "Added Successfully!!",
					"serial" => $serialNo,
					"mothers" => $mothers
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