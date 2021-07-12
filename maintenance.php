<?php
require 'configuration.php';

if(isset($_POST["action"])){

	if($_POST["action"]=="load_products_table"){
		$products = mysqli_query($connection, "SELECT * FROM products");
		while($product = mysqli_fetch_assoc($products)){
			echo '<tr>';
			echo '<td>'.$product["Products"].'</td>';
			echo '<td>'.$product["Stock"].'</td>';
			echo '<td>'.$product["Price"].'</td>';
			echo '</tr>';
		}
	}

	if($_POST["action"]=="search_item_by_code"){
		$search = $_POST['search'];
		if(!empty($search) && !is_null($search)){
			$header = "<h5><span class='close_span' onclick='clear_results();'>&times;</span> Search results: </h5>";
			$content = "";

			$sql1 = "SELECT id, Products, Stock, Price, CONCAT_WS(' ', Products, Stock, Price) full_item_name FROM products WHERE (id LIKE '%$search%')";
			$invs = mysqli_query($connection, $sql1);

			if(mysqli_num_rows($invs) == 0) {
				$content = '<p>No item found.</p>';
			}
			else{
				while($inv = mysqli_fetch_assoc($invs)){
					$content .= '<p class="customPclick" onclick="call_item_record('.$inv["id"].');">['.$inv["id"].'] - '.$inv["Products"].' | Stock ('.$inv["Stock"].') | Price ('.$inv["Price"].')</p>';
				}
			}
			echo $header.$content;
		}
		else{
			echo '';
		}
	}

	if($_POST["action"]=="search_item"){
		$search = $_POST['search'];
		if(!empty($search) && !is_null($search)){
			$header = "<h5>Search results: </h5>";
			$content = "";

			$sql1 = "SELECT id, Products, Stock, Price, CONCAT_WS(' ', Products, Stock, Price) full_item_name FROM products WHERE (Products LIKE '%$search%' OR CONCAT_WS(' ', Products, Stock, Price) LIKE '%$search%')";
			$invs = mysqli_query($connection, $sql1);

			if(mysqli_num_rows($invs) == 0) {
				$content = '<p>No item found.</p>';
			}
			else{
				while($inv = mysqli_fetch_assoc($invs)){
					$content .= '<p class="customPclick" onclick="call_item_record('.$inv["id"].');">['.$inv["id"].'] - '.$inv["Products"].' | Stock ('.$inv["Stock"].') | Price ('.$inv["Price"].')</p>';
				}
			}
			echo $header.$content;
		}
		else{
			echo '';
		}
	}

	if($_POST["action"]=="retrieve_item"){
		$id = $_POST["id"];
		$items = mysqli_query($connection, "SELECT * FROM products WHERE id=$id");
		$item = mysqli_fetch_assoc($items);
		echo $item["id"].'.-.'.$item["Products"].'.-.'.$item["Stock"].'.-.'.$item["Price"].'.-.';

	}

	if($_POST["action"]=="add_item"){
		$id = $_POST["id"];
		$og_qty = number_format($_POST["og_qty"], 2, '.', "");

		$products = mysqli_query($connection, "SELECT * FROM products WHERE id='$id'");
		$product = mysqli_fetch_assoc($products);
		$item = $product["Products"];
		$price = $product["Price"];
		$current_stock = $product["Stock"];

		$if_exists = mysqli_query($connection, "SELECT * FROM cart WHERE product_id='$id'");
		$ie = mysqli_fetch_assoc($if_exists);
		$c_qty = $ie["qty"];

		$con = $c_qty + $og_qty;
		if($con > $current_stock){
			echo "error stock";
		}else{
			if($ie["product_id"]==""){
				$total = $og_qty*$price;
				mysqli_query($connection, "INSERT INTO cart (product_id, item, qty, amount, price) VALUES ('$id', '$item', '$og_qty', '$total', '$price')");
			}
			else{

				$u_qty = $c_qty+$og_qty;
				$u_qty = number_format($u_qty, 2, '.', "");
				$total = $u_qty*$price;
				mysqli_query($connection, "UPDATE cart SET qty='$u_qty', amount='$total' WHERE product_id ='$id'");
			}

			$ts = mysqli_query($connection, "SELECT SUM(amount) FROM cart");
			$t = mysqli_fetch_assoc($ts);
			$grand_total = number_format($t["SUM(amount)"], 2, '.', "");

			$added_items = mysqli_query($connection, "SELECT * FROM cart");
			while($added_item = mysqli_fetch_assoc($added_items)){
				$item = "[".$added_item["product_id"]."] ".$added_item["item"]." x ".$added_item["qty"]." = <b>". number_format($added_item["amount"], 2, '.', "")."</b>";
				echo "<p><b class='close_b' onclick='remove_item(".$added_item["product_id"].");'>&times;</b> ".$item."</p>";
			}
			echo '<div align="right"><h5>TOTAL SALE: <strong>'.$grand_total.'</strong></h5></div>';
		}
	}

	if($_POST["action"]=="remove_item"){
		$id = $_POST["id"];
		mysqli_query($connection, "DELETE FROM cart WHERE product_id = '$id'");
	}

	if($_POST["action"]=="load_item_list"){
		$ts = mysqli_query($connection, "SELECT SUM(amount) FROM cart");
		$t = mysqli_fetch_assoc($ts);
		$grand_total = number_format($t["SUM(amount)"], 2, '.', "");

		$added_items = mysqli_query($connection, "SELECT * FROM cart");
		while($added_item = mysqli_fetch_assoc($added_items)){
			$item = "<b class='close_b' onclick='remove_item(".$added_item["product_id"].");'>&times;</b> [".$added_item["product_id"]."] ".$added_item["item"]." x ".$added_item["qty"]." = <b>". number_format($added_item["amount"], 2, '.', "")."</b>";
			echo "<p>".$item."</p>";
		}
		echo '<div align="right"><h5>TOTAL SALE: <strong>'.$grand_total.'</strong></h5></div>';
	}

	if($_POST["action"]=="save_form"){
	
		$forms = mysqli_query($connection, "SELECT * FROM cart");
		if(mysqli_num_rows($forms) == 0) {
			echo "bad";
		}
		else{
			while($form = mysqli_fetch_assoc($forms)){
				$id = $form["product_id"];
				$from_form_stock = number_format($form["qty"], 2, '.', "");
				
				$products = mysqli_query($connection, "SELECT * FROM products WHERE id = '$id'");
				$product = mysqli_fetch_assoc($products);
				$product_current_stock = number_format($product["Stock"], 2, '.', "");
				$new_stock = $product_current_stock-$from_form_stock;

				mysqli_query($connection, "UPDATE products SET Stock = '$new_stock' WHERE id='$id'");

			}

			mysqli_query($connection, "INSERT INTO sales (product_id, product_name, qty, amount, price) 
					SELECT product_id, item, qty, amount, price FROM cart");

			mysqli_query($connection, "TRUNCATE TABLE cart");
			echo "good";
		}
	}


	if($_POST["action"]=="new_correction_form"){
	  $cur_nos = mysqli_query($connection, "SELECT * FROM settings WHERE id=1");
	  $cur_no = mysqli_fetch_assoc($cur_nos);
	  $cor_no = $cur_no["correction_form"];
	  $date_today = date("m/d/Y");
	  echo $cur_no["correction_form"];
	  mysqli_query($connection, "INSERT INTO correction_form (cor_no,date_added,product_id,status) VALUES ('$cor_no','$date_today','xxxbc','unsaved')");
	  $new_no = $cur_no["correction_form"]+1;
	  mysqli_query($connection, "UPDATE settings SET correction_form = '$new_no' WHERE id=1");
	}

	if($_POST["action"]=="retrieve_item_modal"){
		$id = $_POST["id"];

		$products = mysqli_query($connection, "SELECT * FROM products WHERE id = '$id'");
		$product = mysqli_fetch_assoc($products);
		echo '<p><b>Product name</b>: '.$product["Products"].'</p>';
		echo '<p><b>Stock</b>: '.$product["Stock"].'</p>';
		echo '<p><b>Price</b>: '.$product["Price"].'</p>';
		echo '<hr>';
		echo '
		<input type="text" id="selected_id" value="'.$id.'" hidden>
		<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label for="new_stock"> New stock</label>
				<input type="text" class="form-control" id="new_stock" onkeypress="validate(event)">
				<input type="text" id="stock_orig" value="'.$product["Stock"].'" hidden>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label for="new_price"> New price</label>
				<input type="text" class="form-control" id="new_price" onkeypress="validate(event)">
				<input type="text" id="price_orig" value="'.$product["Price"].'" hidden>
			</div>
		</div>
		</div>
		';
	}

	if($_POST["action"]=="load_correction_list"){
		$cor_no = $_POST["cor_no"];
		$lists = mysqli_query($connection, "SELECT * FROM correction_form WHERE cor_no='$cor_no'");
		while($list = mysqli_fetch_assoc($lists)){
			$p_id = $list["product_id"];
			$sqls = mysqli_query($connection, "SELECT * FROM products WHERE id = '$p_id'");
			$sql = mysqli_fetch_assoc($sqls);
			if($list["product_id"]!="xxxbc"){
				echo '<tr>';
				echo '<td>'.$sql["Products"].'</td>';
				echo '<td>'.$sql["Stock"].'</td>';
				echo '<td>'.$sql["Price"].'</td>';
				echo '<td>'.$list["new_stock"].'</td>';
				echo '<td>'.$list["new_price"].'</td>';
				echo '</tr>';
			}
			
		}
	}

	if($_POST["action"]=="update_stock"){
		$id = $_POST["id"];
		$new_stock = $_POST["new_stock"];
		$cor_no = $_POST["cor_no"];
		$date_today = date("m/d/Y");
		$products = mysqli_query($connection, "SELECT * FROM products WHERE id = '$id'");
		$product = mysqli_fetch_assoc($products);
		$item = $product["Product"];
		$current_stock = $product["Stock"];
		$current_price = $product["Price"];
		$difference = 0;
		if($new_stock>$current_stock){
			$difference = $new_stock-$current_stock;
		}else{
			$difference = $current_stock-$new_stock;
		}

		mysqli_query($connection, "INSERT INTO correction_form (cor_no, product_id, old_qty, old_price, new_stock, difference, status, date_added) VALUES ('$cor_no', '$id', '$current_stock', '$current_price', '$new_stock', '$difference', 'unsaved', '$date_today')");

	}

	if($_POST["action"]=="update_price"){
		$id = $_POST["id"];
		$new_price = $_POST["new_price"];
		$cor_no = $_POST["cor_no"];
		$date_today = date("m/d/Y");
		$products = mysqli_query($connection, "SELECT * FROM products WHERE id = '$id'");
		$product = mysqli_fetch_assoc($products);
		$item = $product["Product"];

		$current_price = $product["Price"];

		mysqli_query($connection, "INSERT INTO correction_form (cor_no, product_id, old_price, new_price, status, date_added) VALUES ('$cor_no', '$id', '$current_price', '$new_price', 'unsaved', '$date_today')");

	}

	if($_POST["action"]=="submit_form"){
		$cor_no = $_POST["cor_no"];
		$updates = mysqli_query($connection, "SELECT * FROM correction_form WHERE cor_no ='$cor_no'");
		while($update = mysqli_fetch_assoc($updates)){
			$new_stock = $update["new_stock"];
			$new_price = $update["new_price"];
			$id = $update["product_id"];
			if($new_stock != ""){
				mysqli_query($connection, "UPDATE products SET Stock='$new_stock' WHERE id='$id'");
			}
			if($new_price != ""){
				mysqli_query($connection, "UPDATE products SET Price='$new_price' WHERE id='$id'");
			}
		}

		mysqli_query($connection, "UPDATE correction_form SET status ='saved' WHERE cor_no ='$cor_no'");
		
	}

	if($_POST["action"]=="load_correction_list_forms"){
		 $sql= "SELECT cor_no, date_added, COUNT(DISTINCT product_id) FROM correction_form WHERE product_id<>'xxxbc' AND status='saved' GROUP BY cor_no";
	    $docs = mysqli_query($connection, $sql);
	    if (mysqli_num_rows($docs)!=0) { 
	    while($doc = mysqli_fetch_assoc($docs)){
	        echo '<tr>';
	        echo '<td><b onclick="view_document('.$doc["cor_no"].')"><u>'.$doc["cor_no"].'</u></b></td>';
	        echo '<td>'.$doc["date_added"].'</td>';
	        echo '<td>'.$doc["COUNT(DISTINCT product_id)"].'</td>';
	        echo '</tr>';
	    	}
		}else{
			echo '<tr><td colspan="3">No data available.</td></tr>';
		}
	}

	if($_POST["action"]=="view_document"){
		$cor_no = $_POST["cor_no"];
		echo '<div class="modal-header">
		<h5 class="modal-title">Correction form no.: '.$cor_no.'</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<table class="table table-sm">
			  <thead>
			    <tr>
			      <th scope="col">Product</th>
			      <th scope="col">New stock</th>
			      <th scope="col">New price</th>
			      <th scope="col">Old stock</th>
			      <th scope="col">Old price</th>
			    </tr>
			  </thead>
			  <tbody>';

			  $docs = mysqli_query($connection, "SELECT * FROM correction_form WHERE cor_no='$cor_no'");
			  while($doc = mysqli_fetch_assoc($docs)){
			  	$p_id = $doc["product_id"];
				$sqls = mysqli_query($connection, "SELECT * FROM products WHERE id = '$p_id'");
				$sql = mysqli_fetch_assoc($sqls);
				if($doc["product_id"]!="xxxbc"){
					echo '<tr>';
					echo '<td>'.$sql["Products"].'</td>';
					echo '<td>'.$doc["new_stock"].'</td>';
					echo '<td>'.$doc["new_price"].'</td>';
					echo '<td>'.$doc["old_qty"].'</td>';
					echo '<td>'.$doc["old_price"].'</td>';
					echo '</tr>';
				}
			  }

		echo '</tbody>
		</table>
		</div>';
	}

	if($_POST["action"]=="load_sales"){
		$sales = mysqli_query($connection, "SELECT * FROM sales");
		if (mysqli_num_rows($sales)!=0) { 
			while($sale = mysqli_fetch_assoc($sales)){
				echo '<tr>';
				echo '<td>'.$sale["product_name"].'</td>';
				echo '<td>'.$sale["qty"].'</td>';
				echo '<td>'.$sale["price"].'</td>';
				echo '<td>'.$sale["amount"].'</td>';
				echo '<td>'.$sale["purchased_date"].'</td>';
				echo '</tr>';
			}
		}else{
			echo '<tr><td colspan="5">No data available.</td></tr>';
		}
		
	}

}


?>