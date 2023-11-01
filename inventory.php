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
  $warehouse = $db->query("SELECT * FROM warehouse WHERE deleted = '0'");
  $grade = $db->query("SELECT * FROM grade WHERE deleted = '0'");
}
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Inventory</h1>
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
                    <div class="card-body">  
                        <div class="row">
                            <div class="form-group col-4">
                                <div class="form-group">
                                    <label for="products">Product</label>
                                    <select class="form-control" id="products" name="products" style="width: 100%;">
                                        <option selected="selected">-</option>
                                        <?php while($rowProduct=mysqli_fetch_assoc($products)){ ?>
                                            <option value="<?=$rowProduct['id'] ?>"><?=$rowProduct['product_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-4">
                                <label>Basis Weight</label>
                                <input class="form-control" type="text" id="basisWeightFilter" placeholder="Basis Weight">
                            </div>
                            <div class="form-group col-4">
                                <label>Diameter</label>
                                <input class="form-control" type="text" id="diameterFilter" placeholder="Diameter">
                            </div>
                            <div class="form-group col-4">
                                <label>Width</label>
                                <input class="form-control" type="text" id="widthFilter" placeholder="Width">
                            </div>
                            <div class="form-group col-4">
                                <label>Grade</label>
                                <select class="form-control" id="gradeFilter" name="gradeFilter" style="width: 100%;">
                                    <option selected="selected">-</option>
                                    <?php while($rowGrade=mysqli_fetch_assoc($grade)){ ?>
                                        <option value="<?=$rowGrade['id'] ?>"><?=$rowGrade['grade'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <button class="btn btn-success" id="filterSearch"><i class="fas fa-search"></i> Filter</button> 
                            </div>
                        </div>
                    </div>
                </div>
				<div class="card">
                    <!--div class="card-header">
                        <div class="row">
                            <div class="col-9"></div>
                            <div class="col-3">
                                <button type="button" class="btn btn-block bg-gradient-info btn-sm" id="scanMoistures">Scan</button>
                            </div>
                        </div>
                    </div-->
					<div class="card-body">     
						<table id="moistureTable" class="table table-bordered table-striped">
							<thead style="background-color: #1360a8;">
								<tr>
									<th>No</th>
                                    <th>Product</th>
                                    <th>Basis Weight</th>
                                    <th>Width</th>
                                    <th>Diameter</th>
                                    <th>Grade</th>
									<th>Quantity</th>
                                    <th>Weight (Kg)</th>
                                    <th>Warehouse</th>
                                    <!--th>Action <br>行动</th-->
								</tr>
							</thead>
                            <tbody></tbody>
						</table>
					</div><!-- /.card-body -->
				</div><!-- /.card -->
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->
<input type="text" id="barcodeScan">

<script>
$(function () {
    $("#moistureTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'searching': false,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadInventory.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'product_name' },
            { data: 'basis_weight' },
            { data: 'width' },
            { data: 'diameter' },
            { data: 'grade' },
            { data: 'quantity' },
            { data: 'weight' },
            { data: 'warehouse' }
            /*{ 
                data: 'id',
                width: '140px',
                render: function ( data, type, row ) {
                    return '<div class="row px-0"><div class="col-3 mr-1"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3 mr-1"><button type="button" id="print'+data+'" onclick="print('+data+')" class="btn btn-info btn-sm"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                }
            }*/
        ],
        "rowCallback": function( row, data, index ) {
            //$('td', row).css('background-color', '#E6E6FA');
        },        
    });
    
    /*$.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();
            $.post('php/moisture.php', $('#moistureForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#moistureModal').modal('hide');
                    toastr["success"](obj.message, "Success:");

                    var printWindow = window.open('', '', 'height=400,width=800');
                    printWindow.document.write(obj.label);
                    printWindow.document.close();
                    setTimeout(function(){
                        printWindow.print();
                        printWindow.close();
                    }, 1000);
                    
                    $.get('wMoisturise.php', function(data) {
                        $('#mainContents').html(data);
                        $('#spinnerLoading').hide();
                    });
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

    $('#scanMoistures').on('click', function(){
        $('#barcodeScan').trigger('focus');
    });

    $('#barcodeScan').on('change', function(){
        $('#spinnerLoading').show();
        var url = $(this).val();
        $(this).val('');

        $.get(url, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                $('#moistureModal').find('#id').val(obj.message.id);
                $('#moistureModal').find('#moisturiseItemType').val(obj.message.itemTypes);
                $('#moistureModal').find('#moisturiseTrayWeight').val(obj.message.trayWeight);
                $('#moistureModal').find('#moisturiselotNo').val(obj.message.lotNo);
                $('#moistureModal').find('#moisturiseGrossWeight').val(obj.message.moistureGrossWeight);
                $('#moistureModal').find('#moisturiseTrayNo').val(obj.message.bTrayNo);
                $('#moistureModal').find('#moisturiseNetWeight').val(obj.message.moistureNetWeight);
                $('#moistureModal').find('#moisturiseQty').val(obj.message.pieces);
                $('#moistureModal').find('#stockOutMoisture').val(obj.message.moistureAfterMoisturing);
                $('#moistureModal').modal('show');

                $('#moistureForm').validate({
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
    });*/

    $('#filterSearch').on('click', function(){
        $('#spinnerLoading').show();

        var products = $('#products').val() ? $('#products').val() : '';
        var basisWeight = $('#basisWeightFilter').val() ? $('#basisWeightFilter').val() : '';
        var diameter = $('#diameterFilter').val() ? $('#diameterFilter').val() : '';
        var width = $('#widthFilter').val() ? $('#widthFilter').val() : '';
        var grade = $('#gradeFilter').val() ? $('#gradeFilter').val() : '';

        //Destroy the old Datatable
        $("#moistureTable").DataTable().clear().destroy();

        //Create new Datatable
        table = $("#moistureTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'searching': false,
            'order': [[ 1, 'asc' ]],
            'columnDefs': [ { orderable: false, targets: [0] }],
            'ajax': {
                'type': 'POST',
                'url':'php/filterInventory.php',
                'data': {
                    basisWeight: basisWeight,
                    products: products,
                    diameter: diameter,
                    width: width,
                    grade: grade
                } 
            },
            'columns': [
                { data: 'counter' },
                { data: 'product_name' },
                { data: 'basis_weight' },
                { data: 'width' },
                { data: 'diameter' },
                { data: 'grade' },
                { data: 'quantity' },
                { data: 'weight' },
                { data: 'warehouse' }
                /*{ 
                    data: 'id',
                    width: '140px',
                    render: function ( data, type, row ) {
                        return '<div class="row px-0"><div class="col-3 mr-1"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3 mr-1"><button type="button" id="print'+data+'" onclick="print('+data+')" class="btn btn-info btn-sm"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                    }
                }*/
            ],
            "rowCallback": function( row, data, index ) {
                //$('td', row).css('background-color', '#E6E6FA');
            }
        });

        $('#spinnerLoading').hide();
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getMoisture.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#moistureModal').find('#id').val(obj.message.id);
            $('#moistureModal').find('#moisturiseItemType').val(obj.message.itemType);
            $('#moistureModal').find('#moisturiseGrossWeight').val(obj.message.moisture_gross_weight);
            $('#moistureModal').find('#moisturiselotNo').val(obj.message.lotNo);
            $('#moistureModal').find('#moisturiseTrayWeight').val(obj.message.bTrayWeight);
            $('#moistureModal').find('#moisturiseTrayNo').val(obj.message.bTrayNo);
            $('#moistureModal').find('#moisturiseNetWeight').val(obj.message.moisture_net_weight);
            $('#moistureModal').find('#moisturiseQty').val(obj.message.pieces);
            $('#moistureModal').find('#stockOutMoisture').val(obj.message.moisture_after_moisturing);
            $('#moistureModal').modal('show');
            
            $('#moistureForm').validate({
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

function print(id){
    $.post('php/printMosturing.php', {userID: id}, function(data){
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

function deactivate(id){
    $('#spinnerLoading').show();
    $.post('php/deleteReceives.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            toastr["success"](obj.message, "Success:");
            $.get('wMoisturise.php', function(data) {
                $('#mainContents').html(data);
                $('#spinnerLoading').hide();
            });
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