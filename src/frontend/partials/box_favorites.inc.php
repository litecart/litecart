<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/partials/box_favorites.inc.php
	*/

	$_box = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_favorites.inc.php');

	$product_ids = database::query(
		"select product_id from ". DB_PREFIX ."favorites
		where customer_id = ". (int)customer::$data['id'] ."
		or cart_uid = '". database::input(cart::$data['uid']) ."';"
	)->fetch_all('product_id');

	if (!$product_ids) {
		return;
	}

	$_box->snippets['products'] = f::catalog_products_query([
		'products' => $product_ids
	])->fetch_all();

	echo $_box->render();
