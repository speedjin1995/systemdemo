<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");
$serial = $post['serial'];

$stmt = $db->prepare("SELECT weighing.*, products.product_name, products.basis_weight, products.width, products.diameter, products.class 
from weighing, products WHERE weighing.deleted = '0' AND weighing.availablility = '0' AND products.id = weighing.product AND weighing.serial_no = ?");
$stmt->bind_param('s', $serial);
$stmt->execute();
$result = $stmt->get_result();
$message = array();

while($row = $result->fetch_assoc()){
    $availablility = "1";
    $id = $row['id'];

	$message[] = array( 
        "id"=>$id,
        "serial_no"=>$row['serial_no'],
        "product_name"=>$row['product_name'],
        "basis_weight"=>$row['basis_weight'],
        "width"=>$row['width'],
        "diameter"=>$row['diameter'],
        "class" => $row['class']
    );

    $update_stmt = $db->prepare("UPDATE weighing SET availablility = ? WHERE id = ?");
    $update_stmt->bind_param('ss', $availablility, $id);
    $update_stmt->execute();
    $update_stmt->close();
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
