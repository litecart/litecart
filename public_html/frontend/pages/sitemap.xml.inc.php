<?php

	@set_time_limit(300);

	language::set(settings::get('store_language_code'));

	$output = [
		'<?xml version="1.0" encoding="'. mb_http_output() .'"?>',
		'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">',
	];

	$hreflangs = array_map(function($language){
		if ($language['url_type'] == 'none') return;
		return '		<xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('', [], false, [], $language['code']) .'">';
	}, language::$languages);

	$output[] = implode(PHP_EOL, [
		'	<url>',
		'		<loc>'. document::ilink('') .'</loc>',
		$hreflangs,
		'		<lastmod>'. date('Y-m-d') .'</lastmod>',
		'		<changefreq>daily</changefreq>',
		'		<priority>1.0</priority>',
		'	</url>',
	]);

	$category_iterator = function($parent_id=0) use (&$category_iterator, &$output) {
		functions::catalog_categories_query($parent_id)->each(function($category) use (&$category_iterator, &$output) {

			$output[] .= implode(PHP_EOL, array_filter([
				'  <url>',
				'    <loc>'. document::ilink('category', ['category_id' => $category['id']]) .'</loc>',

				implode(PHP_EOL, functions::array_each(language::$languages, function($language) use ($category) {
					if ($language['url_type'] == 'none') return;
					return '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('category', ['category_id' => $category['id']], false, [], $language['code']) .'" />';
				})),

				$category['image'] ? implode(PHP_EOL, [
					'    <image:image>',
					'      <image:loc>'. document::link('storage://images/' . $category['image']) .'</image:loc>',
					'    </image:image>',
				]) : '',

			 '    <lastmod>'. date('Y-m-d', strtotime($category['date_updated'])) .'</lastmod>',
			 '    <changefreq>weekly</changefreq>',
			 '    <priority>1.0</priority>',
			 '  </url>',
			])) . PHP_EOL;

			$category_iterator($category['id']);
		});
	};

	$category_iterator(0);

	database::query(
		"select id, image, date_updated from ". DB_TABLE_PREFIX ."products
		where status
		order by id;"
	)->each(function($product) use (&$output) {

		$output[] = implode(PHP_EOL, array_filter([
			'  <url>',
			'    <loc>'. document::ilink('product', ['product_id' => $product['id']]) .'</loc>',

			implode(PHP_EOL, functions::array_each(language::$languages, function($language) use ($product) {
				if ($language['url_type'] == 'none') return;
				return '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('product', ['product_id' => $product['id']], false, [], $language['code']) .'" />';
			})),

			implode(PHP_EOL, database::query(
				"select filename from ". DB_TABLE_PREFIX ."products_images
				where product_id = ". (int)$product['id'] ."
				order by priority;"
			)->fetch_all(function($image){
				return implode(PHP_EOL, [
					'    <image:image>',
					'      <image:loc>'. document::link('storage://images/' . $image['filename']) .'</image:loc>',
					'    </image:image>',
				]);
			})),

			'    <lastmod>'. date('Y-m-d', strtotime($product['date_updated'])) .'</lastmod>',
			'    <changefreq>weekly</changefreq>',
			'    <priority>0.8</priority>',
			'  </url>',
		])) . PHP_EOL;
	});

	$output[] = '</urlset>';

	ob_clean();
	header('Content-type: application/xml; charset='. mb_http_output());
	echo implode(PHP_EOL, $output);
	exit;
