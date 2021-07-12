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
  <a href="sell_products.php" class="active">Sell Products</a>
  <a href="correction_forms.php">Price/Stock Correction</a>
  <a href="products_sales_report.php">Sales Report</a>
</div>

	<div class="container-fluid">
		<span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span><hr>
		<h1>Simple Stock Management System</h1>
		<br>
		<h3>Sell product</h3>
		<hr>
		<div id="selling_panel">
			<div class="row">
		    <div class="col-md-2">
		      <div class="form-group">
			    <label for="product_code">Product code</label>
			    <input type="text" class="form-control" id="product_code" placeholder="Search here">
			  </div>
		    </div>

		    <div class="col-md-4">
		      <div class="form-group">
			    <label for="product_name">Product name</label>
			    <input type="text" class="form-control" id="product_name" placeholder="Search here">
			  </div>
		    </div>

		    <div class="col-md-2">
		      <div class="form-group">
			    <label for="qty_to_sell">Qty</label>
			    <input type="number" class="form-control" id="qty_to_sell" value="0" onkeypress="validate(event);">
			  </div>
		    </div>
		    <div class="col-md-2">
		      <div class="form-group">
			    <label for="price">Price</label>
			    <input type="text" class="form-control" id="price" readonly>
			  </div>
		    </div>

		    <div class="col-md-2">
		      <div class="form-group">
			    <label for="amount_1">Total</label>
			    <input type="text" class="form-control" id="amount_1" readonly>
			  </div>
		    </div>

		    </div>
		    

		    <div align="right">
			 	<button type="button" class="btn btn-primary" onclick="add_item();" id="btn_add">Add</button>
			 </div>
		 
		 

		 <!-- search results -->
		 <div id="search_results"></div>
		 <br>
		 <h5>Items added:</h5>
		 <div id="items_added"></div>

		 <hr>
		 <div align="right">
		 	<button type="button" class="btn btn-success" id="btn_save" onclick="save_form();">Save</button>
		 </div>
		 </div>

		 <div id="success_panel" style="display: none;">
		 	<button type="button" class="btn btn-warning" onclick="sell_again();">Sell product again</button>
		 	<h3>Product list</h3>
			<table class="table table-dark">
				<thead>
			    <tr>
			      <th scope="col">Products</th>
			      <th scope="col">Stock</th>
			      <th scope="col">Price</th>
			    </tr>
			  </thead>
			  <tbody id="products_table">
			  	
			  </tbody>
			</table>
		 </div>
	</div>

</body>
</html>

<script type="text/javascript">
$(function(){
	var s_id;
	var s_stock = 0;
	var s_price = 0;
	$('#qty_to_sell').attr('disabled', true);
	$('#btn_add').attr('disabled', true);
	load_item_list();
});

function load_products_table(){
	$.ajax({
        url: "maintenance.php",
        type: "POST",
        data: {action: "load_products_table"},
        beforeSend: function(){
            $('#products_table').html('<tr><td colspan="3">Loading...</td></tr>');
        },
        success: function(data){
            $('#products_table').html(data);
        }
    });
}

function load_item_list(){
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "load_item_list"},
		success: function(data){
			$('#items_added').html(data);
		}
	})
}

//for searching values
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

$('#product_code').keyup(bindDelay(function (e) {
  $('#btn_add').attr('disabled', true);
  var search = $('#product_code').val();
    
        $.ajax({
            url: "maintenance.php",
            type: "POST",
            data: {action: "search_item_by_code", search: search},
            beforeSend: function(){
                $('#search_results').html('<h5>Searching...</h5>');
            },
            success: function(data){
                $('#search_results').html(data);
            }
        });
}, 700));


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
	$('#qty_to_sell').attr('disabled', false);
	$('#amount_1').val('');
	$('#qty_to_sell').val('0');
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "retrieve_item", id: id},
		success: function(data){
			var x = data.split(".-.");

			$('#product_code').val(x[0]);
			$('#product_name').val(x[1]);
			s_id = x[0];
			s_stock = x[2];
			s_price = x[3];
			$('#price').val(x[3]);
			

			$('#search_results').html('');
		}
	});
}

function clear_results(){
	$('#search_results').html('');
}

$('#qty_to_sell').on('input',function(e){
	$('#btn_add').attr('disabled', false);
	if($('#qty_to_sell').val() <= 0){
		$('#btn_add').attr('disabled', true);
	}else{
		$('#btn_add').attr('disabled', false);
	}
	var orig_qty, outgoing_qty, price, total_qty, amount;
    var total = 0;
    if(s_stock != 0 && s_stock != ""){
    	 orig_qty = parseFloat(s_stock);
    	 outgoing_qty = parseFloat($('#qty_to_sell').val());
    	 price = parseFloat(s_price);

    	 total_qty = orig_qty-outgoing_qty;
    	 amount = outgoing_qty*price;

    	$('#amount_1').val(amount.toFixed(2));

    }
});

function isBlank(str) {
    return (!str || /^\s*$/.test(str));
}

function add_item(){
	var og_qty = parseFloat($('#qty_to_sell').val());
	og_qty = og_qty.toFixed(2);
	if(!isBlank(og_qty) && og_qty != 0){
		
			if(og_qty >= 0){
				$.ajax({
					url: "maintenance.php",
					type: "POST",
					data: {action: "add_item", id: s_id, og_qty: og_qty},
					success: function(data){
						
						if(data!="error stock"){
							$('#items_added').html(data);
							clear_inputs();
						}else{
							alert("Insufficient stock. Current stock ["+s_stock+"]");
						}
					}
				});
			}else{
				alert("Invalid quantity input.");
			}
		
		
	}else{
		alert("Quantity is empty/invalid.");
	}
}

function remove_item(p_id){
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "remove_item", id: p_id},
		success: function(data){
			$('#items_added').html(data);
			load_item_list();
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

function save_form(){
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "save_form"},
		success: function(data){
			if(data=="good"){
 				alert("Stocks updated.");
 				load_products_table();
 				$('#success_panel').show();
 				$('#selling_panel').hide();
			}
			if(data=="bad"){
 				alert("Error");
			}	
		}
	});
}

function clear_inputs(){
	$('#product_code').val('');
	$('#product_name').val('');
	$('#qty_to_sell').val('0');
	$('#price').val('');
	$('#amount_1').val('');
}

function sell_again(){
	$('#selling_panel').show();
	$('#success_panel').hide();
	clear_results();
	$('#items_added').html('');
	clear_inputs();

}

</script>

