<?php
require_once 'db_connect.php';

$products = $db->query("SELECT products.*, parent_product.name_en, parent_product.name_ch FROM products, parent_product WHERE products.product_parents = parent_product.id AND products.deleted = '0'");
$warehouse = $db->query("SELECT * FROM warehouse WHERE deleted = '0'");
$racking = $db->query("SELECT * FROM racking WHERE deleted = '0'");

$data1 = array();
$data2 = array();
$data3 = array();

while($row1=mysqli_fetch_assoc($warehouse)){
    $data1[] = array( 
        'id'=>$row1['id'],
        'warehouse'=>$row1['warehouse']
    );
}

while($row2=mysqli_fetch_assoc($racking)){
    $data2[] = array( 
        'id'=>$data2['id'],
        'warehouse'=>$data2['warehouse'],
        'rack_number'=>$data2['rack_number']
    );
}

while($row3=mysqli_fetch_assoc($products)){
    $data3[] = array( 
        'id'=>$row3['id'],
        'product_code'=>$row3['product_code'],
        'product_name'=>$row3['product_name'],
        'basis_weight'=>$row3['basis_weight'],
        'width'=>$row3['width'],
        'diameter'=>$row3['diameter'],
        'class'=>$row3['class'],
        'name_en'=>$row3['name_en'],
        'name_ch'=>$row3['name_ch']
    );
}

$db->close();

echo json_encode(
    array(
        "status"=> "success",
        "warehouses"=> $data1,
        "rackings"=> $data2,
        "products"=> $data3
    )
);
?>