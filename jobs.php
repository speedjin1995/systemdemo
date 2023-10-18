<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $products = $db->query("SELECT * FROM products WHERE deleted = '0'");
  $customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");
  $users = $db->query("SELECT * FROM `users` WHERE deleted = '0'");
}
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Job Monitoring</h1>
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
					<div class="card-header">
            <div class="row">
              <div class="col-9"></div>
              <div class="col-3">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="addProducts">Add Jobs</button>
              </div>
            </div>
          </div>
					<div class="card-body">
						<table id="productTable" class="table table-bordered table-striped">
							<thead>
								<tr>
                  <th>Job No.</th>
                  <th>Customer</th>
                  <th>Picked By</th>
                  <th>Status</th>
									<th>Actions</th>
                  <th></th>
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
        <form role="form" id="productForm">
            <div class="modal-header">
              <h4 class="modal-title">Add Jobs</h4>
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
                  <label for="customers">Customers *</label>
                  <select class="form-control" id="customers" name="customers" style="width: 100%;">
                    <option selected="selected">-</option>
                    <?php while($rowCustomer=mysqli_fetch_assoc($customers)){ ?>
                      <option value="<?=$rowCustomer['id'] ?>"><?=$rowCustomer['customer_name'] ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group"> 
                  <label for="remark">Picked By *</label>
                  <select class="form-control" id="pickedBy" name="pickedBy" style="width: 100%;">
                    <option selected="selected">-</option>
                    <?php while($rowUsers=mysqli_fetch_assoc($users)){ ?>
                        <option value="<?=$rowUsers['id'] ?>"><?=$rowUsers['name'] ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="row">
                  <h4>Products</h4>
                  <button style="margin-left:auto;margin-right: 25px;" type="button" class="btn btn-primary add-branch">Add Product</button>
                </div>
                <table style="width: 100%;">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Quantity</th>
                      <th>Delete</th>
                    </tr>
                  </thead>
                  <tbody id="branchTable"></tbody>
                </table>
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

<script type="text/html" id="branchDetails">
  <tr class="details">
    <td>
      <select class="form-control" style="width: 100%;" id="product" required>
        <?php while($rowProduct=mysqli_fetch_assoc($products)){ ?>
          <option value="<?=$rowProduct['id'] ?>"><?=$rowProduct['product_name'] ?></option>
        <?php } ?>
      </select>
    </td>
    <td>
      <input id="quantity" type="number" class="form-control" placeholder="Enter ..." required>
    </td>
    <td><button class="btn btn-danger btn-sm" id="remove"><i class="fa fa-times"></i></button></td>
  </tr>
</script>

<script>
var branchCount = $("#branchTable").find(".details").length;

$(function () {
    var table = $("#productTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadJobs.php'
        },
        'columns': [
          { data: 'job_no' },
          { data: 'customer_name' },
          { data: 'name' },
          { data: 'status' },
          { 
            data: 'id',
            render: function ( data, type, row ) {
              return '<div class="row"><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
            }
          },
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

            //$('td', row).css('background-color', '#E6E6FA');
        },        
    });

    $('#productTable tbody').on('click', 'td.dt-control', function () {
      var tr = $(this).closest('tr');
      var row = table.row( tr );

      if ( row.child.isShown() ) {
        row.child.hide();
        tr.removeClass('shown');
      }
      else {
        row.child( format(row.data()) ).show();tr.addClass("shown");
      }
    });
    
    $.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();
            $.post('php/jobs.php', $('#productForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#addModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#productTable').DataTable().ajax.reload();
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

    $('#addProducts').on('click', function(){
        $('#addModal').find('#id').val("");
        $('#addModal').find('#customers').val("");
        $('#addModal').find('#branchTable').html("");
        $('#addModal').find('#pickedBy').val("");
        branchCount = 0;
        $('#addModal').modal('show');
        
        $('#productForm').validate({
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

    $(".add-branch").click(function(){
      var $addContents = $("#branchDetails").clone();
      $("#branchTable").append($addContents.html());

      $("#branchTable").find('.details:last').attr("id", "detail" + branchCount);
      $("#branchTable").find('.details:last').attr("data-index", branchCount);
      $("#branchTable").find('#remove:last').attr("id", "remove" + branchCount);

      $("#branchTable").find('#product:last').attr('name', 'product['+branchCount+']').attr("id", "product" + branchCount);
      $("#branchTable").find('#quantity:last').attr('name', 'quantity['+branchCount+']').attr("id", "quantity" + branchCount);
      
      branchCount++;
    });

    $("#branchTable").on('click', 'button[id^="remove"]', function () {
      var index = $(this).parents('.details').attr('data-index');
      $("#branchTable").append('<input type="hidden" name="deletedBranch[]" value="'+index+'"/>');
      branchCount--;
      $(this).parents('.details').remove();
    });
});

function format (row) {
  var returnString = '';
  if(row.items != null){
    returnString += '<p>Items</p><table style="width: 100%;"><thead><tr><th>Product Name</th><th>Quantity</th></tr></thead><tbody>'
    
    for(var i=0; i<row.items.length; i++){
      returnString += '<tr><td>'+row.items[i].product_name+'</td><td>'+row.items[i].quantity+'</td></tr>';
    }

    returnString += '</tbody></table>';
  }
  
  return returnString;
}

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getJob.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
          $('#addModal').find('#id').val(obj.message.id);
          $('#addModal').find('#customers').val(obj.message.customer);
          $('#addModal').find('#product').val(obj.message.product);
          $('#addModal').find('#quantity').val(obj.message.quantity);
          $('#addModal').find('#pickedBy').val(obj.message.pick_by);
          $('#addModal').modal('show');
          
          $('#productForm').validate({
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
    $('#spinnerLoading').show();
    $.post('php/deleteJobs.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            toastr["success"](obj.message, "Success:");
            $('#productTable').DataTable().ajax.reload();
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
</script>