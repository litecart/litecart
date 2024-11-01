<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/brands.inc.php
	 */

	document::$title[] = language::translate('brands:head_title', 'Brands');
	document::$description = language::translate('brands:meta_description', '');

	breadcrumbs::add(language::translate('title_brands', 'Brands'), document::ilink('brands'));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/brands.inc.php');

	$_page->snippets['brands'] = [];

	$brands_cache_token = cache::token('brands', ['get', 'language'], 'file');
	if (!$_page->snippets['brands'] = cache::get($brands_cache_token)) {

		$_page->snippets['brands'] = database::query(
			"select b.id, b.name, b.image, bi.short_description, bi.link
			from ". DB_TABLE_PREFIX ."brands b
			left join ". DB_TABLE_PREFIX ."brands_info bi on (bi.brand_id = b.id and bi.language_code = '". language::$selected['code'] ."')
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
