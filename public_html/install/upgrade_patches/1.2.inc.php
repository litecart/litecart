<?php

	perform_action('delete', [
		FS_DIR_APP . 'pages/ajax/index.html',
		FS_DIR_APP . 'pages/ajax/cart.json.php',
		FS_DIR_APP . 'pages/ajax/checkout_cart.html.php',
		FS_DIR_APP . 'pages/ajax/checkout_customer.html.php',
		FS_DIR_APP . 'pages/ajax/checkout_payment.html.php',
		FS_DIR_APP . 'pages/ajax/checkout_shipping.html.php',
		FS_DIR_APP . 'pages/ajax/checkout_summary.html.php',
		FS_DIR_APP . 'pages/ajax/get_address.json.php',
		FS_DIR_APP . 'pages/ajax/option_values.json.php',
		FS_DIR_APP . 'pages/ajax/zones.json.php',
		FS_DIR_APP . 'pages/feeds/index.html',
		FS_DIR_APP . 'pages/feeds/sitemap.xml.php',
		FS_DIR_APP . 'pages/includes/boxes/account.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/also_purchased_products.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/campaigns.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/cart.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/categories.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/category_tree.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/filter.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/footer_categories.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/footer_information.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/footer_manufacturers.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/latest_products.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/login.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/logotypes.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/manufacturers.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/most_popular.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/region.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/search.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/similar_products.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/site_links.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/site_menu.inc.php',
		FS_DIR_APP . 'pages/includes/boxes/slider.inc.php',
		FS_DIR_APP . 'pages/includes/library/lib_seo_links.inc.php',
		FS_DIR_APP . 'pages/includes/modules/seo_links/url_category.inc.php',
		FS_DIR_APP . 'pages/includes/modules/seo_links/url_customer_service.inc.php',
		FS_DIR_APP . 'pages/includes/modules/seo_links/url_information.inc.php',
		FS_DIR_APP . 'pages/includes/modules/seo_links/url_manufacturer.inc.php',
		FS_DIR_APP . 'pages/includes/modules/seo_links/url_product.inc.php',
		FS_DIR_APP . 'pages/includes/modules/seo_links/url_search.inc.php',
		FS_DIR_APP . 'pages/includes/printable_order_copy.inc.php',
		FS_DIR_APP . 'pages/includes/printable_packing_slip.inc.php',
		FS_DIR_APP . 'pages/categories.php',
		FS_DIR_APP . 'pages/category.php',
		FS_DIR_APP . 'pages/checkout.php',
		FS_DIR_APP . 'pages/create_account.php',
		FS_DIR_APP . 'pages/customer_service.php',
		FS_DIR_APP . 'pages/edit_account.php',
		FS_DIR_APP . 'pages/error_document.php',
		FS_DIR_APP . 'pages/information.php',
		FS_DIR_APP . 'pages/login.php',
		FS_DIR_APP . 'pages/logout.php',
		FS_DIR_APP . 'pages/manufacturer.php',
		FS_DIR_APP . 'pages/manufacturers.php',
		FS_DIR_APP . 'pages/order_history.php',
		FS_DIR_APP . 'pages/order_process.php',
		FS_DIR_APP . 'pages/order_success.php',
		FS_DIR_APP . 'pages/printable_order_copy.php',
		FS_DIR_APP . 'pages/product.php',
		FS_DIR_APP . 'pages/push_jobs.php',
		FS_DIR_APP . 'pages/search.php',
		FS_DIR_APP . 'pages/select_region.php',
	]);

	perform_action('move', [
		FS_DIR_APP . 'pages/ajax' => FS_DIR_APP . 'pages/ajax.deleteme',
		FS_DIR_APP . 'pages/feeds' => FS_DIR_APP . 'pages/feeds.deleteme',
	]);

	perform_action('modify', [
		FS_DIR_ADMIN . '.htaccess' => [
			[
				'search'  => "# Denied content",
				'replace' => "# Solve 401 rewrite and auth conflict on some machines" . PHP_EOL
									.  "ErrorDocument 401 \"Access Forbidden\"" . PHP_EOL
									.  PHP_EOL
									.  "# Denied content",
			],
		],
		FS_DIR_APP . 'includes/config.inc.php' => [
			[
				'search'  => "  define('WS_DIR_INCLUDES',    WS_DIR_APP . 'includes/');",
				'replace' => "  define('WS_DIR_INCLUDES',    WS_DIR_APP . 'includes/');" . PHP_EOL
									 . "  define('WS_DIR_PAGES',       WS_DIR_APP . 'pages/');",
			],
			[
				'search'  => "  define('WS_DIR_REFERENCES',  WS_DIR_INCLUDES  . 'references/');",
				'replace' => "  define('WS_DIR_REFERENCES',  WS_DIR_INCLUDES  . 'references/');" . PHP_EOL
									 . "  define('WS_DIR_ROUTES',      WS_DIR_INCLUDES  . 'routes/');",
			],
			[
				'search'  => "  define('DB_SERVER',",
				'replace' => "  define('DB_TYPE', 'mysql');" . PHP_EOL
									 . "  define('DB_SERVER',",
			],
			[
				'search'  => "  define('DB_DATABASE_CHARSET',",
				'replace' => "  define('DB_CONNECTION_CHARSET',",
			],
		],
		FS_DIR_APP . 'pages/.htaccess' => [
			[
				'search'  => "ErrorDocument 403 ". WS_DIR_APP . "error_document.php?code=403",
				'replace' => "ErrorDocument 403 ". WS_DIR_APP . "index.php/error_document?code=403",
			],
			[
				'search'  => "ErrorDocument 404 ". WS_DIR_APP . "error_document.php?code=404",
				'replace' => "ErrorDocument 404 ". WS_DIR_APP . "index.php/error_document?code=404",
			],
			[
				'search'  => "ErrorDocument 410 ". WS_DIR_APP . "error_document.php?code=410",
				'replace' => "ErrorDocument 410 ". WS_DIR_APP . "index.php/error_document?code=410",
			],
			[
				'search'  => "  RewriteRule ^(?:[a-z]{2}/)?.*-c-([0-9]+)/?$ category.php?category_id=$1&%{QUERY_STRING} [L]",
				'replace' => "  RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]",
				'regexp'  => true,
			],
			[
				'search'  => "RewriteRule ^(?:[a-z]{2}/)?.*-m-([0-9]+)/?$ manufacturer.php?manufacturer_id=$1&%{QUERY_STRING} [L]",
				'replace' => "",
				'regexp'  => true,
			],
			[
				'search'  => "RewriteRule ^(?:[a-z]{2}/)?.*-i-([0-9]+)$ information.php?page_id=$1&%{QUERY_STRING} [L]",
				'replace' => "",
				'regexp'  => true,
			],
			[
				'search'  => "RewriteRule ^(?:[a-z]{2}/)?.*-s-([0-9]+)$ customer_service.php?page_id=$1&%{QUERY_STRING} [L]",
				'replace' => "",
				'regexp'  => true,
			],
			[
				'search'  => "RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]",
				'replace' => "",
				'regexp'  => true,
			],
			[
				'search'  => "RewriteRule ^(?:[a-z]{2}/)?search/(.*)?$ search.php?query=$1&%{QUERY_STRING} [L]",
				'replace' => "",
				'regexp'  => true,
			],
			[
				'search'  => "RewriteRule ^(?:[a-z]{2}/)?(.*) $1?%{QUERY_STRING} [L]",
				'replace' => "",
				'regexp'  => true,
			],
		],
	], 'abort');
