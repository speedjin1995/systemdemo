<?php
## Database configuration
session_start();
require_once 'db_connect.php';

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value

## Search 
$searchQuery = " ";

if($_POST['products'] != null && $_POST['products'] != '' && $_POST['products'] != '-'){
  $searchQuery = " and inventory.product_id = '".$_POST['products']."'";
}

if($_POST['diameter'] != null && $_POST['diameter'] != '' && $_POST['diameter'] != '-'){
  $searchQuery = " and inventory.diameter like '%".$_POST['diameter']."%'";
}

if($_POST['width'] != null && $_POST['width'] != '' && $_POST['width'] != '-'){
  $searchQuery = " and inventory.width = '".$_POST['width']."'";
}

if($_POST['grade'] != null && $_POST['grade'] != '' && $_POST['grade'] != '-'){
  $searchQuery = " and inventory.class like '%".$_POST['grade']."%'";
}

if($_POST['basisWeight'] != null && $_POST['basisWeight'] != '' && $_POST['basisWeight'] != '-'){
  $searchQuery = " and inventory.basis_weight like '%".$_POST['basisWeight']."%'";
}

if($searchValue != ''){
  $searchQuery = " AND (products.product_name like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from inventory");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from inventory WHERE deleted = '0'".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select inventory.id, inventory.diameter, inventory.width, products.product_code, products.product_name, inventory.quantity, inventory.weight, 
warehouse.warehouse, grade.grade from inventory, products, warehouse, grade WHERE inventory.deleted = '0' AND inventory.product_id = products.id AND warehouse.id = inventory.warehouse 
AND grade.id = inventory.class".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array( 
    "counter"=>$counter,
    "id"=>$row['id'],
    "diameter"=>$row['diameter'],
    "width"=>$row['width'],
    "product_code"=>$row['product_code'],
    "product_name"=>$row['product_name'],
    "quantity"=>$row['quantity'],
    "weight"=>$row['weight'],
    "grade"=>$row['grade'],
    "warehouse"=>$row['warehouse']
  );

  $counter++;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data
);

echo json_encode($response);

?>