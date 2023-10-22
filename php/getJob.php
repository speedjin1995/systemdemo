<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM jobs WHERE id=?")) {
        $update_stmt->bind_param('s', $id);
        
        // Execute the prepared query.
        if (! $update_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                )); 
        }
        else{
            $result = $update_stmt->get_result();
            $message = array();
            
            while ($row = $result->fetch_assoc()) {
                $id = '0';
                $items = array();

                if($row['id']!=null && $row['id']!=''){
                    $id = $row['id'];
                
                    if ($update_stmt = $db->prepare("SELECT * FROM job_details WHERE job_id=?")) {
                        $update_stmt->bind_param('s', $id);
                      
                        if ($update_stmt->execute()) {
                            $result2 = $update_stmt->get_result();
            
                            while ($row2 = $result2->fetch_assoc()) {
                                $items[] = array(
                                    "id"=>$row2['id'],
                                    "job_id"=>$row2['job_id'],
                                    "product"=>$row2['product'],
                                    "diameter"=>$row2['diameter'],
                                    "width"=>$row2['width'],
                                    "quantity"=>$row2['quantity']
                                );
                            }
                        }
                    }
                }

                $message['id'] = $id;
                $message['customer'] = $row['customer'];
                $message['items'] = $items;
                $message['pick_by'] = $row['pick_by'];
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message
                ));   
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Attribute"
            )); 
}
?>