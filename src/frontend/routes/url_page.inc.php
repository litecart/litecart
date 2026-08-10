<?php

	return [
		'f:page' => [
			'pattern' => '#^pages/(\d+)(?:/.*)?$#',
			'controller' => 'app://frontend/pages/page.inc.php',
			'params' => 'page_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(type_url $link, string $language_code): ?type_url {

				if (empty($link->query['page_id'])) return null;

				$page = reference::page($link->query['page_id'], $language_code);
				if (empty($page->id)) return $link;

				if (empty($page)) return null;

				$link->path = 'pages/'. $page->id .'/'. f::format_path_friendly($page->title, $language_code);
				$link->unset_query('page_id');

				return $link;
			}
		],
	];
