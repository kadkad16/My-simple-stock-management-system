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
		<h3>Price / Stock correction <span><button type="button" class="btn btn-link" onclick="add_new_form();">Add correction form</button></span></h3> 
		<br>
		<br>
			<table class="table table-dark">
				<thead>
			    <tr>
			      <th scope="col">Cor no.</th>
			      <th scope="col">Date</th>
			      <th scope="col">No. of items</th>
			    </tr>
			  </thead>
			  <tbody id="correction_forms_list">
			  	
			  </tbody>
			</table>
	</div>

</body>
</html>

<div class="modal" tabindex="-1" role="dialog" id="doc_modal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div id="doc_table">
        
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
$(function() {
	load_correction_list_forms();
});

function load_correction_list_forms(){
	$.ajax({
        url: "maintenance.php",
        type: "POST",
        data: {action: "load_correction_list_forms"},
        beforeSend: function(){
            $('#correction_forms_list').html('<tr><td colspan="3">Loading...</td></tr>');
        },
        success: function(data){
            $('#correction_forms_list').html(data);
        }
    });
}
function add_new_form(){
	$.ajax({
        url: "maintenance.php",
        type: "POST",
        data: {action: "new_correction_form"},
        success: function(data){
            location.href="correction_doc.php?cor_no="+data;
        }
    });
}

function view_document(cor_no){
	$.ajax({
		url: "maintenance.php",
		type: "POST",
		data: {action: "view_document", cor_no: cor_no},
		success: function(data){
			$('#doc_table').html(data);
			$('#doc_modal').modal('show');
		}
	})
}
</script>

