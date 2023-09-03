<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");

$stmt = $db->prepare("SELECT * from weighing WHERE deleted = '0'");
$stmt->execute();
$result = $stmt->get_result();
$message = array();

while($row = $result->fetch_assoc()){
	$message[] = array( 
        "id"=>$row['id'],
        "serial_no"=>$row['serial_no'],
        "status"=>$row['status'],
        "customer_name"=>$row['customer_name'],
        "supplier_name"=>$row['supplier_name'],
        "product"=>$row['product'],
        "weight"=>$row['weight'],
        "tare"=>$row['tare'],
        "net"=>$row['net'],
        "shift"=>$row['shift'],
        "staff_name"=>$row['staff_name'],
        "created_datetime"=>$row['created_datetime']
    );
}

$stmt->close();
$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "message"=> $message
    )
);
?>
