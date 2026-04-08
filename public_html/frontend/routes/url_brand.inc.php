<?php

	return [
		'f:brands' => [
			'pattern' => '#^b/?$#',
			'controller' => 'app://frontend/pages/brands.inc.php',
			'params' => 'brand_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(ent_link $link, $language_code) {
				$link->path = 'b/';
				return $link;
			}
		],

		'f:brand' => [
			'pattern' => '#^b/(\d+)(?:/.*)?$#',
			'controller' => 'app://frontend/pages/brand.inc.php',
			'params' => 'brand_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(ent_link $link, $language_code) {

				if (empty($link->query['brand_id'])) return;

				$brand = reference::brand($link->query['brand_id'], $language_code);
				if (empty($brand->id)) return $link;

				$link->path = 'b/'. $brand->id .'/'. f::format_path_friendly($brand->name, $language_code);
				$link->unset_query('brand_id');

				return $link;
			}
		],

		'' => [
			'pattern' => '#^.*-m-(\d+)/?$#',
			'controller' => 'app://frontend/pages/brand.inc.php',
			'params' => 'brand_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
		],
	];
