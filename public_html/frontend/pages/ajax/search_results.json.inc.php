<?php

	try {

		if (empty($_GET['query'])) {
			$_GET['query'] = '';
		}

		$_GET['query'] = trim($_GET['query']);

		$result = [];

		$result['categories'] = functions::catalog_categories_search_query([
			'query' => $_GET['query'],
			'limit' => 5,
		])->fetch_all(function($category) {
			return [
				'id' => $category['id'],
				'name' => $category['name'],
				'link' => document::ilink('category', ['category_id' => $category['id']]),
				'image' => [
					'original' => document::rlink('storage://images' . $category['image'] ?: 'no_image.svg'),
					'thumbnail' => document::rlink(functions::image_thumbnail('storage://images/'. $category['image'], 128, 0, 'category')),
					'thumbnail_2x' => document::rlink(functions::image_thumbnail('storage://images/'. $category['image'], 256, 0, 'category')),
				],
			];
		});

		$result['products'] = functions::catalog_products_search_query([
			'query' => $_GET['query'],
			'limit' => 5,
		])->fetch_all(function($product) {
			return [
				'id' => $product['id'],
				'name' => $product['name'],
				'link' => document::ilink('product', ['product_id' => $product['id']]),
				'image' => [
					'original' => document::rlink('storage://images' . $product['image'] ?: 'no_image.svg'),
					'thumbnail' => document::rlink(functions::image_thumbnail('storage://images/'. $product['image'], 128, 0, 'product')),
					'thumbnail_2x' => document::rlink(functions::image_thumbnail('storage://images/'. $product['image'], 256, 0, 'product')),
				],
			];
		});

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
