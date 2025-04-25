<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/category.inc.php
	 */

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (empty($_GET['sort'])) {
		$_GET['sort'] = 'price';
	}

	if (empty($_GET['list_style'])) {
		$_GET['list_style'] = 'columns';
	}

	if (empty($_GET['category_id'])) {
		redirect(document::ilink('categories'));
		exit;
	}

	if (!empty($_GET['attributes'])) {
		$_GET['attributes'] = array_map('array_filter', $_GET['attributes']);
		$_GET['attributes'] = array_filter($_GET['attributes']);
	}

	$category = reference::category($_GET['category_id']);

	if (empty($_GET['list_style'])) {
		$_GET['list_style'] = !empty($category->list_style) ? $category->list_style : 'columns';
	}

	if (!$category->id) {
		http_response_code(410);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	if (!$category->status) {
		http_response_code(404);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	document::$title[] = $category->head_title ?: $category->name;
	document::$description = $category->meta_description ?: strip_tags($category->short_description);

	document::$head_tags['canonical'] = '<link rel="canonical" href="'. document::href_ilink('category', ['category_id' => $category->id]) .'">';

	if ($category->image) {
		$og_image = functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category->image, 1200, 630, 'FIT_USE_WHITESPACING');
		document::$snippets['head_tags'][] = '<meta property="og:image" content="'. document::href_rlink(FS_DIR_STORAGE . $og_image) .'">';
	}

	breadcrumbs::add(language::translate('title_categories', 'Categories'), document::ilink('categories'));
	foreach (array_slice($category->path, 0, -1, true) as $category_crumb) {
		breadcrumbs::add($category_crumb->name, document::ilink('category', ['category_id' => $category_crumb->id]));
	}
	breadcrumbs::add($category->name, document::ilink('category', ['category_id' => $category->id]));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/category.inc.php');

	$_page->snippets = [
		'id' => $category->id,
		'parent_id' => $category->parent_id,
		'name' => $category->name,
		'short_description' => $category->short_description,
		'description' => (!empty($category->description) && trim(strip_tags($category->description))) ? $category->description : '',
		'h1_title' => $category->h1_title ?: $category->name,
		'head_title' => $category->head_title ?: $category->name,
		'meta_description' => $category->meta_description ?: $category->short_description,
		'image' => ($category->image ? 'storage://images/' . $category->image : ''),
		'main_category' => [
			'id' => $category->main_category->id,
			'name' => $category->main_category->name,
			'image' => $category->main_category->image ? 'storage://images/' . $category->main_category->image : '',
			'link' => document::ilink('category', ['category_id' => $category->main_category->id]),
		],
		'subcategories' => [],
		'products' => [],
		'brands' => [],
		'attributes' => [],
		'list_style' => $category->list_style,
		'sort_alternatives' => [
			'name' => language::translate('title_name', 'Name'),
			'price' => language::translate('title_price', 'Price'),
			'popularity' => language::translate('title_popularity', 'Popularity'),
			'date' => language::translate('title_date', 'Date'),
		],
		'pagination' => null,
	];

	// Subcategories
	$_page->snippets['subcategories'] = functions::catalog_categories_query($category->id)->fetch_all();

	// Products
	$_page->snippets['products'] = functions::catalog_products_query([
		'categories' => [$category->id] + array_keys($category->descendants),
		'brands' => fallback($_GET['brands']),
		'attributes' => fallback($_GET['attributes']),
		'product_name' => fallback($_GET['product_name']),
		'sort' => $_GET['sort'],
		'campaigns_first' => true,
		'price_range' => [
			'min' => fallback($_GET['price_range']['min]']),
			'max' => fallback($_GET['price_range']['max]']),
		],
	])->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	$_page->snippets['num_products_page'] = count($_page->snippets['products']);
	$_page->snippets['num_products_total'] = $num_rows;
	$_page->snippets['pagination'] = functions::draw_pagination($num_pages);

	// Brands
	$_page->snippets['brands'] = database::query(
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

	// Attributes
	database::query(
		"select cf.attribute_group_id as id, cf.select_multiple, json_value(ag.name, '$.". database::input(language::$selected['code']) ."') as name
		from ". DB_TABLE_PREFIX ."categories_filters cf
		left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = cf.attribute_group_id)
		where category_id = ". (int)$_GET['category_id'] ."
		order by priority;"
	)->each(function($attribute) use (&$_page) {

		$attribute['values'] = database::query(
			"select distinct pa.value_id as id, if(pa.custom_value != '', pa.custom_value, json_value(av.name, '$.". database::input(language::$selected['code']) ."')) as value
			from ". DB_TABLE_PREFIX ."products_attributes pa
			left join ". DB_TABLE_PREFIX ."attribute_values av on (av.id = pa.value_id)
			where product_id in (
				select product_id from ". DB_TABLE_PREFIX ."products_to_categories
				where category_id = ". (int)$_GET['category_id'] ."
			)
			and pa.group_id = ". (int)$attribute['id'] ."
			order by `value`;"
		)->fetch_all();

		if (!$attribute['values']) return;

		$_page->snippets['attributes'][] = $attribute;
	});

	echo $_page->render();
