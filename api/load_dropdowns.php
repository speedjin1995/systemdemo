<?php
require_once 'db_connect.php';

$products = $db->query("SELECT * FROM products WHERE deleted = '0'");
$customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");

$data3 = array();
$data5 = array();

while($row3=mysqli_fetch_assoc($products)){
    $data3[] = array( 
        'id'=>$row3['id'],
        'product_name'=>$row3['product_name']
    );
}

while($row5=mysqli_fetch_assoc($customers)){
    $data5[] = array( 
        'id'=>$row5['id'],
        'customer_name'=>$row5['customer_name']
    );
}

$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "products"=> $data3, 
        "customers"=> $data5,
    )
);
?>