<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

if(isset($_POST['code'], $_POST['product'], $_POST['basis'], $_POST['width'], $_POST['diameter'], $_POST['class'], $_POST['productParents'])){
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
    $basis = filter_input(INPUT_POST, 'basis', FILTER_SANITIZE_STRING);
    $width = filter_input(INPUT_POST, 'width', FILTER_SANITIZE_STRING);
    $diameter = filter_input(INPUT_POST, 'diameter', FILTER_SANITIZE_STRING);
    $class = filter_input(INPUT_POST, 'class', FILTER_SANITIZE_STRING);
    $productParents = filter_input(INPUT_POST, 'productParents', FILTER_SANITIZE_STRING);
    $remark = null;

    if(isset($_POST['remark']) && $_POST['remark'] != null && $_POST['remark'] != ''){
        $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_STRING);
    }

    if($_POST['id'] != null && $_POST['id'] != ''){
        if ($update_stmt = $db->prepare("UPDATE products SET product_code=?, product_parents=?, product_name=?, remark=?, basis_weight=?, width=?, diameter=?, class=? WHERE id=?")) {
            $update_stmt->bind_param('sssssssss', $code, $productParents, $product, $remark, $basis, $width, $diameter, $class, $_POST['id']);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $update_stmt->error
                    )
                );
            }
            else{
                $update_stmt->close();
                $db->close();
                
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Updated Successfully!!" 
                    )
                );
            }
        }
    }
    else{
        if ($insert_stmt = $db->prepare("INSERT INTO products (product_code, product_parents, product_name, remark, basis_weight, width, diameter, class) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {
            $insert_stmt->bind_param('ssssssss', $code, $productParents, $product, $remark, $basis, $width, $diameter, $class);
            
            // Execute the prepared query.
            if (! $insert_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $insert_stmt->error
                    )
                );
            }
            else{
                $insert_stmt->close();
                $db->close();
                
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Added Successfully!!" 
                    )
                );
            }
        }
    }
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );
}
?>