<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");
$serial = $post['serial'];
$width = $post['width'];
$diameter = $post['diameter'];
$job_details_id = $post['job_details_id'];

$stmt = $db->prepare("SELECT weighing.*, products.product_name, products.basis_weight, grade.grade as class
from weighing, products, grade WHERE weighing.deleted = '0' AND weighing.availablility = '0' AND products.id = weighing.product AND grade.id=weighing.grade 
AND weighing.serial_no = ?");
$stmt->bind_param('s', $serial);
$stmt->execute();
$result = $stmt->get_result();
$message = array();

if ($result->num_rows > 0) {
    if($row = $result->fetch_assoc()){
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
            $db->close();
    
            echo json_encode(
                array(
                    "status"=> "success", 
                    "message"=> $message
                )
            );
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
}


?>
