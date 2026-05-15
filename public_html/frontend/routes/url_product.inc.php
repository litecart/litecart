<?php

	return [
		'f:product' => [
			'pattern' => '#^p/(\d+)(?:/.*)?$#',
			'controller' => 'app://frontend/pages/product.inc.php',
			'params' => 'product_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(type_url $link, $language_code) {

				if (!is_array($link->query)) {
					return;
				}

				if (empty($link->query['product_id'])) {
					return;
				}

				$product = reference::product($link->query['product_id'], $language_code);

				if (empty($product->id)) {
					return $link;
				}

				$new_path = '';

				if (isset($link->query['category_id']) && $link->query['category_id'] == $product->default_category_id) {
					$link->unset_query('category_id');
				}

				$new_path .= 'p/'. $product->id .'/'. f::format_path_friendly($product->name, $language_code);

				$link->path = $new_path;
				$link->unset_query('product_id');

				return $link;
			}
		],

		'' => [
			'pattern' => '#^(?:.*-c-(\d+)/)?(?:.*-m-(\d+)/)?.*-p-(\d+)$#',
			'controller' => 'app://frontend/pages/product.inc.php',
			'params' => 'category_id=$1&brand_id=$2&product_id=$3',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
		],
	];
