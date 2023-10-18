<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['weighing'], $post['createdDatetime'])) {
    $product = $post['weighing'];
    $createdDatetime = $post['createdDatetime'];
    $completed = true;
	$job_id = "0";
    
    for($i=0; $i<count($post['weighing']); $i++){
        $job = $post['weighing'][$i];
		$job_id = $post['weighing'][$i]['job_id'];

		if((int)$job['completed'] < (int)$job['quantity']){
			$completed = false;
		}

		$weigh_data = $job['weigh_data'];
		$success = true;
	
		foreach ($weigh_data as $item) {
			$id = $item['id'];
	
			$check_stmt = $db->prepare("SELECT net, product FROM weighing WHERE id = ?");
			$check_stmt->bind_param('s', $id);
			$check_stmt->execute();
			$check_stmt->store_result();

			if ($check_stmt->num_rows > 0) {
				$check_stmt->bind_result($net, $product);
				$check_stmt->fetch();
				$check_stmt->close();

				$check_stmt2 = $db->prepare("SELECT id, quantity, weight FROM inventory WHERE product_id = ? AND warehouse = ?");
				$check_stmt2->bind_param('ss', $product, $warehouse);
				$check_stmt2->execute();
				$check_stmt2->store_result();

				if ($check_stmt2->num_rows > 0) {
					// Product and warehouse combination exists, update quantity.
					$check_stmt2->bind_result($inventoryId, $quantity, $oriWeight);
					$check_stmt2->fetch();
					$check_stmt2->close();

					$newQuantity = (int)$quantity - 1;
					$newWeight = (float)$oriWeight - (float)$net;

					$update_stmt = $db->prepare("UPDATE inventory SET quantity = ?, weight = ? WHERE id = ?");
					$update_stmt->bind_param('sss', $newQuantity, $newWeight, $inventoryId);
					$update_stmt->execute();
					$update_stmt->close();
				}
			}
		}
    }

	if($completed){
		$newQuantity = "Picked";
		$update_stmt2 = $db->prepare("UPDATE jobs SET status = ? WHERE id = ?");
		$update_stmt2->bind_param('sss', $newQuantity, $job_id);
		$update_stmt2->execute();
		$update_stmt2->close();
	}
	else{
		$newQuantity = "Picking";
		$update_stmt2 = $db->prepare("UPDATE jobs SET status = ? WHERE id = ?");
		$update_stmt2->bind_param('sss', $newQuantity, $job_id);
		$update_stmt2->execute();
		$update_stmt2->close();
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
