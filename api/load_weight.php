<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");

$stmt = $db->prepare("SELECT weighing.*, products.product_name, products.product_code, products.basis_weight, 
users.name, parent_product.name_en, parent_product.name_ch, grade.grade as class from products, weighing, 
users, parent_product, grade WHERE products.product_parents = parent_product.id AND weighing.staff_name = users.id 
AND weighing.product = products.id AND grade.id=weighing.grade AND weighing.deleted = '0'");
$stmt->execute();
$result = $stmt->get_result();
$message = array();

while($row = $result->fetch_assoc()){
	$message[] = array( 
        "id"=>$row['id'],
        "serial_no"=>$row['serial_no'],
        "product"=>$row['product'],
        "product_name"=>$row['product_name'],
        "product_code"=>$row['product_code'],
        "basis_weight"=>$row['basis_weight'],
        "width"=>$row['width'],
        "diameter"=>$row['diameter'],
        "class"=>$row['class'],
        "name_en"=>$row['name_en'],
        "name_ch"=>$row['name_ch'],
        "weight"=>$row['weight'],
        "tare"=>$row['tare'],
        "net"=>$row['net'],
        "shift"=>$row['shift'],
        "staff_name"=>$row['staff_name'],
        "name"=>$row['name'],
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
