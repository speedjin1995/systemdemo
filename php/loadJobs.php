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
   $searchQuery = " AND (jobs.job_no like '%".$searchValue."%' OR users.name like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from jobs, users, customers WHERE jobs.deleted = '0' AND users.id = jobs.pick_by AND customers.id = jobs.customer");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from jobs, users, customers WHERE jobs.deleted = '0' AND users.id = jobs.pick_by AND customers.id = jobs.customer".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select jobs.id, jobs.job_no, customers.customer_name, users.name, jobs.status, jobs.created_datetime 
from jobs, users, customers WHERE jobs.deleted = '0' AND users.id = jobs.pick_by AND customers.id = jobs.customer
".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
  $items = array();

  if($row['id']!=null && $row['id']!=''){
    $id = $row['id'];

    if ($update_stmt = $db->prepare("SELECT job_details.*, products.product_name FROM job_details, products WHERE job_details.product = products.id AND job_details.job_id=?")) {
      $update_stmt->bind_param('s', $id);
      
      if ($update_stmt->execute()) {
        $result = $update_stmt->get_result();

        while ($row2 = $result->fetch_assoc()) {
          $items2 = array();

          if($row2['id']!=null && $row2['id']!=''){
            $id2 = $row2['id'];
        
            if ($update_stmt2 = $db->prepare("SELECT weighing.serial_no, users.name FROM weighing, users WHERE weighing.staff_name = users.id AND weighing.job_details_id=?")) {
              $update_stmt2->bind_param('s', $id2);
              
              if ($update_stmt2->execute()) {
                $result2 = $update_stmt2->get_result();
                $items2 = array();
        
                while ($row3 = $result2->fetch_assoc()) {
                  $items2[] = array(
                    "serial_no"=>$row3['serial_no'],
                    "name"=>$row3['name']
                  );
                }
              }
            }
          }

          $items[] = array(
            "id"=>$row2['id'],
            "job_id"=>$row2['job_id'],
            "product"=>$row2['product'],
            "product_name" => $row2['product_name'],
            "width"=>$row2['width'],
            "diameter" => $row2['diameter'],
            "quantity"=>$row2['quantity'],
            "weighing"=>$items2
          );
        }
      }
    }
  }

  $data[] = array( 
    "id"=>$row['id'],
    "job_no"=>$row['job_no'],
    "customer_name"=>$row['customer_name'],
    "name"=>$row['name'],
    "status"=>$row['status'],
    "created_datetime"=>$row['created_datetime'],
    "items" => $items
  );
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