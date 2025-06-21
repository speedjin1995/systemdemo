<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");
$product = $post['product'];
$width = $post['width'];
$diameter = $post['diameter'];

$stmt = $db->prepare("SELECT weighing.*, products.product_name, products.basis_weight, grade.grade as class
from weighing, products, grade WHERE weighing.deleted = '0' AND products.id = ? AND grade.id=weighing.grade 
AND weighing.width = ? AND weighing.diameter =? AND weighing.availablility = '0'");
$stmt->bind_param('sss', $product, $width, $diameter);
$stmt->execute();
$result = $stmt->get_result();
$message = array();
$message2 = array();

while($row = $result->fetch_assoc()){
	$message[] = array( 
        "id"=>$row['id'],
        "serial_no"=>$row['serial_no'],
        "product_name"=>$row['product_name'],
        "basis_weight"=>$row['basis_weight'],
        "width"=>$row['width'],
        "diameter"=>$row['diameter'],
        "class" => $row['class'],
        "grade" => $row['grade']
    );
}

$stmt->close();
$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "message"=> $message,
    )
);
?>