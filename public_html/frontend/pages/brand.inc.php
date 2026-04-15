<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/brand.inc.php
	 */

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (empty($_GET['sort'])) {
		$_GET['sort'] = 'price';
	}

	if (empty($_GET['list_style'])) {
		$_GET['list_style'] = 'columns';
	}

	if (empty($_GET['brand_id'])) {
		redirect(document::ilink('brands'), 301);
		exit;
	}

	$brand = reference::brand($_GET['brand_id']);

	if (!$brand->id) {
		http_response_code(410);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	if (!$brand->status) {
		http_response_code(404);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	document::$title[] = $brand->head_title ?: $brand->name;
	document::$description = $brand->meta_description ?: strip_tags($brand->short_description);

	document::$head_tags['canonical'] = '<link rel="canonical" href="'. document::href_ilink('brand', ['brand_id' => (int)$brand->id], false) .'">';

	breadcrumbs::add(t('title_brands', 'Brands'), document::ilink('brands'));
	breadcrumbs::add($brand->name, document::ilink('brand', ['brand_id' => $brand->id]));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/brand.inc.php');

	$_page->snippets = [
		'id' => $brand->id,
		'title' => $brand->h1_title ?: $brand->name,
		'name' => $brand->name,
		'description' => $brand->description,
		'link' => $brand->link,
		'image' => $brand->image ? 'storage://images/' . $brand->image : '',
		'products' => [],
		'sort_alternatives' => [
			'name' => t('title_name', 'Name'),
			'price' => t('title_price', 'Price'),
			'popularity' => t('title_popularity', 'Popularity'),
			'date' => t('title_date', 'Date'),
		],
	];

	$_page->snippets['products'] = f::catalog_products_query([
		'brands' => [$brand->id],
		'product_name' => $_GET['product_name'] ?? '',
		'sort' => $_GET['sort'],
		'campaigns_first' => true,
	])->fetch_page(null, null, $_GET['page'], 20, $num_rows, $num_pages);

	$_page->snippets['num_products'] = $num_rows;
	$_page->snippets['num_pages'] = $num_pages;

	// Headless requests
	if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
		header('Content-Type: application/json;charset='. mb_http_output());
		echo f::format_json($_page->snippets);
		exit;
	}

	$_page->snippets['pagination'] = f::draw_pagination($num_pages);

	echo $_page->render();
