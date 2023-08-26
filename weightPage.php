<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $stmt = $db->prepare("SELECT * from users where id = ?");
	$stmt->bind_param('s', $user);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if(($row = $result->fetch_assoc()) !== null){
    $role = $row['role_code'];
  }

  $lots = $db->query("SELECT * FROM lots WHERE deleted = '0'");
  $vehicles = $db->query("SELECT * FROM vehicles WHERE deleted = '0'");
  $products = $db->query("SELECT * FROM products WHERE deleted = '0'");
  $packages = $db->query("SELECT * FROM packages WHERE deleted = '0'");
  $customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");
  $suppliers = $db->query("SELECT * FROM supplies WHERE deleted = '0'");
  $units = $db->query("SELECT * FROM units WHERE deleted = '0'");
  $status = $db->query("SELECT * FROM `status` WHERE deleted = '0'");
  $transporters = $db->query("SELECT * FROM `transporters` WHERE deleted = '0'");
}
?>

<style>
  @media screen and (min-width: 676px) {
    .modal-dialog {
      max-width: 1800px; /* New width for default modal */
    }
  }
</style>

<select class="form-control" style="width: 100%;" id="customerNoHidden" style="display: none;">
  <option value="" selected disabled hidden>Please Select</option>
  <?php while($rowCustomer=mysqli_fetch_assoc($customers)){ ?>
    <option value="<?=$rowCustomer['id'] ?>"><?=$rowCustomer['customer_name'] ?></option>
  <?php } ?>
</select>

<select class="form-control" style="width: 100%;" id="supplierNoHidden" style="display: none;">
  <option value="" selected disabled hidden>Please Select</option>
  <?php while($rowCustomer=mysqli_fetch_assoc($suppliers)){ ?>
    <option value="<?=$rowCustomer['id'] ?>"><?=$rowCustomer['customer_name'] ?></option>
  <?php } ?>
</select>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Weight Weighing</h1>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
  <div class="container-fluid">
    <!--div div class="row">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" id="saleCard">
          <span class="info-box-icon bg-info">
            <i class="fas fa-shopping-cart"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Sales</span>
            <span class="info-box-number" id="salesInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" id="purchaseCard">
          <span class="info-box-icon bg-success">
            <i class="fas fa-shopping-basket"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Purchase</span>
            <span class="info-box-number" id="purchaseInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" id="miscCard">
          <span class="info-box-icon bg-warning">
            <i class="fas fa-warehouse" style="color: white;"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Miscellaneous</span>
            <span class="info-box-number" id="localInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 col-12">
        <div class="input-group-text color-palette" id="indicatorConnected"><i>Indicator Connected</i></div>
        <div class="input-group-text bg-danger color-palette" id="checkingConnection"><i>Checking Connection</i></div>
      </div>
    </div-->

    <div class="row">

      <!-- <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-4">
                <div class="input-group-text color-palette" id="indicatorConnected"><i>Indicator Connected</i></div>
              </div>
              <div class="col-4">
                <div class="input-group-text bg-danger color-palette" id="checkingConnection"><i>Checking Connection</i></div>
              </div>
              <div class="col-4">
                <button type="button" class="btn btn-block bg-gradient-primary"  onclick="setup()">
                  Setup
                </button>
              </div>
            </div>
          </div>
        </div>
      </div> -->

      <div class="col-lg-12">
        <div class="card card-primary">
          <div class="card-header">
            <!--div class="row">
              <div class="col-6"></div>
              <div class="col-3">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="refreshBtn">Refresh</button>
              </div>
              <div class="col-3">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" onclick="newEntry()">Add New Weight</button>
              </div>
            </div-->
          </div>

          <div class="card-body">
            <table id="weightTable" class="table table-bordered table-striped display">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Serial No</th>
                  <th>Group No</th>
                  <th>Customers</th>
                  <th>Suppliers</th>
                  <th>Product</th>
                  <th>Vehicle No</th>
                  <th>Driver Name</th>
                  <th>Farm Id</th>
                  <th></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--div class="modal fade" id="extendModal">
  <div class="modal-dialog modal-xl" style="max-width: 90%;">
    <div class="modal-content">
      <form role="form" id="extendForm">
        <div class="modal-header bg-gray-dark color-palette">
          <h4 class="modal-title">Add New Entry</h4>
          <button type="button" class="close bg-gray-dark color-palette" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body" >
          <div class="row">
            <div class="col-md-3">
              <div class="d-flex">
                <div class="small-box bg-success">
                  <div class="inner">
                  <h3 style="text-align: center; font-size: 100px" id="indicatorWeight">0.00kg</h3>
                  </div>
                </div>
              </div>      
            </div>
            
            <div class="row col-md-9">
              <div class="row col-md-12">
                <div class="col-2">
                  <input type="hidden" class="form-control" id="id" name="id">
                  <div class="form-group">
                    <label>Serial No.</label>
                    <input class="form-control" type="text" placeholder="Serial No" id="serialNumber" name="serialNumber" readonly>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label>Status *</label>
                    <select class="form-control" style="width: 100%;" id="status" name="status" required>
                      <option selected="selected">-</option>
                      <?php while($rowS=mysqli_fetch_assoc($status)){ ?>
                        <option value="<?=$rowS['id'] ?>"><?=$rowS['status'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-group col-md-2">
                  <label>Invoice No</label>
                  <input class="form-control" type="text" placeholder="Invoice No" id="invoiceNo" name="invoiceNo" >
                </div>

                <div class="form-group col-md-2">
                  <label>Delivery No</label>
                  <input class="form-control" type="text" placeholder="Delivery No" id="deliveryNo" name="deliveryNo" >
                </div>

                <div class="form-group col-md-2">
                  <label>Purchase Order</label>
                  <input class="form-control" type="text" placeholder="Purchase No" id="purchaseNo" name="purchaseNo" >
                </div>

                <div class="form-group col-md-2">
                  <label class="labelOrder">Order Weight</label>
                  <div class="input-group">
                    <input class="form-control" type="number" id="supplyWeight" name="supplyWeight"/>
                    <div class="input-group-text bg-success color-palette"><i id="changeSupplyWeight">KG/G</i></div>
                  </div>
                </div>
              </div>

              <div class="row col-md-12">
                <div class="col-2">
                  <div class="form-group">
                    <label>Date / Time</label>
                      <div class='input-group date' id="datePicker" data-target-input="nearest">
                        <input type='text' class="form-control datetimepicker-input" data-target="#datePicker" id="dateTime" name="dateTime" required/>
                        <div class="input-group-append" data-target="#datePicker" data-toggle="datetimepicker">
                          <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                      </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label>Unit Weight *</label>
                    <select class="form-control" style="width: 100%;" id="unitWeight" name="unitWeight" required> 
                      <option selected="selected">-</option>
                      <?php while($rowunits=mysqli_fetch_assoc($units)){ ?>
                        <option value="<?=$rowunits['id'] ?>"><?=$rowunits['units'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>      

                <div class="col-md-2">
                  <div class="form-group">
                    <label>Package *</label>
                    <select class="form-control" style="width: 100%;" id="package" name="package" required>
                      <option selected="selected">-</option>
                      <?php while($row6=mysqli_fetch_assoc($packages)){ ?>
                        <option value="<?=$row6['id'] ?>"><?=$row6['packages'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-group col-md-2">
                  <label>Batch No</label>
                  <input class="form-control" type="text" placeholder="Batch No" id="batchNo" name="batchNo" >
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label>Lot No </label>
                    <select class="form-control" style="width: 100%;" id="lotNo" name="lotNo">
                      <option selected="selected">-</option>
                      <?php while($row3=mysqli_fetch_assoc($lots)){ ?>
                        <option value="<?=$row3['lots_no'] ?>"><?=$row3['lots_no'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-group col-md-2">
                    <label style="color:red;">Variance Weight</label>
                    <div class="input-group">
                      <input class="form-control" type="text" placeholder="Variance Weight" id="varianceWeight" name="varianceWeight" readonly/>
                      <div class="input-group-text bg-success color-palette"><i id="changeWeightVariance">KG/G</i></div>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="labelStatus">Customer No *</label>
                <select class="form-control" id="customerNo" name="customerNo" required></select>
                <input class="form-control" type="text" placeholder="Description" id="customerNoTxt" name="customerNoTxt" hidden>
              </div>
            </div>

            <div class="row col-md-8">
              <div class="row col-md-12">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Product *</label>
                    <select class="form-control" style="width: 100%;" id="product" name="product" required>
                      <option selected="selected">-</option>
                      <?php while($row5=mysqli_fetch_assoc($products)){ ?>
                        <option value="<?=$row5['id'] ?>"><?=$row5['product_name'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-group" hidden>
                  <label>M.O.Q *</label>
                  <input class="form-control" type="number" placeholder="moq" id="moq" name="moq" min="0">
                </div>

                <div class="form-group col-md-3">
                  <label>Transporter</label>
                  <select class="form-control" style="width: 100%;" id="transporter" name="transporter">
                      <option selected="selected">-</option>
                      <?php while($row5=mysqli_fetch_assoc($transporters)){ ?>
                        <option value="<?=$row5['transporter_name'] ?>"><?=$row5['transporter_name'] ?></option>
                      <?php } ?>
                  </select>
                </div>

                <div class="form-group col-md-2">
                  <label>Unit Price</label>
                  <div class="input-group">
                    <div class="input-group-text"><i>RM</i></div>
                    <input class="form-control money" type="number" placeholder="unitPrice" id="unitPrice" name="unitPrice" min="0" required/>                        
                  </div>
                </div>

                <div class="form-group col-md-2">
                  <label>Total Price</label>
                  <div class="input-group">
                    <div class="input-group-text"><i>RM</i></div>
                    <input class="form-control money" type="number" placeholder="Total Price"  id="totalPrice" name="totalPrice" readonly required/>                        
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <input type="hidden" id="outGDateTime" name="outGDateTime">
            <input type="hidden" id="inCDateTime" name="inCDateTime">

            <div class="col-md-2">
              <div class="form-group">
                <label>
                  Vehicle No
                  <span style="padding-left: 80px;"><input type="checkbox" class="form-check-input" id="manualVehicle" name="manualVehicle" value="0"/>Manual</span>
                </label>

                <select class="form-control" id="vehicleNo" name="vehicleNo">
                  <option selected="selected">-</option>
                  <?php while($row2=mysqli_fetch_assoc($vehicles)){ ?>
                    <option value="<?=$row2['veh_number'] ?>" data-weight="<?=$row2['vehicleWeight'] ?>"><?=$row2['veh_number'] ?></option>
                  <?php } ?>
                </select>

                <input class="form-control" type="text" placeholder="Vehicle No." id="vehicleNoTct" name="vehicleNoTxt" hidden>
              </div>
            </div>

            <div class="form-group col-md-2">
              <label>Incoming - G.W *
              <?php 
                if($role == "ADMIN"){         
                  echo '<span style="padding-left: 60px;"><input type="checkbox" class="form-check-input" id="manual" name="manual" value="0"/>Manual</span>';
                }
              ?>
              </label>
              <div class="input-group">
                <input class="form-control" type="number" placeholder="Current Weight" id="currentWeight" name="currentWeight" readonly required/>
                <div class="input-group-text bg-primary color-palette"><i id="changeWeight">KG/G</i></div>
                <button type="button" class="btn btn-primary" id="inCButton"><i class="fas fa-sync"></i></button>
              </div>
            </div> 

            <div class="form-group col-md-2 hidOutgoing">
              <label>Outgoing - T.W *
                <span style="padding-left: 70px;"><input type="checkbox" class="form-check-input" id="manualOutgoing" name="manualOutgoing" value="0"/>Manual</span>
              </label>
              <div class="input-group">
                <input class="form-control" type="number" placeholder="Tare Weight" id="tareWeight" name="tareWeight" min="0" readonly/>
                <div class="input-group-text bg-primary color-palette"><i id="changeWeightTare">KG/G</i></div>
                <button type="button" class="btn btn-primary" id="outGButton"><i class="fas fa-sync"></i></button>
              </div>
            </div>
              
            <div class="row col-md-6">
              <div class="row col-md-12">
                <div class="form-group col-md-3">
                  <label>Reduce Weight</label>
                  <div class="input-group">
                    <input class="form-control" type="number" placeholder="Reduce Weight" id="reduceWeight" name="reduceWeight" min="0"/>
                    <div class="input-group-text bg-danger color-palette"><i id="changeReduceWeight">KG/G</i></div>
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label>Sub Nett Weight</label>
                  <div class="input-group">
                    <input class="form-control" type="number" placeholder="Actual Weight" id="actualWeight" name="actualWeight" readonly required/>
                    <div class="input-group-text bg-success color-palette"><i id="changeWeightActual">KG/G</i></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Remark</label>
                    <textarea class="form-control" rows="1" placeholder="Enter ..." id="remark" name="remark"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group col-md-3" hidden>
            <label>Total Weight</label>
            <div class="input-group">
              <input class="form-control" type="number" placeholder="Total Weight" id="totalWeight" name="totalWeight" readonly required/>
              <div class="input-group-text bg-success color-palette"><i id="changeWeightTotal">KG/G</i></div>
            </div>
          </div>

          <input type="hidden" id="pStatus" name="pStatus">
          <input type="hidden" id="variancePerc" name="variancePerc">
        </div>

        <div class="modal-footer justify-content-between bg-gray-dark color-palette">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="saveButton">Save changes</button>
        </div>
      </form>
    </div> <!-- /.modal-content >
  </div> <!-- /.modal-dialog >
</div> <!-- /.modal -->     

<script>
// Values
var controlflow = "None";
var indicatorUnit = "kg";
var weightUnit = "1";
var rate = 1;
var currency = "1";

$(function () {
  $('#customerNoHidden').hide();
  $('#supplierNoHidden').hide();

  var table = $("#weightTable").DataTable({
    "responsive": true,
    "autoWidth": false,
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'searching': true,
    'order': [[ 1, 'asc' ]],
    'columnDefs': [ { orderable: false, targets: [0] }],
    'ajax': {
        'url':'php/loadWeights.php'
    },
    'columns': [
      { data: 'no' },
      { data: 'serial_no' },
      { data: 'group_no' },
      { data: 'customer' },
      { data: 'supplier' },
      { data: 'product' },
      { data: 'lorry_no' },
      { data: 'driver_name' },
      { data: 'farm_id' },
      { 
        className: 'dt-control',
        orderable: false,
        data: null,
        render: function ( data, type, row ) {
          return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
        }
      }
    ],
    "rowCallback": function( row, data, index ) {
      $('td', row).css('background-color', '#E6E6FA');
    },
    "drawCallback": function(settings) {
      
    }
  });

  // Add event listener for opening and closing details
  $('#weightTable tbody').on('click', 'td.dt-control', function () {
    var tr = $(this).closest('tr');
    var row = table.row( tr );

    if ( row.child.isShown() ) {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
    }
    else {
      // Open this row
      <?php 
        if($role == "ADMIN"){
          echo 'row.child( format(row.data()) ).show();tr.addClass("shown");';
        }
        else{
          echo 'row.child( formatNormal(row.data()) ).show();tr.addClass("shown");';
        }
      ?>
    }
  });
  
  //Date picker
  $('#fromDate').datetimepicker({
      icons: { time: 'far fa-clock' },
      format: 'DD/MM/YYYY hh:mm:ss A'
  });

  $('#toDate').datetimepicker({
      icons: { time: 'far fa-clock' },
      format: 'DD/MM/YYYY hh:mm:ss A'
  });

  /*$.post('http://127.0.0.1:5002/', $('#setupForm').serialize(), function(data){
    if(data == "true"){
      $('#indicatorConnected').addClass('bg-primary');
      $('#checkingConnection').removeClass('bg-danger');
      //$('#captureWeight').removeAttr('disabled');
    }
    else{
      $('#indicatorConnected').removeClass('bg-primary');
      $('#checkingConnection').addClass('bg-danger');
      //$('#captureWeight').attr('disabled', true);
    }
  });
  
  setInterval(function () {
    $.post('http://127.0.0.1:5002/handshaking', function(data){
      if(data != "Error"){
        console.log("Data Received:" + data);
        var text = data.split(" ");
        $('#indicatorWeight').html(text[text.length - 1]);
        $('#indicatorConnected').addClass('bg-primary');
        $('#checkingConnection').removeClass('bg-danger');
      }
      else{
        $('#indicatorConnected').removeClass('bg-primary');
        $('#checkingConnection').addClass('bg-danger');
      }
    });
  }, 500);*/

  $.validator.setDefaults({
    submitHandler: function () {
        if($('#extendModal').hasClass('show')){
          $('#spinnerLoading').show();

            var convert1 = $('#extendModal').find('#dateTime').val().replace(", ", " ");
            convert1 = convert1.replace(":", "/");
            convert1 = convert1.replace(":", "/");
            convert1 = convert1.replace(" ", "/");
            convert1 = convert1.replace(" pm", "");
            convert1 = convert1.replace(" am", "");
            convert1 = convert1.replace(" PM", "");
            convert1 = convert1.replace(" AM", "");
            var convert2 = convert1.split("/");
            var date  = new Date(convert2[2], convert2[1] - 1, convert2[0], convert2[3], convert2[4], convert2[5]);
            $('#extendModal').find('#dateTime').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds());

        $.post('php/insertWeight.php', $('#extendForm').serialize(), function(data){
          var obj = JSON.parse(data); 
          if(obj.status === 'success'){
            $('#extendModal').modal('hide');
            toastr["success"](obj.message, "Success:");
            $('#weightTable').DataTable().ajax.reload();
          }
          else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
          }
          else{
            toastr["error"]("Something wrong when edit", "Failed:");
          }

          $('#spinnerLoading').hide();
        });
      }
      /*else if ($('#setupModal').hasClass('show')){
        $.post('http://127.0.0.1:5002/', $('#setupForm').serialize(), function(data){
          if(data == "true"){
            $('#indicatorConnected').addClass('bg-primary');
            $('#checkingConnection').removeClass('bg-danger');
            $('#captureWeight').removeAttr('disabled');
          }
          else{
            $('#indicatorConnected').removeClass('bg-primary');
            $('#checkingConnection').addClass('bg-danger');
            $('#captureWeight').attr('disabled', true);
          }
        });
        
        $('#setupModal').modal('hide');
      }*/
    }
  });

  $('#refreshBtn').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        $('#salesInfo').text(settings.json.salesTotal);
        $('#purchaseInfo').text(settings.json.purchaseTotal);
        $('#localInfo').text(settings.json.localTotal);
      }
    });
  });

  $('#saleCard').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '1';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      }
    });
  });

  $('#purchaseCard').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '2';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      }
    });
  });

  $('#miscCard').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '3';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      }
    });
  });
});

function updatePrices(isFromCurrency, rat){
  var totalPrice;
  var unitPrice = $('#unitPrice').val();
  var totalWeight = $('#totalWeight').val();

  if(isFromCurrency == 'Y'){
    unitPrice = (unitPrice / rate) * parseFloat(rat);
    $('#extendModal').find('#unitPrice').val(unitPrice.toFixed(2));
    rate = parseFloat(rat).toFixed(2);
  }
  else{
    unitPrice = unitPrice * parseFloat(rat);
    $('#extendModal').find('#unitPrice').val(unitPrice.toFixed(2));
    rate = parseFloat(rat).toFixed(2);
  }
  

  if(unitPrice != '' &&  moq != '' && totalWeight != ''){
    totalPrice = unitPrice * totalWeight;
    $('#totalPrice').val(totalPrice.toFixed(2));
  }
  else(
    $('#totalPrice').val((0).toFixed(2))
  )
}

function updateWeights(){
  var tareWeight =  0;
  var currentWeight =  0;
  var reduceWeight = 0;
  var moq = $('#moq').val();
  var totalWeight = 0;
  var actualWeight = 0;

  if($('#currentWeight').val()){
    currentWeight =  $('#currentWeight').val();
  }

  if($('#tareWeight').val()){
    tareWeight =  $('#tareWeight').val();
  }

  if($('#reduceWeight').val()){
    reduceWeight =  $('#reduceWeight').val();
  }

  if(tareWeight == 0){
    actualWeight = currentWeight - reduceWeight;
    actualWeight = Math.abs(actualWeight);
    $('#actualWeight').val(actualWeight.toFixed(2));
  }
  else{
    actualWeight = tareWeight - currentWeight - reduceWeight;
    actualWeight = Math.abs(actualWeight);
    $('#actualWeight').val(actualWeight.toFixed(2));
  }

  if(actualWeight != '' &&  moq != ''){
    totalWeight = actualWeight * moq;
    $('#totalWeight').val(totalWeight.toFixed(2));
  }
  else{
    $('#totalWeight').val((0).toFixed(2))
  };
}

function format (row) {
  return '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div><div class="row"><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div></div></div></div>'+
  '</div>';
}

function formatNormal (row) {
  return '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div><div class="row"><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div></div></div></div>'+
  '</div>';
}

function newEntry(){
  var date = new Date();
  $('#extendModal').find('#id').val("");
  $('#extendModal').find('#serialNumber').val("");
  $('#extendModal').find('#unitWeight').val('');
  $('#extendModal').find('#invoiceNo').val("");
  $('#extendModal').find('#status').val('');
  $('#extendModal').find('#lotNo').val('');
  $('#extendModal').find('#vehicleNo').val('');
  $('#extendModal').find('#customerNo').val('');
  $('#extendModal').find('#deliveryNo').val("");
  $('#extendModal').find('#batchNo').val("");
  $('#extendModal').find('#purchaseNo').val("");
  $('#extendModal').find('#currentWeight').val("");
  $('#extendModal').find('#product').val('');
  $('#extendModal').find('#transporter').val('');
  $('#extendModal').find('#moq').val("1");
  $('#extendModal').find('#currency').val("1");
  $('#extendModal').find('#tareWeight').val("0.00");
  $('#extendModal').find('#package').val('');
  $('#extendModal').find('#actualWeight').val("");
  $('#extendModal').find('#supplyWeight').val("");
  $('#extendModal').find('#varianceWeight').val("");
  $('#extendModal').find('#remark').val("");
  $('#extendModal').find('#totalPrice').val("");
  $('#extendModal').find('#unitPrice').val("");
  $('#extendModal').find('#totalWeight').val("");
  $('#extendModal').find('#manual').prop('checked', false);
  $('#extendModal').find('#manualVehicle').prop('checked', false);
  $('#extendModal').find('#manualOutgoing').prop('checked', false);
  $('#extendModal').find('#vehicleNoTct').val("");
  $('#extendModal').find('#vehicleNo').removeAttr('hidden');
  $('#extendModal').find('#vehicleNoTct').attr('hidden', 'hidden');
  // $('#extendModal').find('.hidOutgoing').attr('hidden', 'hidden');
  $('#extendModal').find('#currentWeight').attr('readonly', true);
  $('#extendModal').find('#tareWeight').attr('readonly', true);
  $('#extendModal').find('#reduceWeight').val("");
  $('#extendModal').find('#outGDateTime').val("");
  $('#extendModal').find('#inCDateTime').val("");
  $('#extendModal').find('#pStatus').val("");
  $('#extendModal').find('#variancePerc').val("");
  $('#datePicker').datetimepicker({
      icons: { time: 'far fa-clock' },
      format: 'DD/MM/YYYY HH:mm:ss A'
  });
  $('#extendModal').find('#dateTime').val(date.toLocaleString('en-AU', { hour12: false }));
  $('#extendModal').modal('show');
  
  $('#extendForm').validate({
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass('is-invalid');
    }
  });
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function edit(id) {
  $('#spinnerLoading').show();
  $.post('php/getWeights.php', {userID: id}, function(data){
    var obj = JSON.parse(data);
    
    if(obj.status === 'success'){
      $('#extendModal').find('#id').val(obj.message.id);
      $('#extendModal').find('#serialNumber').val(obj.message.serialNo);
      $('#extendModal').find('#unitWeight').val(obj.message.unitWeight);
      $('#extendModal').find('#invoiceNo').val(obj.message.invoiceNo);
      $('#extendModal').find('#status').val(obj.message.status);
      $('#extendModal').find('#lotNo').val(obj.message.lotNo);
      $('#extendModal').find('#deliveryNo').val(obj.message.deliveryNo);
      $('#extendModal').find('#batchNo').val(obj.message.batchNo);
      $('#extendModal').find('#purchaseNo').val(obj.message.purchaseNo);
      $('#extendModal').find('#currentWeight').val(obj.message.currentWeight);
      $('#extendModal').find('#product').val(obj.message.productName);
      $('#extendModal').find('#moq').val(obj.message.moq);
      $('#extendModal').find('#currency').val(obj.message.currency);
      $('#extendModal').find('#transporter').val(obj.message.transporter);
      $('#extendModal').find('#tareWeight').val(obj.message.tare);
      $('#extendModal').find('#package').val(obj.message.package);
      $('#extendModal').find('#actualWeight').val(obj.message.actualWeight);
      $('#extendModal').find('#supplyWeight').val(obj.message.supplyWeight);
      $('#extendModal').find('#varianceWeight').val(obj.message.varianceWeight);
      $('#extendModal').find('#remark').val(obj.message.remark);
      $('#extendModal').find('#totalPrice').val(obj.message.totalPrice);
      $('#extendModal').find('#unitPrice').val(obj.message.unitPrice);
      $('#extendModal').find('#totalWeight').val(obj.message.totalWeight);
      $('#extendModal').find('#reduceWeight').val(obj.message.reduceWeight);
      $('#extendModal').find('#pStatus').val(obj.message.pStatus);
      $('#extendModal').find('#outGDateTime').val(obj.message.outGDateTime);
      $('#extendModal').find('#inCDateTime').val(obj.message.inCDateTime);
      $('#extendModal').find('#variancePerc').val(obj.message.variancePerc);

      $('#extendModal').find('#toDatePicker').datetimepicker({
        icons: { time: 'far fa-clock' },
        format: 'DD/MM/YYYY HH:mm:ss A'
      });

      $('#extendModal').find('#dateTime').val(obj.message.dateTime);
    
      if($('#extendModal').find('#status').val() == '1'){
        $('#extendModal').find('#customerNo').html($('select#customerNoHidden').html()).append($('#extendModal').find('#status').val());
        $('#extendModal').find('.labelStatus').text('Customer No');
        $('#extendModal').find('.labelOrder').text('Order Weight');
        $('#extendModal').find('#customerNo').val(obj.message.customer);
        
      }
      else if($('#extendModal').find('#status').val() == '2'){
        $('#extendModal').find('#customerNo').html($('select#supplierNoHidden').html()).append($('#extendModal').find('#status').val());
        $('#extendModal').find('.labelStatus').text('Supplier No');
        $('#extendModal').find('.labelOrder').text('Supply Weight');
        $('#extendModal').find('#customerNo').val(obj.message.customer);
      }

      if(obj.message.manualVehicle === 1){
        $('#extendModal').find('#manualVehicle').prop('checked', true);
        $('#extendModal').find('#vehicleNoTct').removeAttr('hidden');
        $('#extendModal').find('#vehicleNo').attr('hidden', 'hidden');
        $('#extendModal').find('#vehicleNoTct').val(obj.message.vehicleNo);
      }
      else{
        $('#extendModal').find('#manualVehicle').prop('checked', false);
        $('#extendModal').find('#vehicleNo').removeAttr('hidden');
        $('#extendModal').find('#vehicleNoTct').attr('hidden', 'hidden');
        $('#extendModal').find('#vehicleNo').val(obj.message.vehicleNo);
      }

            ///still need do some changes
      if(obj.message.manual === 1){
        $('#extendModal').find('#manual').prop('checked', true);
        $('#extendModal').find('#currentWeight').attr('readonly', false);
      }

      if(obj.message.manualOutgoing === 1){
        $('#extendModal').find('#manualOutgoing').prop('checked', true);
        $('#extendModal').find('#tareWeight').attr('readonly', false);
      }

      $('#extendModal').modal('show');
      $('#extendForm').validate({
        errorElement: 'span',
        errorPlacement: function (error, element) {
          error.addClass('invalid-feedback');
          element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
          $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
          $(element).removeClass('is-invalid');
        }
      });
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when pull data", "Failed:");
    }
    $('#spinnerLoading').hide();
  });
}

function deactivate(id) {
  if (confirm('Are you sure you want to delete this items?')) {
    $('#spinnerLoading').show();
    $.post('php/deleteWeight.php', {userID: id}, function(data){
      var obj = JSON.parse(data);

      if(obj.status === 'success'){
        toastr["success"](obj.message, "Success:");
        $('#weightTable').DataTable().ajax.reload();
        /*$.get('weightPage.php', function(data) {
          $('#mainContents').html(data);
        });*/
      }
      else if(obj.status === 'failed'){
        toastr["error"](obj.message, "Failed:");
      }
      else{
        toastr["error"]("Something wrong when activate", "Failed:");
      }
      $('#spinnerLoading').hide();
    });
  }
}

function print(id) {
  $.post('php/print.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 500);

      /*$.get('weightPage.php', function(data) {
        $('#mainContents').html(data);
      });*/
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}

function portrait(id) {
  $.post('php/printportrait.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 500);
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}
</script>