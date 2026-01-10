<?php

	return [
		'f:category' => [
			'pattern' => '#^categories/(\d+)(?:/.*)?$#',
			'controller' => 'app://frontend/pages/category.inc.php',
			'params' => 'category_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(ent_link $link, $language_code) {

				if (empty($link->query['category_id'])) return;

				$link->path = 'categories/'. $link->query['category_id'];

				$category = reference::category($link->query['category_id'], $language_code);

				foreach ($category->path as $parent_id => $parent) {
					$link->path .= '/'. f::format_path_friendly($parent->name, $language_code);
				}

				$link->unset_query('category_id');

				return $link;
			}
		],

		null => [
			'pattern' => '#^.*-c-(\d+)/?$#',
			'controller' => 'app://frontend/pages/category.inc.php',
			'params' => 'category_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
		],
	];
