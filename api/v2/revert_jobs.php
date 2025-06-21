<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['weighing'], $post['createdDatetime'])) {
    $product = $post['weighing'];
    $createdDatetime = $post['createdDatetime'];
    $availability = '0'; // Set the availability value
    $job_details_id = null; // Set the availability value
    
    for($i=0; $i<count($post['weighing']); $i++){
        $job = $post['weighing'][$i];
        
        $weigh_data = $job['weigh_data'];
        $success = true;
    
        foreach ($weigh_data as $item) {
            $id = $item['id'];
    
            if ($update_stmt = $db->prepare("UPDATE weighing SET availablility=?, job_details_id=? WHERE id=?")) {
                $update_stmt->bind_param('sss', $availability, $job_details_id, $id);
                
                // Execute the prepared query.
                if (!$update_stmt->execute()) {
                    $success = false;
                }
            }
        }
    }

    if ($success) {
        $update_stmt->close();
        $db->close();

        echo json_encode([
            "status" => "success",
            "message" => "Updated Successfully!!"
        ]);
    } 
    else {
        $update_stmt->close();
        $db->close();

        echo json_encode([
            "status" => "failed",
            "message" => "Something went wrong when updating"
        ]);
    }
} else {
    echo json_encode([
        "status" => "failed",
        "message" => "Please fill in all the fields"
    ]);
}
?>
