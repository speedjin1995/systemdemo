<?php
## Database configuration
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
$searchQuery = "";
if($searchValue != ''){
   $searchQuery = " and (serial_no like '%".$searchValue."%' or 
   lorry_no like '%".$searchValue."%' )";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from weighing");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from weighing WHERE deleted = '0'".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * from weighing WHERE deleted = '0'".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array( 
    "no"=>$counter,
    "id"=>$row['id'],
    "serial_no"=>$row['serial_no'],
    "group_no"=>$row['group_no'],
    "customer"=>$row['customer'],
    "supplier"=>$row['supplier'],
    "product"=>$row['product'],
    "driver_name"=>$row['driver_name'],
    "lorry_no"=>$row['lorry_no'],
    "farm_id"=>$row['farm_id'],
    "average_cage"=>$row['average_cage'],
    "average_bird"=>$row['average_bird'],
    "minimum_weight"=>$row['minimum_weight'],
    "maximum_weight"=>$row['maximum_weight'],
    "weight_data"=>json_decode($row['weight_data'], true),
    "created_datetime"=>$row['created_datetime'],
    "start_time"=>$row['start_time'],
    "end_time"=>$row['end_time']
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