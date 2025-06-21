<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['id'])) {
    $product = $post['id'];
    $availability = '0'; // Set the availability value
    $job_details_id = null; // Set the availability value
    
    if ($update_stmt = $db->prepare("UPDATE weighing SET availablility=?, job_details_id=? WHERE id=?")) {
        $update_stmt->bind_param('sss', $availability, $job_details_id, $product);
        
        // Execute the prepared query.
        if (!$update_stmt->execute()) {
            $update_stmt->close();
            $db->close();

            echo json_encode([
                "status" => "failed",
                "message" => "Something went wrong when updating"
            ]);
        }
        else{
            $update_stmt->close();
            $db->close();
    
            echo json_encode([
                "status" => "success",
                "message" => "Updated Successfully!!"
            ]);
        }
    }
} else {
    echo json_encode([
        "status" => "failed",
        "message" => "Please fill in all the fields"
    ]);
}
?>
