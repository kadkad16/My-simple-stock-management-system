<?php require 'configuration.php'; ?>
<!DOCTYPE html>
<html>
<head>
	<?php require 'meta.php'; ?>
	<title>Simple Stock Management System</title>
	<?php require 'link.php'; ?>
	<?php require 'script.php'; ?>
</head>
<body>
<!-- sidebar -->
<div id="mySidenav" class="sidenav">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <a href="products.php">Products</a>
  <a href="sell_products.php">Sell Products</a>
  <a href="correction_forms.php" class="active">Price/Stock Correction</a>
  <a href="products_sales_report.php">Sales Report</a>
</div>

	<div class="container-fluid">
		<span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span><hr>
		<h1>Simple Stock Management System</h1>
		<br>
		<h3>Correction form no. <?php echo $_GET["cor_no"]; ?></h3>
		<input type="text" value="<?php echo $_GET["cor_no"]; ?>" id="cor_no" hidden> 
		<br>
		<div class="row">
			<div class="col-sm-2">
				<button type="button" class="btn btn-success btn-block" onclick="add_item_button();">Add item</button>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-primary btn-block" onclick="save_button();">Save & submit</button>
			</div>
			<div class="col-sm-2">
				<button type="button" class="btn btn-light btn-block" onclick="cancel_button();">Cancel</button>
			</div>
		</div>
		<br>
			<div id="myTable">
				<table class="table table-dark">
					<thead>
				    <tr>
				      <th scope="col">Products</th>
				      <th scope="col">Stock</th>
				      <th scope="col">Price</th>
				      <th scope="col">New stock</th>
				      <th scope="col">New price</th>
				    </tr>
				  </thead>
				  <tbody id="correction_table">
				  	
				  </tbody>
				</table>
			</div>
			<div id="search_panel" style="display: none;">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
						   <label for="product_name"><span class='close_span' onclick='close_search_panel();'>&times;</span> Search product name</label>
						   <input type="text" class="form-control" id="product_name">
						 </div>
					</div>
				</div>
				<hr>
				<div id="search_results"></div>
			</div>
	</div>

</body>
</html>

<div class="modal" tabindex="-1" role="dialog" id="mymodal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Product information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="item_result">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="add_to_list();">Add</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
load_correction_list();
function add_item_button(){
	$('#myTable').hide();
	$('#search_panel').show();
}

function close_search_panel(){
	$('#myTable').show();
	$('#search_panel').hide();
}

function load_correction_list(){
	var cor = $('#cor_no').val();
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "load_correction_list", cor_no: cor},
		success: function(data){
			$('#correction_table').html(data);
		}
	})
}

function bindDelay(callback, ms) {
      var timer = 0;
      return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
          callback.apply(context, args);
        }, ms || 0);
      };
}

$('#product_name').keyup(bindDelay(function (e) {
  $('#btn_add').attr('disabled', true);
  var search = $('#product_name').val();
    
        $.ajax({
            url: "maintenance.php",
            type: "POST",
            data: {action: "search_item", search: search},
            beforeSend: function(){
                $('#search_results').html('<h5>Searching...</h5>');
            },
            success: function(data){
                $('#search_results').html(data);
            }
        });
}, 700));

function call_item_record(id){
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "retrieve_item_modal", id: id},
		success: function(data){
			$('#item_result').html(data);
			$('#mymodal').modal('show');
		}
	});
}

function validate(evt) {
  var theEvent = evt || window.event;

  // Handle paste
  if (theEvent.type === 'paste') {
      key = event.clipboardData.getData('text/plain');
  } else {
  // Handle key press
      var key = theEvent.keyCode || theEvent.which;
      key = String.fromCharCode(key);
  }
  var regex = /[0-9]|\./;
  if( !regex.test(key) ) {
    theEvent.returnValue = false;
    if(theEvent.preventDefault) theEvent.preventDefault();
  }
}

function isBlank(str) {
    return (!str || /^\s*$/.test(str));
}

function add_to_list(){
	var id = $('#selected_id').val();
	var new_stock = $('#new_stock').val();
	var new_price = $('#new_price').val();
	var cor_no = $('#cor_no').val();
	if(!isBlank(new_stock) && new_stock!=0){
		if(new_stock!=$('#stock_orig').val()){
			$.ajax({
				url: "maintenance.php",
				type: "POST",
				data: {action: "update_stock", id: id, new_stock: new_stock, cor_no: cor_no},
				success: function(data){
					
					load_correction_list();
					$('#mymodal').modal('hide');
					close_search_panel();
				}
			});
		}
	}
	
	if(!isBlank(new_price) && new_price!=0){
		if(new_price!=$('#price_orig').val()){
			$.ajax({
				url: "maintenance.php",
				type: "POST",
				data: {action: "update_price", id: id, new_price: new_price, cor_no: cor_no},
				success: function(data){

					load_correction_list();
					$('#mymodal').modal('hide');
					close_search_panel();
				}
			})
		}
	}
}

function save_button(){
	var cor_no = $('#cor_no').val();
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "submit_form", cor_no: cor_no},
		success: function(data){
			alert("Form submitted successfully.");
			location.href = "correction_forms.php";
		}
	})
}



</script>

