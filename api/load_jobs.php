<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");
$id = $post['id'];

$stmt = $db->prepare("SELECT select jobs.id, jobs.job_no, customers.customer_name, users.name, jobs.status, jobs.created_datetime 
from jobs, users, customers WHERE jobs.deleted = '0' AND users.id = jobs.pick_by AND customers.id = jobs.customer AND jobs.status <> 'Picked'
AND jobs.pick_by = ?");
$insert_stmt->bind_param('s', $id);
$stmt->execute();
$result = $stmt->get_result();
$message = array();
$message2 = array();

while($row = $result->fetch_assoc()){
	$items = array();

    if($row['id']!=null && $row['id']!=''){
        $id = $row['id'];

        if ($update_stmt = $db->prepare("SELECT * FROM job_details, products WHERE job_details.product = products.id AND job_details.job_id=?")) {
            $update_stmt->bind_param('s', $id);
            
            if ($update_stmt->execute()) {
                $result = $update_stmt->get_result();

                while ($row2 = $result->fetch_assoc()) {
                    $items[] = array(
                        "id"=>$row2['id'],
                        "job_id"=>$row2['job_id'],
                        "product"=>$row2['product'],
                        "product_name" => $row2['product_name'],
                        "quantity"=>$row2['quantity']
                    );
                }
            }
        }
    }

    if($row['status'] == 'Ready To Pick'){
        $message2[] = array( 
            "id"=>$row['id'],
            "job_no"=>$row['job_no'],
            "customer_name"=>$row['customer_name'],
            "name"=>$row['name'],
            "status"=>$row['status'],
            "created_datetime"=>$row['created_datetime'],
            "items" => $items
        );
    }
    else{
        $message[] = array( 
            "id"=>$row['id'],
            "job_no"=>$row['job_no'],
            "customer_name"=>$row['customer_name'],
            "name"=>$row['name'],
            "status"=>$row['status'],
            "created_datetime"=>$row['created_datetime'],
            "items" => $items
        );
    }
}

$stmt->close();
$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "picking"=> $message, 
        "ready_to_pick"=> $message2
    )
);
?>
