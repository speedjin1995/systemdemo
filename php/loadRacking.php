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
if($searchValue != ''){
   $searchQuery = " AND (racking.rack_number like '%".$searchValue."%' OR warehouse.warehouse like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from racking");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from racking WHERE deleted = '0'".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select racking.id, warehouse.warehouse, racking.rack_number from racking, warehouse WHERE racking.deleted = '0' AND warehouse.id=racking.warehouse".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array( 
      "counter"=>$counter,
      "id"=>$row['id'],
      "warehouse"=>$row['warehouse'],
      "rack_number"=>$row['rack_number']
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