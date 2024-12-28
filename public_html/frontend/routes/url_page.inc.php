<?php

	return [
		'f:page' => [
			'pattern' => '#^pages/([0-9]+)(/.*|/?$)$#',
			'controller' => 'app://frontend/pages/page.inc.php',
			'params' => 'page_id=$1',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(ent_link $link, $language_code) {

				if (empty($link->query['page_id'])) return false;

				$page = reference::page($link->query['page_id'], $language_code);
				if (empty($page->id)) return $link;

				if (empty($page)) return false;

				$link->path = 'pages/'. $page->id .'/'. functions::format_path_friendly($page->title, $language_code);
				$link->unset_query('page_id');

				return $link;
			}
		],
	];
