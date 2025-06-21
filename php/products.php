<?php
session_start();
require_once "db_connect.php";

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.php";</script>';
}

if(isset($_POST['code'], $_POST['product'], $_POST['productParents'])){
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $product = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
    $productParents = filter_input(INPUT_POST, 'productParents', FILTER_SANITIZE_STRING);
    
    $basis = null;
    $width = null;
    $diameter = null;
    $class = null;
    $remark = null;
    
    if(isset($_POST['basis']) && $_POST['basis'] != null && $_POST['basis'] != ''){
       $basis = filter_input(INPUT_POST, 'basis', FILTER_SANITIZE_STRING);
    }
    
    if(isset($_POST['width']) && $_POST['width'] != null && $_POST['width'] != ''){
        $width = filter_input(INPUT_POST, 'width', FILTER_SANITIZE_STRING);
    }
    
    if(isset($_POST['diameter']) && $_POST['diameter'] != null && $_POST['diameter'] != ''){
        $diameter = filter_input(INPUT_POST, 'diameter', FILTER_SANITIZE_STRING);
    }
    
    if(isset($_POST['class']) && $_POST['class'] != null && $_POST['class'] != ''){
        $class = filter_input(INPUT_POST, 'class', FILTER_SANITIZE_STRING);
    }

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
                $id = $insert_stmt->insert_id;;
                $insert_stmt->close();

                if ($insert_stmt2 = $db->prepare("INSERT INTO inventory (product_id) VALUES (?)")) {
                    $insert_stmt2->bind_param('s', $id);
                    
                    // Execute the prepared query.
                    if (! $insert_stmt2->execute()) {
                        echo json_encode(
                            array(
                                "status"=> "failed", 
                                "message"=> $insert_stmt2->error
                            )
                        );
                    }
                    else{
                        $insert_stmt2->close();
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