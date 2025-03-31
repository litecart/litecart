<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_category_filter.inc.php
	 */

	$box_category_filter = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_category_filter.inc.php');

	$box_category_filter->snippets = [
		'brands' => [],
		'attributes' => [],
		'sort_alternatives' => [
			'name' => language::translate('title_name', 'Name'),
			'price' => language::translate('title_price', 'Price'),
			'popularity' => language::translate('title_popularity', 'Popularity'),
			'date' => language::translate('title_date', 'Date'),
		],
	];

	// Brands
	if (!empty(route::$selected['route']) && route::$selected['route'] == 'f:category' && empty($_GET['brand_id'])) {

		$box_category_filter->snippets['brands'] = database::query(
			"select distinct b.id, b.name from ". DB_TABLE_PREFIX ."products p
			left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)
			". (!empty($_GET['category_id']) ? " left join ". DB_TABLE_PREFIX ."products_to_categories pc on pc.product_id = p.id " : "") ."
			where p.status
			and brand_id
			". (!empty($_GET['category_id']) ? "and pc.category_id = " . (int)$_GET['category_id']  : "") ."
			order by b.name asc;"
		)->fetch_all(function($brand) {
			return [
				'id' => $brand['id'],
				'name' => $brand['name'],
				'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
			];
		});
	}

	// Attributes
	if (!empty(route::$selected['route']) && route::$selected['route'] == 'f:category') {

		database::query(
			"select cf.attribute_group_id as id, agi.name as name, cf.select_multiple from ". DB_TABLE_PREFIX ."categories_filters cf
			left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = cf.attribute_group_id and agi.language_code = '". database::input(language::$selected['code']) ."')
			where category_id = ". (int)$_GET['category_id'] ."
			order by priority;"
		)->each(function($attribute) use (&$box_category_filter) {

			$attribute['values'] = database::query(
				"select distinct cf.value_id as id, if(cf.custom_value != '', cf.custom_value, avi.name) as value
				from ". DB_TABLE_PREFIX ."products_attributes cf
				left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = cf.value_id and avi.language_code = '". database::input(language::$selected['code']) ."')
				where product_id in (
					select product_id from ". DB_TABLE_PREFIX ."products_to_categories
					where category_id = ". (int)$_GET['category_id'] ."
				)
				and cf.group_id = ". (int)$attribute['id'] ."
				order by `value`;"
			)->fetch_all();

			if (!$attribute['values']) return;

			$box_category_filter->snippets['attributes'][] = $attribute;
		});
	}

	echo $box_category_filter->render();
