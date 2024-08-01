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
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " where (serial_no like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db, "select count(*) as allcount from mother_rolls");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db, "select count(*) as allcount from mother_rolls".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * from mother_rolls ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
  $items = array();
  
  if($row['serial_no']!=null && $row['serial_no']!=''){
    $id = $row['serial_no'];

    if ($update_stmt = $db->prepare("SELECT weighing.*, users.name, products.product_name, products.basis_weight, grade.grade as class FROM weighing, users, products, grade WHERE grade.id=weighing.grade AND products.id=weighing.product AND users.id=weighing.staff_name AND weighing.deleted='0' AND weighing.mother_serials=?")) {
      $update_stmt->bind_param('s', $id);
      
      if ($update_stmt->execute()) {
        $result2 = $update_stmt->get_result();

        while ($row2 = $result2->fetch_assoc()) {
          $items[] = array(
            "id"=>$row2['id'],
            "serial_no"=>$row2['serial_no'],
            "product"=>$row2['product'],
            "product_name"=>$row2['product_name'],
            "basis_weight"=>$row2['basis_weight'],
            "diameter"=>$row2['diameter'],
            "width"=>$row2['width'],
            "grade"=>$row2['grade'],
            "class"=>$row2['class'],
            "weight"=>$row2['weight'],
            "tare"=>$row2['tare'],
            "net"=>$row2['net'],
            "shift"=>$row2['shift'],
            "staff_name"=>$row2['name'],
            "created_datetime"=>$row2['created_datetime']
          );
        }
      }
    }
  }

  $data[] = array( 
    "no"=>$counter,
    "id"=>$row['id'],
    "serial_no"=>$row['serial_no'],
    "completed"=>$row['completed'],
    "created_datetime"=>$row['created_datetime'],
    "items" => $items
  );

  $counter++;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data,
  "query" => $empQuery
);

echo json_encode($response);

?>