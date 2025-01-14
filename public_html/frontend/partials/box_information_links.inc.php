<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_information_links.inc.php
	 */

	$box_information_links = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_information_links.inc.php');

	if (!empty($_GET['page_id'])) {
		$current_page_path = array_keys(reference::page($_GET['page_id'])->path);
	} else {
		$current_page_path = [];
	}

	$box_information_links->snippets = [
		'title' =>  language::translate('title_information', 'Information'),
		'pages' => [],
		'page_path' => $current_page_path,
	];

	$box_information_links_cache_token = cache::token('box_information_links', ['language', fallback($_GET['page_id'])]);
	if (!$box_information_links->snippets['pages'] = cache::get($box_information_links_cache_token)) {

		$iterator = function($parent_id) use (&$iterator) {

			return database::query(
				"select p.id, p.parent_id, pi.title, p.priority, p.date_updated from ". DB_TABLE_PREFIX ."pages p
				left join ". DB_TABLE_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
				where p.status
				". (!empty($parent_id) ? "and p.parent_id = ". (int)$parent_id ."" : "and find_in_set('information', p.dock)") ."
				order by p.priority asc, pi.title asc;"
			)->fetch_all(function($page) use (&$iterator) {

				return [
					'id' => $page['id'],
					'parent_id' => $page['parent_id'],
					'title' => $page['title'],
					'link' => document::ilink('information', ['page_id' => $page['id']], false),
					'active' => (!empty($_GET['page_id']) && $page['id'] == $_GET['page_id']),
					'subpages' => $iterator($page['id']),
				];
			});
		};

		$box_information_links->snippets['pages'] = $iterator(0, 0);

		cache::set($box_information_links_cache_token, $box_information_links->snippets['pages']);
	}

	if (!$box_information_links->snippets['pages']) return;

	echo $box_information_links->render();

