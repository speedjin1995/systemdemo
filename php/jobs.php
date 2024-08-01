<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

if(isset($_POST['customers'], $_POST['pickedBy'])){
    $customers = filter_input(INPUT_POST, 'customers', FILTER_SANITIZE_STRING);
    $pickedBy = filter_input(INPUT_POST, 'pickedBy', FILTER_SANITIZE_STRING);
    $user = $_SESSION['userID'];
    $today = date("Y-m-d 00:00:00");
    $createdDatetime = date("Y-m-d h:i:s");
    $poNo = null;
    $doNo = null;

    if(isset($_POST['poNo']) && $_POST['poNo'] != null && $_POST['poNo'] != ''){
        $poNo = filter_input(INPUT_POST, 'poNo', FILTER_SANITIZE_STRING);
    }

    if(isset($_POST['doNo']) && $_POST['doNo'] != null && $_POST['doNo'] != ''){
        $doNo = filter_input(INPUT_POST, 'doNo', FILTER_SANITIZE_STRING);
    }

    // Branch
    $detailsId = $_POST['detailsId'] ?? [];
    $product = $_POST['product'] ?? [];
    $width = $_POST['width'] ?? [];
    $diameter = $_POST['diameter'] ?? [];
    $quantity = $_POST['quantity'] ?? [];

    if($_POST['id'] != null && $_POST['id'] != ''){
        $id = $_POST['id'];
        if ($update_stmt = $db->prepare("UPDATE jobs SET po_no=?, do_no=?, customer=?, pick_by=? WHERE id=?")) {
            $update_stmt->bind_param('sssss', $poNo, $doNo, $customers, $pickedBy, $id);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $update_stmt->error
                    )
                );
            }
            else{
                $update_stmt->close();
                $success = true;

                for($j=0; $j<sizeof($product); $j++){
                    if ($insert_stmt2 = $db->prepare("UPDATE job_details SET product=?, diameter=?, width=?, quantity=? WHERE id=?")) {
                        $insert_stmt2->bind_param('sssss', $product[$j], $diameter[$j], $width[$j], $quantity[$j], $detailsId[$j]);
                        
                        // Execute the prepared query.
                        if (! $insert_stmt2->execute()) {
                            $success = false;
                        }
                    }
                }

                if($success){
                    $insert_stmt2->close();
                    $db->close();

                    echo json_encode(
                        array(
                            "status"=> "success", 
                            "message"=> "Updated Successfully!!"
                        )
                    );
                }
                else{
                    $insert_stmt2->close();
                    $db->close();

                    echo json_encode(
                        array(
                            "status"=> "failed", 
                            "message"=> "Failed to created branch records due to ".$insert_stmt2->error 
                        )
                    );
                }
            }
        }
    }
    else{
        $serialNo = 'J'.date("Ymd");

		if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE created_datetime >= ?")) {
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

                if ($insert_stmt = $db->prepare("INSERT INTO jobs (job_no, po_no, do_no, customer, pick_by, created_by, created_datetime) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('sssssss', $serialNo, $poNo, $doNo, $customers, $pickedBy, $user, $createdDatetime);
                    
                    // Execute the prepared query.
                    if (! $insert_stmt->execute()) {
                        echo json_encode(
                            array(
                                "status"=> "failed", 
                                "message"=> $insert_stmt->error
                            )
                        );
                    }
                    else{
                        $id = $insert_stmt->insert_id;;
                        $insert_stmt->close();
                        $success = true;

                        for($j=0; $j<sizeof($product); $j++){
                            if ($insert_stmt2 = $db->prepare("INSERT INTO job_details (job_id, product, diameter, width, quantity) VALUES (?, ?, ?, ?, ?)")) {
                                $insert_stmt2->bind_param('sssss', $id, $product[$j], $diameter[$j], $width[$j], $quantity[$j]);
                                
                                // Execute the prepared query.
                                if (! $insert_stmt2->execute()) {
                                    $success = false;
                                }
                            }
                            
                            $insert_stmt2->close();
                        }
    
                        if($success){
                            $db->close();
    
                            echo json_encode(
                                array(
                                    "status"=> "success", 
                                    "message"=> "Added Successfully!!"
                                )
                            );
                        }
                        else{
                            $insert_stmt2->close();
                            $db->close();
    
                            echo json_encode(
                                array(
                                    "status"=> "failed", 
                                    "message"=> "Failed to created branch records due to ".$insert_stmt2->error 
                                )
                            );
                        }
                    }
                }
			}
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