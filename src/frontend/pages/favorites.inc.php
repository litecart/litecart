<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/favorites.inc.php
	*/

	header('X-Robots-Tag: noindex');

	if (settings::get('catalog_only_mode')) return;

	document::$title[] = t('title_favorites', 'Favorites');

	breadcrumbs::add(t('title_favorites', 'Favorites'), document::ilink('favorites'));

	if (!empty($_POST['add'])) {
		try {

			if (empty($_POST['product_id'])) {
				throw new Exception(t('error_must_provide_product', 'You must provide a product'));
			}

			database::query(
				"insert ignore into ". DB_PREFIX ."favorites
				(customer_id, cart_uid, product_id, created_at)
				values (" . (int)customer::$data['id'] .", '". database::input(cart::$data['uid']) ."', ". (int)$_POST['product_id'] .", '". date('Y-m-d H:i:s') ."');",
			);

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json(['status' => 'ok']);
				exit;
			}

			notices::add('success', t('success_added_to_favorites', 'The product was added to your favorites'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}

		ob_clean();
		header('Content-type: application/json; charset=' . mb_http_output());
		echo f::format_json(['error' => $e->getMessage()]);
		exit;
	}

	if (!empty($_POST['remove'])) {

		try {

			if (empty($_POST['product_id'])) {
				throw new Exception(t('error_must_provide_product_id', 'You must provide a product'));
			}

			database::query(
				"delete from ". DB_PREFIX ."favorites
				where (
					customer_id = ". (int) customer::$data['id'] ."
					or cart_uid = '". database::input(cart::$data['uid']) ."'
				)
				and product_id = ". (int) $_POST['product_id'] ."
				limit 1;",
			);

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json(['status' => 'ok']);
				exit;
			}

			notices::add('success', t('success_removed_from_favorites', 'The product was removed from your favorites'));
			reload();
			exit;

		} catch (Exception $e) {

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json(['error' => $e->getMessage()]);
				exit;
			}

			notices::add('errors', $e->getMessage());
		}

		ob_clean();
		header('Content-type: application/json; charset=' . mb_http_output());
		echo f::format_json(['error' => $e->getMessage()]);
		exit;
	}

	// Output

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/favorites.inc.php');

	$product_ids = database::query(
		"select product_id from ". DB_PREFIX ."favorites
		where customer_id = ". (int)customer::$data['id'] ."
		or cart_uid = '". database::input(cart::$data['uid']) ."';"
	)->fetch_all('product_id');

	if ($product_ids) {
		$_page->snippets['products'] = f::catalog_products_query(['products' => $product_ids])->fetch_all();
	} else {
		$_page->snippets['products'] = [];
	}

	// Headless requests
	if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
		header('Content-Type: application/json;charset='. mb_http_output());
		echo f::format_json($_page->snippets);
		exit;
	}

	echo $_page->render();
