<?php
session_start();
require_once 'php/db_connect.php';

$productName = '';
$productCode = '';
$basis_weight = '';
$width = '';
$diameter = '';
$class = '';
$weight = '0.00kg';
$serial = '';

if(isset($_GET['serial']) && $_GET['serial'] != null && $_GET['serial'] != ''){
    $serial = (string)$_GET['serial'];
}

if(isset($_GET['product']) && $_GET['product'] != null && $_GET['product'] != ''){
    $productName = (string)$_GET['product'];
    $products = $db->query("SELECT * FROM products WHERE product_name = '".$productName."'");

    if ($row5=mysqli_fetch_assoc($products)){
        $productCode = $row5['product_code'];
        $basis_weight = $row5['basis_weight'];
        $width = $row5['width'];
        $diameter = $row5['diameter'];
        $class = $row5['class'];
    }
}

if(isset($_GET['weight']) && $_GET['weight'] != null && $_GET['weight'] != ''){
    $weight = (string)$_GET['weight'].' kg';
}

if(isset($_GET['date']) && $_GET['date'] != null && $_GET['date'] != ''){
    $dateAndTime = (string)$_GET['date'];
    list($datePart, $timePart) = explode(" ", $dateAndTime);
    list($year, $month, $day) = explode("-", $datePart);
    $date = "$day-$month-$year";
    $time = $timePart;
}
?>

<html>
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
                            <span style="font-size: 40px;font-family: sans-serif;font-weight: bold;">精牛原纸</span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:0px;">
                        <p>
                            <span style="font-size: 18px;font-family: sans-serif;">口 合格证</span><br>
                            <span style="font-size: 16px;font-family: sans-serif;">Quality Certificate</span><br>
                            <span style="font-size: 16px;font-family: sans-serif;">(<?=$serial ?>)</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 55%;border-top:3px;">
                        <p>
                            <span style="font-size: 16px;font-family: sans-serif;">Time : <?=$time ?></span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:3px;">
                        <p>
                            <span style="font-size: 16px;font-family: sans-serif;">DATE : <?=$date ?></span>
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
                            <span style="font-size: 20px;font-family: sans-serif;">产品编号 Product No.</span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:0px;">
                        <p>
                            <span style="font-size: 20px;font-family: sans-serif;">定量 BASIS WEIGHT C/M2</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 55%;border-top:3px;">
                        <img src="assets/barcode2.png" alt="Girl in a jacket" width="50%">
                        <p style="margin-top: 0px;">
                            <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$productCode ?></span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:3px;">
                        <p>
                            <span style="font-size: 24px;font-family: sans-serif;"><?=$basis_weight ?></span>
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
                            <span style="font-size: 22px;font-family: sans-serif;"><?=$width ?></span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:3px;">
                        <p>
                            <span style="font-size: 22px;font-family: sans-serif;"><?=$diameter ?></span>
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
                            <span style="font-size: 24px;font-family: sans-serif;">质量 WEIGHT KG</span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:0px;">
                        <p>
                            <span style="font-size: 24px;font-family: sans-serif;">产品等级 PRODUCT CLASS</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 55%;border-top:3px;">
                        <img src="assets/barcode2.png" alt="Girl in a jacket" width="50%">
                        <p style="margin-top: 0px;">
                            <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$weight ?></span>
                        </p>
                    </td>
                    <td style="width: 45%;border-top:3px;">
                        <p>
                            <span style="font-size: 24px;font-family: sans-serif;"><?=$class ?></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div id="print-button" onclick="window.print()">
            Print
        </div>
    </body>
</html>