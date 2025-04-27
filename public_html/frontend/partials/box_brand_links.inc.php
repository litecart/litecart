<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_brand_links.inc.php
	 */

	$box_brand_links = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_brand_links.inc.php');

	$box_brand_links_cache_token = cache::token('box_brand_links', ['language']);
	if (!$box_brand_links->snippets['brands'] = cache::get($box_brand_links_cache_token)) {

		$box_brand_links->snippets['brands'] = database::query(
			"select b.id, b.created_at, b.name
			from ". DB_TABLE_PREFIX ."brands b
			where b.status
			order by b.name;"
		)->fetch_all(function($brand) {
			return [
				'id' => $brand['id'],
				'name' => $brand['name'],
				'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
				'created_at' => $brand['created_at'],
				'active' => (isset($_GET['brand_id']) && $_GET['brand_id'] == $brand['id']),
			];
		});

		cache::set($box_brand_links_cache_token, $box_brand_links->snippets['brands']);
	}

	if (!empty($_GET['brand_id'])) {
		foreach ($box_brand_links->snippets['brands'] as $key => $brand) {
			if ($brand['id'] == $_GET['brand_id']) {
				$box_brand_links->snippets['brands'][$key]['active'] = true;
				break;
			}
		}
	}

	if ($box_brand_links->snippets['brands']) {
		echo $box_brand_links->render();
	}
