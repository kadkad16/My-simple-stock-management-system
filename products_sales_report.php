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
  <a href="correction_forms.php">Price/Stock Correction</a>
  <a href="products_sales_report.php" class="active">Sales Report</a>
</div>

	<div class="container-fluid">
		<span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span><hr>
		<h1>Simple Stock Management System</h1>
		<br>
		<h3>Product list</h3>
			<table class="table table-dark">
				<thead>
			    <tr>
			      <th scope="col">Product</th>
			      <th scope="col">Quantity</th>
			      <th scope="col">Price</th>
			      <th scope="col">Amount</th>
			      <th scope="col">Date & time</th>
			    </tr>
			  </thead>
			  <tbody id="sales_table">
			  	
			  </tbody>
			</table>
	</div>

</body>
</html>

<script type="text/javascript">
$(function() {
	load_sales();
});

function load_sales(){
	$.ajax({
        url: "maintenance.php",
        type: "POST",
        data: {action: "load_sales"},
        beforeSend: function(){
            $('#sales_table').html('<tr><td colspan="5">Loading...</td></tr>');
        },
        success: function(data){
            $('#sales_table').html(data);
        }
    });
}

</script>

