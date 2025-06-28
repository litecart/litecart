<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/brands.inc.php
	 */

	document::$title[] = t('brands:head_title', 'Brands');
	document::$description = t('brands:meta_description', '');

	breadcrumbs::add(t('title_brands', 'Brands'), document::ilink('brands'));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/brands.inc.php');

	$_page->snippets['brands'] = [];

	$brands_cache_token = cache::token('brands', ['get', 'language'], 'file');
	if (!$_page->snippets['brands'] = cache::get($brands_cache_token)) {

		$_page->snippets['brands'] = database::query(
			"select b.id, b.image, json_value(b.name, '$.". database::input(language::$selected['code']) ."') as name,
				json_value(b.short_description, '$.". database::input(language::$selected['code']) ."') as short_description,
			 	json_value(b.link, '$.". database::input(language::$selected['code']) ."') as link
			from ". DB_TABLE_PREFIX ."brands b
			where status
			order by name;"
		)->fetch_all(function($brand) {
			return [
				'id' => $brand['id'],
				'name' => $brand['name'],
				'image' => $brand['image'] ? 'storage://images/' . $brand['image'] : '',
				'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
				'active' => false,
			];
		});

		cache::set($brands_cache_token, $_page->snippets['brands']);
	}

	echo $_page->render();
