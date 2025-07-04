<?php
session_start();
require_once 'php/db_connect.php';

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.php";</script>';
}
else{
  $user = $_SESSION['userID'];
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Customers</h1>
			</div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
        <div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header" style="background-color: #1360a8;">
                        <div class="row">
                            <div class="col-9"></div>
                            <div class="col-3">
                                <button type="button" class="btn btn-block btn-sm" id="addCustomers" style="background-color: #75c251;">
                                    <i class="fas fa-plus"></i>
                                    Add Customers
                                </button>
                            </div>
                        </div>
                    </div>
					<div class="card-body">
						<table id="customerTable" class="table table-bordered table-striped">
							<thead style="background-color: #1360a8;">
								<tr>
                                    <th>Code</th>
									<th>Name</th>
									<th>Address</th>
									<th>Phone</th>
									<th>PIC</th>
									<th>Actions</th>
								</tr>
							</thead>
						</table>
					</div><!-- /.card-body -->
				</div><!-- /.card -->
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->

<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="customerForm">
            <div class="modal-header">
              <h4 class="modal-title">Add Customers</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="card-body">
                <div class="form-group">
                  <input type="hidden" class="form-control" id="id" name="id">
                </div>
                <div class="form-group">
                  <label for="code">Customer Code *</label>
                  <input type="text" class="form-control" name="code" id="code" placeholder="Enter Customer Code" maxlength="10" required>
                </div>
                <div class="form-group">
                  <label for="name">Customer Name *</label>
                  <input type="text" class="form-control" name="name" id="name" placeholder="Enter Customer Name" required>
                </div>
                <div class="form-group"> 
                  <label for="address">Address *</label>
                  <input type="text" class="form-control" name="address" id="address" placeholder="Enter  Address" required>
                <div class="form-group"> 
                  <label for="address">Address 2</label>
                  <input type="text" class="form-control" name="address2" id="address2" placeholder="Enter  Address">
                </div>
                <div class="form-group"> 
                  <label for="address">Address 3</label>
                  <input type="text" class="form-control" name="address3" id="address3" placeholder="Enter  Address">
                </div>
                <div class="form-group"> 
                  <label for="address">Address 4</label>
                  <input type="text" class="form-control" name="address4" id="address4" placeholder="Enter  Address">
                </div>
                <div class="form-group"> 
                  <label for="address">Country</label>
                  <input type="text" class="form-control" name="country" id="country" placeholder="Enter Country">
                </div>
                <div class="form-group">
                  <label for="phone">Phone *</label>
                  <input type="text" class="form-control" name="phone" id="phone" placeholder="01x-xxxxxxx" required>
                </div>
                <div class="form-group"> 
                  <label for="email">PIC *</label>
                  <input type="text" class="form-control" id="email" name="email" placeholder="Enter your PIC" required>
                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" name="submit" id="submitMember">Submit</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
$(function () {
    $("#customerTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url':'php/loadCustomers.php'
        },
        'columns': [
            { data: 'customer_code' },
            { data: 'customer_name' },
            { data: 'customer_address' },
            { data: 'customer_phone' },
            { data: 'pic' },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="row"><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                }
            }
        ],
        "rowCallback": function( row, data, index ) {

            //$('td', row).css('background-color', '#E6E6FA');
        },        
    });
    
    $.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();
            $.post('php/customers.php', $('#customerForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#addModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#customerTable').DataTable().ajax.reload();
                    $('#spinnerLoading').hide();
                }
                else if(obj.status === 'failed'){
                    toastr["error"](obj.message, "Failed:");
                    $('#spinnerLoading').hide();
                }
                else{
                    toastr["error"]("Something wrong when edit", "Failed:");
                    $('#spinnerLoading').hide();
                }
            });
        }
    });

    $('#addCustomers').on('click', function(){
        $('#addModal').find('#id').val("");
        $('#addModal').find('#code').val("");
        $('#addModal').find('#name').val("");
        $('#addModal').find('#address').val("");
        $('#addModal').find('#address2').val("");
        $('#addModal').find('#address3').val("");
        $('#addModal').find('#address4').val("");
        $('#addModal').find('#country').val("")
        $('#addModal').find('#phone').val("");
        $('#addModal').find('#email').val("");
        $('#addModal').modal('show');
        
        $('#customerForm').validate({
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
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getCustomer.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#addModal').find('#id').val(obj.message.id);
            $('#addModal').find('#code').val(obj.message.customer_code);
            $('#addModal').find('#name').val(obj.message.customer_name);
            $('#addModal').find('#address').val(obj.message.customer_address);
            $('#addModal').find('#address2').val(obj.message.customer_address2);
            $('#addModal').find('#address3').val(obj.message.customer_address3);
            $('#addModal').find('#address4').val(obj.message.customer_address4);
            $('#addModal').find('#country').val(obj.message.country);
            $('#addModal').find('#phone').val(obj.message.customer_phone);
            $('#addModal').find('#email').val(obj.message.pic);
            $('#addModal').modal('show');
            
            $('#customerForm').validate({
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
            toastr["error"]("Something wrong when activate", "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id){
    if (confirm('Are you sure you want to delete this items?')) {
        $('#spinnerLoading').show();
        $.post('php/deleteCustomer.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                toastr["success"](obj.message, "Success:");
                $('#customerTable').DataTable().ajax.reload();
                $('#spinnerLoading').hide();
            }
            else if(obj.status === 'failed'){
                toastr["error"](obj.message, "Failed:");
                $('#spinnerLoading').hide();
            }
            else{
                toastr["error"]("Something wrong when activate", "Failed:");
                $('#spinnerLoading').hide();
            }
        });
    }
}
</script>