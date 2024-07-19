<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_brand_links.inc.php
	 */

	$box_brand_links_cache_token = cache::token('box_brand_links', ['language', fallback($_GET['brand_id'])]);
	if (cache::capture($box_brand_links_cache_token)) {

		$box_brand_links = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_brand_links.inc.php');

		$box_brand_links->snippets['brands'] = database::query(
			"select b.id, b.name, b.date_created from ". DB_TABLE_PREFIX ."brands b
			left join ". DB_TABLE_PREFIX ."brands_info bi on (b.id = bi.brand_id and bi.language_code = '". language::$selected['code'] ."')
			where b.status
			order by b.name;"
		)->fetch_all(function($brand) {
			return [
				'id' => $brand['id'],
				'name' => $brand['name'],
				'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
				'date_created' => $brand['date_created'],
				'active' => (isset($_GET['brand_id']) && $_GET['brand_id'] == $brand['id']),
			];
		});

		if ($box_brand_links->snippets['brands']) {
			echo $box_brand_links->render();
		}

		cache::end_capture($box_brand_links_cache_token);
	}
