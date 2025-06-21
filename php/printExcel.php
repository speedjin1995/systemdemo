<?php
session_start();
require_once 'db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';

$fileName = date('Y-m-d') . ".xls";
$message = '';
 
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

if(isset($_GET['userID'], $_GET["file"])){
    $stmt = $db->prepare("SELECT * FROM companies WHERE id=?");
    $stmt->bind_param('s', $compids);
    $stmt->execute();
    $result1 = $stmt->get_result();
            
    if ($row = $result1->fetch_assoc()) {
        $compname = $row['name'];
        $compreg = $row['company_reg_no'];
        $compaddress = $row['address'];
        $compphone = $row['phone'];
        $compiemail = $row['email'];
    }
    $id = $_GET['userID'];
    $empQuery = "select jobs.*, customers.customer_name, customers.customer_address, customers.customer_address2, 
    customers.customer_address3, customers.customer_address4, customers.customer_phone,users.name, jobs.status, jobs.created_datetime 
    from jobs, users, customers WHERE jobs.deleted = '0' AND users.id = jobs.pick_by AND customers.id = jobs.customer AND jobs.id=
    ".$id;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    if($row = mysqli_fetch_assoc($empRecords)) {
        $fileName = $row['job_no'] . ".xls";
        $items = array();

        if($row['id']!=null && $row['id']!=''){
            $id = $row['id'];

            if ($update_stmt = $db->prepare("SELECT job_details.*, products.product_name, products.basis_weight FROM job_details, products WHERE job_details.product = products.id AND job_details.job_id=?")) {
                $update_stmt->bind_param('s', $id);
                
                if ($update_stmt->execute()) {
                    $result = $update_stmt->get_result();

                    while ($row2 = $result->fetch_assoc()) {
                        $items2 = array();

                        if($row2['id']!=null && $row2['id']!=''){
                            $id2 = $row2['id'];
                        
                            if ($update_stmt2 = $db->prepare("SELECT * FROM weighing WHERE job_details_id=?")) {
                                $update_stmt2->bind_param('s', $id2);
                                
                                if ($update_stmt2->execute()) {
                                    $result2 = $update_stmt2->get_result();
                                    $items2 = array();
                            
                                    while ($row3 = $result2->fetch_assoc()) {
                                        $items2[] = array(
                                            "serial_no"=>$row3['serial_no'],
                                            "net"=>$row3['net']
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
                            "basis_weight"=>$row2['basis_weight'],
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
            "po_no" => $row['po_no'],
            "do_no" => $row['do_no'],
            "customer_name" => $row['customer_name'],
            "customer_address" => $row['customer_address'],
            "customer_address2" => $row['customer_address2'],
            "customer_address3" => $row['customer_address3'],
            "customer_address4" => $row['customer_address4'],
            "customer_phone" => $row['customer_phone'],
            "items" => $items
        );
    }

    if($data!=null && count($data) > 0){
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
                <div>
                    <div style="font-size: 14px; padding-left: 5px;">
                        <div>
                            <h1 style="display:inline">'.$compname.'('.$compreg.')</h1>
                        </div>
                        <div>'.$compaddress.'</div>
                        <div>T: '.$compphone.' &nbsp;&nbsp;&nbsp;&nbsp;F : 03-60573250  &nbsp;&nbsp;&nbsp;&nbsp;E : '.$compiemail.'</div>
                    </div>
                </div><br>
                <h2><u>Packing List</u></h2>
                <div>
                    <div style="font-size: 14px; padding-left: 5px;">
                        <div>
                            <h2 style="display:inline">'.$data[0]['customer_name'].'</h2>
                        </div>
                        <div>'.$data[0]['customer_address'].'</div>
                        <div>'.$data[0]['customer_address2'].'</div>
                        <div>'.$data[0]['customer_address3'].'</div>
                        <div>'.$data[0]['customer_address4'].'</div>
                        <div>TEL : '.$data[0]['customer_phone'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax : 03-77838633</div>
                    </div>
                </div><br>
                <div>
                    <div style="font-size: 14px; padding-left: 5px;">
                        <div><b>Delivery Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b> -</div>
                        <div><b>Product Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b> '.$data[0]['items'][0]['product_name'].'</div>
                        <div><b>Delivery Note No. &nbsp;&nbsp;:</b> '.$data[0]['do_no'].'</div>
                        <div><b>P/O No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b> '.$data[0]['po_no'].'</div>
                    </div>
                </div><br><br>
                <table>
                    <tbody>';

                    for($i=0; $i<count($data[0]['items']); $i++){
                        if($i % 2 == 0){
                            $message .= '<tr><td width="50%"><table>
                                    <thead>
                                        <tr>
                                            <th colspan="4"><u>'.$data[0]['items'][$i]['basis_weight'].'GSM - '.$data[0]['items'][$i]['width'].'MM</u></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>';

                                        $count = 1;
                                        $totalNet = 0.00;

                                        for($j=0; $j<count($data[0]['items'][$i]['weighing']); $j++){
                                            $message .= '<td>
                                                    Roll No '.$count.'
                                                </td>
                                                <td>
                                                    '.$data[0]['items'][$i]['weighing'][$j]['serial_no'].'
                                                </td>
                                                <td>
                                                    '.$data[0]['items'][$i]['weighing'][$j]['net'].'
                                                </td>
                                                <td>
                                                    kgs
                                                </td>
                                            </tr>';

                                            $totalNet += (float)$data[0]['items'][$i]['weighing'][$j]['net'];
                                            $count++;
                                        }
                                    $message .= '</tbody><tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">
                                            '.$totalNet.'
                                        </td>
                                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">
                                            kgs
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>';

                            $message .= '</td>';
                        }
                        else{
                            $message .= '<td width="50%"><table>
                                    <thead>
                                        <tr>
                                            <th colspan="4"><u>'.$data[0]['items'][$i]['basis_weight'].'GSM - '.$data[0]['items'][$i]['width'].'MM</u></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>';

                                        $count = 1;
                                        $totalNet = 0.00;

                                        for($j=0; $j<count($data[0]['items'][$i]['weighing']); $j++){
                                            $message .= '<td>
                                                    Roll No '.$count.'
                                                </td>
                                                <td>
                                                    '.$data[0]['items'][$i]['weighing'][$j]['serial_no'].'
                                                </td>
                                                <td>
                                                    '.$data[0]['items'][$i]['weighing'][$j]['net'].'
                                                </td>
                                                <td>
                                                    kgs
                                                </td>
                                            </tr>';

                                            $totalNet += (float)$data[0]['items'][$i]['weighing'][$j]['net'];
                                            $count++;
                                        }
                                    $message .= '</tbody><tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">
                                            '.$totalNet.'
                                        </td>
                                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">
                                            kgs
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>';

                            $message .= '</td></tr>';
                        }
                    }
                      
                    if(count($data[0]['items']) % 2 != 0){
                        $message .= '<td></td></tr>';
                    }
                        
                    $message .= '</tbody>
                </table>
            </body>
        </html>';
    }
    else{
        $message = 'No records found...'. "\n"; 
    }
}
else{
    $message = 'No records found...'. "\n"; 
}

// Headers for download 
header("Content-Type: application/vnd.ms-excel; charset=utf-8"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 

// Render excel data 
// $str = utf8_decode($excelData);
echo $message; 
 
exit;
?>