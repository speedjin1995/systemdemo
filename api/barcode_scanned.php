<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);

$services = 'Scan_Weight';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$now = date("Y-m-d H:i:s");
$serial = $post['serial'];
$width = $post['width'];
$diameter = $post['diameter'];
$job_details_id = $post['job_details_id'];

$stmt = $db->prepare("SELECT weighing.*, products.product_name, products.basis_weight, grade.grade as class
from weighing, products, grade WHERE weighing.deleted = '0' AND products.id = weighing.product AND grade.id=weighing.grade 
AND weighing.serial_no = ?");
$stmt->bind_param('s', $serial);
$stmt->execute();
$result = $stmt->get_result();
$message = array();

if ($result->num_rows > 0) {
    if($row = $result->fetch_assoc()){
        if($row['availablility'] == "1"){
            $stmt->close();
            $db->close();
    
            echo json_encode(
                array(
                    "status"=> "failed", 
                    "message"=> 'This item is being picked'
                )
            );
        }
        else{
            if($row['width'] == $width && $row['diameter'] == $diameter){
                $availablility = "1";
                $id = $row['id'];
            
                $message[] = array( 
                    "id"=>$id,
                    "serial_no"=>$row['serial_no'],
                    "product_name"=>$row['product_name'],
                    "basis_weight"=>$row['basis_weight'],
                    "width"=>$row['width'],
                    "diameter"=>$row['diameter'],
                    "class" => $row['class'],
                    "grade" => $row['grade']
                );
            
                $update_stmt = $db->prepare("UPDATE weighing SET availablility = ?, job_details_id = ? WHERE id = ?");
                $update_stmt->bind_param('sss', $availablility, $job_details_id, $id);
                $update_stmt->execute();
                $update_stmt->close();
        
                $stmt->close();
                
                $response = json_encode(
            		array(
                        "status"=> "success", 
                        "message"=> $message
                    )
            	);
            	$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
            
            	$db->close();
            	echo $response;
            }
            else{
                $stmt->close();
                $db->close();
        
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> 'Width or Diameter incorrect'
                    )
                );
            }
        }
    }
    else{
        $stmt->close();
        $db->close();
        
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Something went wrong"
            )
        );
    }
}
else{
    $stmt->close();
    $db->close();
    
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Serial No not found"
        )
    );
    /*$parts = preg_split('/[ND]/', $serial);
    $mother_rools = $parts[0];
    $status = 'Incoming';
    $randomNumber = rand(620, 650);
    $tare = '0';
    $staffName = 'SYSTEM';
    $grade = '1';
    $createdDatetime = date("Y-m-d H:i:s");
    $product = '11';
    
    $shift = 'Morning';
	$currentTime = date('H'); // Get current hour in 24-hour format

    if ($currentTime >= 8 && $currentTime < 17) {
        $shift = 'Morning'; // Day shift
    } 
    else {
        $shift = 'Night'; // Night shift
    }
    
    if ($insert_stmt = $db->prepare("INSERT INTO weighing (mother_serials, serial_no, status, product, diameter, width, grade, weight, tare, net, shift, staff_name, job_details_id, created_datetime) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){
		$insert_stmt->bind_param('ssssssssssssss', $mother_rools, $serial, $status, $product, $diameter, $width, $grade, $randomNumber, $tare, $randomNumber, $shift, $staffName, $job_details_id, $createdDatetime);

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
		    $id = $insert_stmt->insert_id;;
			$insert_stmt->close();

			$message[] = array( 
                "id"=>$id,
                "serial_no"=>$serial,
                "product_name"=>$product,
                "basis_weight"=>' ',
                "width"=>$width,
                "diameter"=>$diameter,
                "class" => '',
                "grade" => $grade
            );

			$db->close();
        
            echo json_encode(
                array(
                    "status"=> "success", 
                    "message"=> $message
                )
            );
		}
	}*/
}


?>
