<?php
session_start();
require_once 'db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

function totalWeight($strings){ 
    $totalSum = 0;

    foreach ($strings as $string) {
        if (preg_match('/([\d.]+)/', $string, $matches)) {
            $value = floatval($matches[1]);
            $totalSum += $value;
        }
    }

    return $totalSum;
}

if(isset($_POST['userID'], $_POST["file"])){
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($select_stmt = $db->prepare("select weighing.*, products.product_code, products.basis_weight, weighing.width, weighing.diameter, grade.grade as class FROM weighing, products, grade WHERE grade.id=weighing.grade AND weighing.product = products.id AND weighing.id=?")) {
        $select_stmt->bind_param('s', $id);

        if (! $select_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong went execute"
                )); 
        }
        else{
            $result = $select_stmt->get_result();

            if ($row = $result->fetch_assoc()) { 
                $dateAndTime = $row['created_datetime'];
                list($datePart, $timePart) = explode(" ", $dateAndTime);
                list($year, $month, $day) = explode("-", $datePart);
                $date = "$day-$month-$year";
                $time = $timePart;
                $serial = $row['serial_no'];
                $weight = $row['net'].' kg';
                $productCode = $row['product_code'];
                $basis_weight = $row['basis_weight'];
                $width = $row['width'];
                $diameter = $row['diameter'];
                $class = $row['class'];

                $message = '<html>
                <head>
                    <style>
                        @media print {
                            @page {
                                margin-left: 0.5in;
                                margin-right: 0.5in;
                                margin-top: 0.1in;
                                margin-bottom: 0.1in;
                            }
                            
                        } 
            
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            
                        } 
                        
                        .table th, .table td {
                            padding: 0.70rem;
                            vertical-align: top;
                            border-top: 1px solid #dee2e6;
                            
                        } 
                        
                        .table-bordered {
                            border: 1px solid #000000;
                        } 
                        
                        .table-bordered th, .table-bordered td {
                            border: 1px solid #000000;
                            font-family: sans-serif;
                        } 
                        
                        .row {
                            display: flex;
                            flex-wrap: wrap;
                            margin-top: 20px;
                        } 
                        
                        .col-md-3{
                            position: relative;
                            width: 25%;
                        }
                        
                        .col-md-9{
                            position: relative;
                            width: 75%;
                        }
                        
                        .col-md-7{
                            position: relative;
                            width: 58.333333%;
                        }
                        
                        .col-md-5{
                            position: relative;
                            width: 41.666667%;
                        }
                        
                        .col-md-6{
                            position: relative;
                            width: 50%;
                        }
                        
                        .col-md-4{
                            position: relative;
                            width: 33.333333%;
                        }
                        
                        .col-md-8{
                            position: relative;
                            width: 66.666667%;
                        }
                        
                        #footer {
                            position: fixed;
                            padding: 10px 10px 0px 10px;
                            bottom: 0;
                            width: 100%;
                            height: 30%;
                        }
            
                        #print-button {
                            position: fixed;
                            bottom: 10px;
                            left: 10px;
                            padding: 10px;
                            background-color: #007bff;
                            color: #fff;
                            cursor: pointer;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                
                <body>
                    <table class="table">
                        <tbody>
                            <tr style="border: 1px solid #000000;">
                                <td style="width: 55%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 36px;font-family: sans-serif;font-weight: bold;">精牛原纸</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 18px;font-family: sans-serif;">口 合格证</span><br>
                                        <span style="font-size: 16px;font-family: sans-serif;">Quality Certificate</span><br>
                                        <span style="font-size: 16px;font-family: sans-serif;">('.$serial.')</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:3px;">
                                    <p>
                                        <span style="font-size: 20px;font-family: sans-serif;">Time : '.$time.'</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:3px;">
                                    <p>
                                        <span style="font-size: 20px;font-family: sans-serif;">DATE : '.$date.'</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:0px;"></td>
                                <td style="width: 45%;border-top:0px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:0px;"></td>
                                <td style="width: 45%;border-top:0px;"></td>
                            </tr>
                            <tr style="border: 1px solid #000000;">
                                <td style="width: 55%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 24px;font-family: sans-serif;">产品编号 Product No.</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 24px;font-family: sans-serif;">定量 BASIS WEIGHT C/M2</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:3px;">
                                    <img src="assets/barcode2.png" alt="Girl in a jacket" width="50%">
                                    <p style="margin-top: 0px;">
                                        <span style="font-size: 16px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$productCode.'</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:3px;">
                                    <p>
                                        <span style="font-size: 20px;font-family: sans-serif;">'.$basis_weight.'</span>
                                    </p>
                                </td>
                            </tr>
                            <tr style="border: 1px solid #000000;">
                                <td style="width: 55%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 24px;font-family: sans-serif;">宽度 WIDTH MM</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 24px;font-family: sans-serif;">直径 DIAMETER MM</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:3px;">
                                    <p>
                                        <span style="font-size: 24px;font-family: sans-serif;">'.$width.'</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:3px;">
                                    <p>
                                        <span style="font-size: 26px;font-family: sans-serif;">'.$diameter.'</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:0px;"></td>
                                <td style="width: 45%;border-top:0px;"></td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:0px;"></td>
                                <td style="width: 45%;border-top:0px;"></td>
                            </tr>
                            <tr style="border: 1px solid #000000;">
                                <td style="width: 55%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 20px;font-family: sans-serif;">质量 WEIGHT KG</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:0px;">
                                    <p>
                                        <span style="font-size: 20px;font-family: sans-serif;">产品等级 PRODUCT CLASS</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 55%;border-top:3px;">
                                    <img src="assets/barcode2.png" alt="Girl in a jacket" width="50%">
                                    <p style="margin-top: 0px;">
                                        <span style="font-size: 16px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$weight.'</span>
                                    </p>
                                </td>
                                <td style="width: 45%;border-top:3px;">
                                    <p>
                                        <span style="font-size: 24px;font-family: sans-serif;">'.$class.'</span>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </body>
            </html>';

                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    )
                );
            }
            else{
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Data Not Found"
                    )); 
            }
        }
    }
    else{
        echo json_encode(
            array(
                "status" => "failed",
                "message" => "Something went wrong"
            )); 
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