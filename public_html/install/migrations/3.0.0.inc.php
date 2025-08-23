<?php

	perform_action('delete', [
		FS_DIR_ADMIN . 'addons.widget/addons.inc.php',
		FS_DIR_ADMIN . 'addons.widget/config.inc.php',
		FS_DIR_ADMIN . 'addons.widget/index.html',
		FS_DIR_ADMIN . 'appearance.app/config.inc.php',
		FS_DIR_ADMIN . 'appearance.app/edit_styling.inc.php',
		FS_DIR_ADMIN . 'appearance.app/favicon.inc.php',
		FS_DIR_ADMIN . 'appearance.app/logotype.inc.php',
		FS_DIR_ADMIN . 'appearance.app/template.inc.php',
		FS_DIR_ADMIN . 'appearance.app/template_settings.inc.php',
		FS_DIR_ADMIN . 'catalog.app/attribute_groups.inc.php',
		FS_DIR_ADMIN . 'catalog.app/attribute_values.json.inc.php',
		FS_DIR_ADMIN . 'catalog.app/catalog.inc.php',
		FS_DIR_ADMIN . 'catalog.app/categories.json.inc.php',
		FS_DIR_ADMIN . 'catalog.app/category_picker.inc.php',
		FS_DIR_ADMIN . 'catalog.app/config.inc.php',
		FS_DIR_ADMIN . 'catalog.app/csv.inc.php',
		FS_DIR_ADMIN . 'catalog.app/delivery_statuses.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_attribute_group.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_category.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_delivery_status.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_manufacturer.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_product.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_quantity_unit.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_sold_out_status.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_supplier.inc.php',
		FS_DIR_ADMIN . 'catalog.app/index.html',
		FS_DIR_ADMIN . 'catalog.app/products.json.inc.php',
		FS_DIR_ADMIN . 'catalog.app/quantity_units.inc.php',
		FS_DIR_ADMIN . 'catalog.app/sold_out_statuses.inc.php',
		FS_DIR_ADMIN . 'catalog.app/suppliers.inc.php',
		FS_DIR_ADMIN . 'countries.app/config.inc.php',
		FS_DIR_ADMIN . 'countries.app/countries.inc.php',
		FS_DIR_ADMIN . 'countries.app/edit_country.inc.php',
		FS_DIR_ADMIN . 'countries.app/index.html',
		FS_DIR_ADMIN . 'currencies.app/config.inc.php',
		FS_DIR_ADMIN . 'currencies.app/currencies.inc.php',
		FS_DIR_ADMIN . 'currencies.app/edit_currency.inc.php',
		FS_DIR_ADMIN . 'currencies.app/index.html',
		FS_DIR_ADMIN . 'customers.app/config.inc.php',
		FS_DIR_ADMIN . 'customers.app/csv.inc.php',
		FS_DIR_ADMIN . 'customers.app/customers.inc.php',
		FS_DIR_ADMIN . 'customers.app/customers.json.inc.php',
		FS_DIR_ADMIN . 'customers.app/customer_picker.inc.php',
		FS_DIR_ADMIN . 'customers.app/edit_customer.inc.php',
		FS_DIR_ADMIN . 'customers.app/get_address.json.inc.php',
		FS_DIR_ADMIN . 'customers.app/index.html',
		FS_DIR_ADMIN . 'customers.app/mailchimp.png',
		FS_DIR_ADMIN . 'customers.app/newsletter.inc.php',
		FS_DIR_ADMIN . 'customers.app/newsletter_recipients.inc.php',
		FS_DIR_ADMIN . 'discussions.widget/config.inc.php',
		FS_DIR_ADMIN . 'discussions.widget/discussions.inc.php',
		FS_DIR_ADMIN . 'discussions.widget/index.html',
		FS_DIR_ADMIN . 'geo_zones.app/config.inc.php',
		FS_DIR_ADMIN . 'geo_zones.app/edit_geo_zone.inc.php',
		FS_DIR_ADMIN . 'geo_zones.app/geo_zones.inc.php',
		FS_DIR_ADMIN . 'geo_zones.app/index.html',
		FS_DIR_ADMIN . 'graphs.widget/config.inc.php',
		FS_DIR_ADMIN . 'graphs.widget/graphs.inc.php',
		FS_DIR_ADMIN . 'graphs.widget/index.html',
		FS_DIR_ADMIN . 'languages.app/config.inc.php',
		FS_DIR_ADMIN . 'languages.app/edit_language.inc.php',
		FS_DIR_ADMIN . 'languages.app/index.html',
		FS_DIR_ADMIN . 'languages.app/languages.inc.php',
		FS_DIR_ADMIN . 'languages.app/storage_encoding.inc.php',
		FS_DIR_ADMIN . 'modules.app/config.inc.php',
		FS_DIR_ADMIN . 'modules.app/edit_module.inc.php',
		FS_DIR_ADMIN . 'modules.app/index.html',
		FS_DIR_ADMIN . 'modules.app/modules.inc.php',
		FS_DIR_ADMIN . 'modules.app/run_job.inc.php',
		FS_DIR_ADMIN . 'orders.app/add_product.inc.php',
		FS_DIR_ADMIN . 'orders.app/config.inc.php',
		FS_DIR_ADMIN . 'orders.app/edit_order.inc.php',
		FS_DIR_ADMIN . 'orders.app/edit_order_status.inc.php',
		FS_DIR_ADMIN . 'orders.app/index.html',
		FS_DIR_ADMIN . 'orders.app/orders.inc.php',
		FS_DIR_ADMIN . 'orders.app/order_statuses.inc.php',
		FS_DIR_ADMIN . 'orders.app/printable_order_copy.inc.php',
		FS_DIR_ADMIN . 'orders.app/printable_packing_slip.inc.php',
		FS_DIR_ADMIN . 'orders.app/product_picker.inc.php',
		FS_DIR_ADMIN . 'orders.widget/config.inc.php',
		FS_DIR_ADMIN . 'orders.widget/index.html',
		FS_DIR_ADMIN . 'orders.widget/orders.inc.php',
		FS_DIR_ADMIN . 'pages.app/config.inc.php',
		FS_DIR_ADMIN . 'pages.app/csv.inc.php',
		FS_DIR_ADMIN . 'pages.app/edit_page.inc.php',
		FS_DIR_ADMIN . 'pages.app/index.html',
		FS_DIR_ADMIN . 'pages.app/pages.inc.php',
		FS_DIR_ADMIN . 'reports.app/config.inc.php',
		FS_DIR_ADMIN . 'reports.app/index.html',
		FS_DIR_ADMIN . 'reports.app/monthly_sales.inc.php',
		FS_DIR_ADMIN . 'reports.app/most_shopping_customers.inc.php',
		FS_DIR_ADMIN . 'reports.app/most_sold_products.inc.php',
		FS_DIR_ADMIN . 'settings.app/config.inc.php',
		FS_DIR_ADMIN . 'settings.app/index.html',
		FS_DIR_ADMIN . 'settings.app/settings.inc.php',
		FS_DIR_ADMIN . 'slides.app/config.inc.php',
		FS_DIR_ADMIN . 'slides.app/edit_slide.inc.php',
		FS_DIR_ADMIN . 'slides.app/index.html',
		FS_DIR_ADMIN . 'slides.app/slides.inc.php',
		FS_DIR_ADMIN . 'stats.widget/config.inc.php',
		FS_DIR_ADMIN . 'stats.widget/index.html',
		FS_DIR_ADMIN . 'stats.widget/stats.inc.php',
		FS_DIR_ADMIN . 'tax.app/config.inc.php',
		FS_DIR_ADMIN . 'tax.app/edit_tax_class.inc.php',
		FS_DIR_ADMIN . 'tax.app/edit_tax_rate.inc.php',
		FS_DIR_ADMIN . 'tax.app/index.html',
		FS_DIR_ADMIN . 'tax.app/tax_classes.inc.php',
		FS_DIR_ADMIN . 'tax.app/tax_rates.inc.php',
		FS_DIR_ADMIN . 'tax.app/tax_rates.json.inc.php',
		FS_DIR_ADMIN . 'translations.app/config.inc.php',
		FS_DIR_ADMIN . 'translations.app/collections.inc.php',
		FS_DIR_ADMIN . 'translations.app/csv.inc.php',
		FS_DIR_ADMIN . 'translations.app/index.html',
		FS_DIR_ADMIN . 'translations.app/scan.inc.php',
		FS_DIR_ADMIN . 'translations.app/translations.inc.php',
		FS_DIR_ADMIN . 'users.app/config.inc.php',
		FS_DIR_ADMIN . 'users.app/edit_user.inc.php',
		FS_DIR_ADMIN . 'users.app/index.html',
		FS_DIR_ADMIN . 'users.app/users.inc.php',
		FS_DIR_ADMIN . 'vmods.app/config.inc.php',
		FS_DIR_ADMIN . 'vmods.app/configure.inc.php',
		FS_DIR_ADMIN . 'vmods.app/download.inc.php',
		FS_DIR_ADMIN . 'vmods.app/edit_vmod.inc.php',
		FS_DIR_ADMIN . 'vmods.app/index.html',
		FS_DIR_ADMIN . 'vmods.app/sources.inc.php',
		FS_DIR_ADMIN . 'vmods.app/view.inc.php',
		FS_DIR_ADMIN . 'vmods.app/vmods.inc.php',
		FS_DIR_ADMIN . 'about.php',
		FS_DIR_ADMIN . 'index.php',
		FS_DIR_ADMIN . 'login.php',
		FS_DIR_ADMIN . 'logout.php',
		FS_DIR_ADMIN . 'search_results.json.php',
		FS_DIR_APP . 'ext/chartist/',
		FS_DIR_APP . 'ext/featherlight/',
		FS_DIR_APP . 'ext/fontawesome/',
		FS_DIR_APP . 'ext/jquery/',
		FS_DIR_APP . 'ext/trumbowyg/',
		FS_DIR_APP . 'ext/index.html',
		FS_DIR_APP . 'cache/.htaccess',
		FS_DIR_APP . 'cache/index.html',
		FS_DIR_APP . 'data/.htaccess',
		FS_DIR_APP . 'data/bad_urls.txt',
		FS_DIR_APP . 'data/blacklist.txt',
		FS_DIR_APP . 'data/whitelist.txt',
		FS_DIR_APP . 'data/captcha.ttf',
		FS_DIR_APP . 'data/index.html',
		FS_DIR_APP . 'images/index.html',
		FS_DIR_APP . 'images/countries/',
		FS_DIR_APP . 'images/languages/',
		FS_DIR_APP . 'includes/boxes/box_account_links.inc.php',
		FS_DIR_APP . 'includes/boxes/box_also_purchased_products.inc.php',
		FS_DIR_APP . 'includes/boxes/box_campaign_products.inc.php',
		FS_DIR_APP . 'includes/boxes/box_cart.inc.php',
		FS_DIR_APP . 'includes/boxes/box_categories.inc.php',
		FS_DIR_APP . 'includes/boxes/box_category_tree.inc.php',
		FS_DIR_APP . 'includes/boxes/box_contact_us.inc.php',
		FS_DIR_APP . 'includes/boxes/box_customer_service_links.inc.php',
		FS_DIR_APP . 'includes/boxes/box_filter.inc.php',
		FS_DIR_APP . 'includes/boxes/box_information_links.inc.php',
		FS_DIR_APP . 'includes/boxes/box_latest_products.inc.php',
		FS_DIR_APP . 'includes/boxes/box_manufacturer_links.inc.php',
		FS_DIR_APP . 'includes/boxes/box_manufacturer_logotypes.inc.php',
		FS_DIR_APP . 'includes/boxes/box_newsletter_subscribe.inc.php',
		FS_DIR_APP . 'includes/boxes/box_popular_products.inc.php',
		FS_DIR_APP . 'includes/boxes/box_recently_viewed_products.inc.php',
		FS_DIR_APP . 'includes/boxes/box_region.inc.php',
		FS_DIR_APP . 'includes/boxes/box_similar_products.inc.php',
		FS_DIR_APP . 'includes/boxes/site_footer.inc.php',
		FS_DIR_APP . 'includes/boxes/site_navigation.inc.php',
		FS_DIR_APP . 'includes/boxes/box_slides.inc.php',
		FS_DIR_APP . 'includes/boxes/index.html',
		FS_DIR_APP . 'includes/functions/func_password.inc.php',
		FS_DIR_APP . 'includes/functions/func_general.inc.php',
		FS_DIR_APP . 'includes/functions/func_reference.inc.php',
		FS_DIR_APP . 'includes/library/index.html',
		FS_DIR_APP . 'includes/library/lib_breadcrumbs.inc.php',
		FS_DIR_APP . 'includes/library/lib_cache.inc.php',
		FS_DIR_APP . 'includes/library/lib_cart.inc.php',
		FS_DIR_APP . 'includes/library/lib_compression.inc.php',
		FS_DIR_APP . 'includes/library/lib_currency.inc.php',
		FS_DIR_APP . 'includes/library/lib_customer.inc.php',
		FS_DIR_APP . 'includes/library/lib_database.inc.php',
		FS_DIR_APP . 'includes/library/lib_document.inc.php',
		FS_DIR_APP . 'includes/library/lib_event.inc.php',
		FS_DIR_APP . 'includes/library/lib_form.inc.php',
		FS_DIR_APP . 'includes/library/lib_functions.inc.php',
		FS_DIR_APP . 'includes/library/lib_language.inc.php',
		FS_DIR_APP . 'includes/library/lib_length.inc.php',
		FS_DIR_APP . 'includes/library/lib_notices.inc.php',
		FS_DIR_APP . 'includes/library/lib_reference.inc.php',
		FS_DIR_APP . 'includes/library/lib_route.inc.php',
		FS_DIR_APP . 'includes/library/lib_session.inc.php',
		FS_DIR_APP . 'includes/library/lib_settings.inc.php',
		FS_DIR_APP . 'includes/library/lib_stats.inc.php',
		FS_DIR_APP . 'includes/library/lib_tax.inc.php',
		FS_DIR_APP . 'includes/library/lib_user.inc.php',
		FS_DIR_APP . 'includes/library/lib_vmod.inc.php',
		FS_DIR_APP . 'includes/library/lib_volume.inc.php',
		FS_DIR_APP . 'includes/library/lib_weight.inc.php',
		FS_DIR_APP . 'includes/modules/order_total/ot_payment_fee.inc.php',
		FS_DIR_APP . 'includes/modules/order_total/ot_shipping_fee.inc.php',
		FS_DIR_APP . 'includes/modules/order_total/ot_subtotal.inc.php',
		FS_DIR_APP . 'includes/modules/order_total/index.html',
		FS_DIR_APP . 'includes/modules/mod_order_total.inc.php',
		FS_DIR_APP . 'includes/routes/index.html',
		FS_DIR_APP . 'includes/routes/url_category.inc.php',
		FS_DIR_APP . 'includes/routes/url_index.inc.php',
		FS_DIR_APP . 'includes/routes/url_product.inc.php',
		FS_DIR_APP . 'includes/routes/url_category.inc.php',
		FS_DIR_APP . 'includes/routes/url_information.inc.php',
		FS_DIR_APP . 'includes/routes/url_push_jobs.inc.php',
		FS_DIR_APP . 'includes/routes/url_customer_service.inc.php',
		FS_DIR_APP . 'includes/routes/url_manufacturer.inc.php',
		FS_DIR_APP . 'includes/routes/url_error_document.inc.php',
		FS_DIR_APP . 'includes/routes/url_order_process.inc.php',
		FS_DIR_APP . 'includes/templates/default.admin/',
		FS_DIR_APP . 'includes/templates/default.catalog/css/app.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/app.min.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/app.min.css.map',
		FS_DIR_APP . 'includes/templates/default.catalog/css/checkout.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/checkout.min.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/checkout.min.css.map',
		FS_DIR_APP . 'includes/templates/default.catalog/css/framework.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/framework.min.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/framework.min.css.map',
		FS_DIR_APP . 'includes/templates/default.catalog/css/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/css/printable.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/printable.min.css',
		FS_DIR_APP . 'includes/templates/default.catalog/css/printable.min.css.map',
		FS_DIR_APP . 'includes/templates/default.catalog/css/variables.css',
		FS_DIR_APP . 'includes/templates/default.catalog/images',
		FS_DIR_APP . 'includes/templates/default.catalog/js/app.js',
		FS_DIR_APP . 'includes/templates/default.catalog/js/app.min.js',
		FS_DIR_APP . 'includes/templates/default.catalog/js/app.min.js.map',
		FS_DIR_APP . 'includes/templates/default.catalog/js/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/fonts/.htaccess',
		FS_DIR_APP . 'includes/templates/default.catalog/fonts/asap-v30-latin_latin-ext-700.woff2',
		FS_DIR_APP . 'includes/templates/default.catalog/fonts/asap-v30-latin_latin-ext-regular.woff2',
		FS_DIR_APP . 'includes/templates/default.catalog/fonts/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/images/cart.svg',
		FS_DIR_APP . 'includes/templates/default.catalog/images/cart_filled.svg',
		FS_DIR_APP . 'includes/templates/default.catalog/images/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/images/loader.svg',
		FS_DIR_APP . 'includes/templates/default.catalog/layouts/ajax.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/layouts/blank.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/layouts/checkout.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/layouts/default.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/layouts/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/layouts/printable.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/less/app/',
		FS_DIR_APP . 'includes/templates/default.catalog/less/app.less',
		FS_DIR_APP . 'includes/templates/default.catalog/less/checkout.less',
		FS_DIR_APP . 'includes/templates/default.catalog/less/framework/',
		FS_DIR_APP . 'includes/templates/default.catalog/less/framework.less',
		FS_DIR_APP . 'includes/templates/default.catalog/less/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/less/printable.less',
		FS_DIR_APP . 'includes/templates/default.catalog/less/variables.less',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/categories.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/category.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/checkout.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/create_account.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/customer_service.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/development_mode.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/edit_account.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/error_document.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/index.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/information.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/login.ajax.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/login.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/maintenance_mode.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/manufacturer.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/manufacturers.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/newsletter.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/order.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/order_history.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/order_success.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/page.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/printable_order_copy.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/printable_packing_slip.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/product.ajax.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/product.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/regional_settings.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/reset_password.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/pages/search_results.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_account_links.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_account_login.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_also_purchased_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_campaign_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_cart.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_categories.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_category_tree.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_checkout_cart.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_checkout_customer.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_checkout_payment.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_checkout_shipping.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_checkout_summary.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_contact_us.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_cookie_notice.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_customer_service_links.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_filter.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_information_links.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_latest_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturer_links.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturer_logotypes.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_newsletter_subscribe.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_popular_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_product.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_recently_viewed_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_region.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_similar_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/site_footer.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/site_navigation.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_slides.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/breadcrumbs.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/views/listing_category.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/listing_product_column.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/listing_product_row.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/notices.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/pagination.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/.development',
		FS_DIR_APP . 'includes/templates/default.catalog/index.html',
		FS_DIR_APP . 'includes/templates/default.catalog/config.inc.php',
		FS_DIR_APP . 'includes/templates/index.html',
		FS_DIR_APP . 'includes/wrappers/index.html',
		FS_DIR_APP . 'includes/wrappers/wrap_http.inc.php',
		FS_DIR_APP . 'includes/wrappers/wrap_smtp.inc.php',
		FS_DIR_APP . 'logs/.htaccess',
		FS_DIR_APP . 'logs/index.html',
		FS_DIR_APP . 'pages/ajax/cart.json.inc.php',
		FS_DIR_APP . 'pages/ajax/checkout_cart.inc.php',
		FS_DIR_APP . 'pages/ajax/checkout_customer.inc.php',
		FS_DIR_APP . 'pages/ajax/checkout_payment.inc.php',
		FS_DIR_APP . 'pages/ajax/checkout_shipping.inc.php',
		FS_DIR_APP . 'pages/ajax/checkout_summary.inc.php',
		FS_DIR_APP . 'pages/ajax/get_address.json.inc.php',
		FS_DIR_APP . 'pages/ajax/product_options_stock.json.inc.php',
		FS_DIR_APP . 'pages/ajax/index.html',
		FS_DIR_APP . 'pages/ajax/zones.json.inc.php',
		FS_DIR_APP . 'pages/feeds/index.html',
		FS_DIR_APP . 'pages/feeds/sitemap.xml.inc.php',
		FS_DIR_APP . 'pages/categories.inc.php',
		FS_DIR_APP . 'pages/category.inc.php',
		FS_DIR_APP . 'pages/checkout.inc.php',
		FS_DIR_APP . 'pages/create_account.inc.php',
		FS_DIR_APP . 'pages/customer_service.inc.php',
		FS_DIR_APP . 'pages/edit_account.inc.php',
		FS_DIR_APP . 'pages/error_document.inc.php',
		FS_DIR_APP . 'pages/index.html',
		FS_DIR_APP . 'pages/index.inc.php',
		FS_DIR_APP . 'pages/information.inc.php',
		FS_DIR_APP . 'pages/login.inc.php',
		FS_DIR_APP . 'pages/logout.inc.php',
		FS_DIR_APP . 'pages/maintenance_mode.inc.php',
		FS_DIR_APP . 'pages/manufacturer.inc.php',
		FS_DIR_APP . 'pages/manufacturers.inc.php',
		FS_DIR_APP . 'pages/order.inc.php',
		FS_DIR_APP . 'pages/order_history.inc.php',
		FS_DIR_APP . 'pages/order_process.inc.php',
		FS_DIR_APP . 'pages/order_success.inc.php',
		FS_DIR_APP . 'pages/printable_order_copy.inc.php',
		FS_DIR_APP . 'pages/printable_packing_slip.inc.php',
		FS_DIR_APP . 'pages/product.inc.php',
		FS_DIR_APP . 'pages/push_jobs.inc.php',
		FS_DIR_APP . 'pages/regional_settings.inc.php',
		FS_DIR_APP . 'pages/reset_password.inc.php',
		FS_DIR_APP . 'pages/search.inc.php',
		FS_DIR_APP . 'vmods/.cache/',
		FS_DIR_APP . 'vqmod/',
		FS_DIR_APP . 'robots.txt',
	]);

	// Copy default storage directory
	perform_action('copy', [
		FS_DIR_APP . 'install/data/default/storage/' => FS_DIR_APP . 'storage/',
		FS_DIR_APP . 'install/data/default/storage/images/favicon*' => FS_DIR_APP . 'storage/images/',
		FS_DIR_APP . 'install/data/default/storage/images/no_image.svg' => FS_DIR_APP . 'storage/images/',
	]);

	// Move files to trash
	mkdir(FS_DIR_APP . '.deleteme', 0777, true);

	perform_action('move', [
		FS_DIR_APP . 'includes/config.inc.php' => FS_DIR_APP . '.deleteme/config.inc.php',
		FS_DIR_APP . 'includes/modules/order_total/' => FS_DIR_APP . '.deleteme/order_total/',
		FS_DIR_APP . 'includes/routes/*' => FS_DIR_APP . '.deleteme/routes/',
		FS_DIR_APP . '.htaccess' => FS_DIR_APP . '.deleteme/.htaccess',
		FS_DIR_APP . 'favicon.ico' => FS_DIR_APP . '.deleteme/favicon.ico',
	]);

	echo '<p>Writing fresh new config file... ';

	$timezone = database::query(
		"select `value` from ". DB_TABLE_PREFIX ."settings
		where `key` = 'store_timezone'
		limit 1;"
	)->fetch('value');

	$config = strtr(file_get_contents(FS_DIR_APP .'install/config'), [
		'{STORAGE_FOLDER}' => 'storage',
		'{ADMIN_FOLDER}' => BACKEND_ALIAS,
		'{DB_SERVER}' => DB_SERVER,
		'{DB_USERNAME}' => DB_USERNAME,
		'{DB_PASSWORD}' => DB_PASSWORD,
		'{DB_DATABASE}' => DB_DATABASE,
		'{DB_TABLE_PREFIX}' => DB_TABLE_PREFIX,
		'{CLIENT_IP}' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
		'{TIMEZONE}' => $timezone,
	]);

	if (file_put_contents(FS_DIR_APP . 'storage/config.inc.php', $config) !== false) {
		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} else {
		throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
	}

	echo '<p>Writing fresh new .htaccess file... ';
	$htaccess = file_get_contents(__DIR__.'/../htaccess');

	$htaccess = strtr($htaccess, [
		'{WS_DIR_APP}' => WS_DIR_APP,
		'{FS_DIR_APP}' => FS_DIR_APP,
	]);

	if (file_put_contents(FS_DIR_APP . '.htaccess', $htaccess)) {
		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	} else {
		throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
	}

	// Move files to new locations

	foreach (glob(FS_DIR_ADMIN . '*.app') as $directory) {

		// Remove empty directories
		if (!glob($directory . '/*')) {
			perform_action('delete', [$directory]);
			continue;
		}

		perform_action('move', [$directory.'/*' => FS_DIR_APP . 'backend/apps/' . preg_replace('#\.app$#', '', basename($directory)) .'/']);
	}

	foreach (glob(FS_DIR_ADMIN . '*.widget') as $directory) {

		// Remove empty directories
		if (!glob($directory . '/*')) {
			perform_action('delete', [$directory]);
			continue;
		}

		perform_action('move', [$directory.'/*' => FS_DIR_APP . 'backend/widgets/' . preg_replace('#\.widget$#', '', basename($directory)) .'/']);
	}

	foreach (glob(FS_DIR_APP . 'cache/*') as $file) {
		perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'cache/', '#') .'#', FS_DIR_APP . 'storage/cache/', $file)]);
	}

	foreach (glob(FS_DIR_APP . 'data/*') as $file) {
		perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'data/', '#') .'#', FS_DIR_APP . 'storage/', $file)]);
	}

	foreach (glob(FS_DIR_APP . 'ext/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'assets/' . basename($file)]);
	}

	foreach (glob(FS_DIR_APP . 'images/*') as $file) {
		perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'images/', '#') .'#', FS_DIR_APP . 'storage/images/', $file)]);
	}

	foreach (glob(FS_DIR_APP . 'logs/*') as $file) {
		perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'logs/', '#') .'#', FS_DIR_APP . 'storage/logs/', $file)]);
	}

	foreach (glob(FS_DIR_APP . 'includes/boxes/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/partials/' . basename($file)]);
	}

	foreach (glob(FS_DIR_APP . 'includes/library/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'inlcudes/nodes/' . preg_replace('#^lib_#', 'nod_', basename($file))]);
	}

	foreach (glob(FS_DIR_APP . 'includes/routes/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/routes/' . basename($file)]);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/css/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/css/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/images/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/images/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/layouts/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/layouts/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/less/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/less/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/js/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/js/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/pages/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/pages/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/views/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/default/views/']);
	}

	foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/' . preg_replace('#\.catalog#', '', basename($file))]);
	}

	foreach (glob(FS_DIR_APP . 'vmods/*') as $file) {
		perform_action('move', [$file => FS_DIR_APP . 'storage/vmods/' . basename($file)]);
	}

	foreach (glob(FS_DIR_APP . 'pages/*') as $file) {
		perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'pages/', '#') .'#', FS_DIR_APP . 'frontend/pages/', $file)]);
	}

	perform_action('delete', [
		FS_DIR_ADMIN,
		FS_DIR_APP . 'cache/',
		FS_DIR_APP . 'data/',
		FS_DIR_APP . 'images/',
		FS_DIR_APP . 'includes/boxes/',
		FS_DIR_APP . 'includes/library/',
		FS_DIR_APP . 'includes/routes/',
		FS_DIR_APP . 'includes/templates/',
		FS_DIR_APP . 'logs/',
		FS_DIR_APP . 'ext/',
		FS_DIR_APP . 'pages/',
		FS_DIR_APP . 'vmods/',
	]);

	// Change indentation from spaces to tabs in files
	foreach ([
		FS_DIR_APP . 'storage/config_deleteme.inc.php',
		FS_DIR_APP . 'storage/vmods/*.xml',
		FS_DIR_APP . '.htaccess',
	] as $file_pattern) {

		perform_action('custom', [
			$file_pattern => function($file){

				$contents = file_get_contents($file);

				while (true) {
					$contents = preg_replace('#^(\t*)  #m', "$1\t", addcslashes($contents, '\\'), -1, $replacements);
					if (!$replacements) break;
				}

				$contents = preg_replace('#^(\t*)//#m', "$1\t//", $contents);

				return (bool)file_put_contents($file, $contents);
			},
		], 'skip');
	}

	perform_action('modify', [
		FS_DIR_APP . '.htaccess' => [
			[
				'search'  => 'RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]',
				'replace' => 'RewriteRule ^ index.php [QSA,L]',
			],
		],
	]);

	// Change VARCHAR(256) to VARCHAR(248) (InnoDB limitation for index length)
	database::query(
		"select TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT from information_schema.COLUMNS
		where TABLE_SCHEMA = '". DB_DATABASE ."'
		and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
		and COLUMN_TYPE like 'varchar(256)'
		order by TABLE_NAME;"
	)->each(function($column){
		database::query(
			"alter table `". $column['TABLE_NAME'] ."`
			change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". strtr($column['COLUMN_TYPE'], ['256' => '248']) ." ". (($column['IS_NULLABLE'] == 'YES') ? "NULL" : "NOT NULL") . (!empty($column['COLUMN_DEFAULT']) ? " DEFAULT " . $column['COLUMN_DEFAULT'] : "") .";"
		);
	});

	// Change VARCHAR to CHAR
	database::query(
		"select TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT from information_schema.COLUMNS
		where TABLE_SCHEMA = '". DB_DATABASE ."'
		and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
		and (COLUMN_NAME like '%country_code%' or COLUMN_NAME like '%language_code%')
		and COLUMN_TYPE = 'varchar(2)'
		order by TABLE_NAME;"
	)->each(function($column){
		database::query(
			"alter table `". $column['TABLE_NAME'] ."`
			change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` CHAR(2) ". (($column['IS_NULLABLE'] == 'YES') ? "NULL" : "NOT NULL") . (!empty($column['COLUMN_DEFAULT']) ? " DEFAULT " . $column['COLUMN_DEFAULT'] : "") .";"
		);
	});

	database::query(
		"select TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT from information_schema.COLUMNS
		where TABLE_SCHEMA = '". DB_DATABASE ."'
		and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
		and COLUMN_NAME like '%currency_code%'
		and COLUMN_TYPE = 'varchar(3)'
		order by TABLE_NAME;"
	)->fetch(function($column){
		database::query(
			"alter table `". $column['TABLE_NAME'] ."`
			change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` CHAR(3) ". (($column['IS_NULLABLE'] == 'YES') ? "NULL" : "NOT NULL") . (!empty($column['COLUMN_DEFAULT']) ? " DEFAULT " . $column['COLUMN_DEFAULT'] : "") .";"
		);
	});

	// Convert Table Charset and Collations
	$collations = database::query(
		"select COLLATION_NAME FROM `information_schema`.`COLLATIONS`
		where CHARACTER_SET_NAME = 'utf8mb4'
		order by COLLATION_NAME;"
	)->fetch_all('COLLATION_NAME');

	database::query(
		"SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES
		WHERE TABLE_SCHEMA = '". DB_DATABASE ."'
		AND TABLE_NAME like '". DB_TABLE_PREFIX ."%'
		order by TABLE_NAME;"
	)->each(function($table) use ($collations) {

		$new_collation = preg_replace('#^(.*?)_.*$#', 'utf8mb4_$1', $table['TABLE_COLLATION']);

		if (!in_array($new_collation, $collations)) {
			if (in_array('utf8mb4_0900_ai_ci', $collations)) {
				$new_collation = 'utf8mb4_0900_ai_ci';
			} else {
				$new_collation = 'utf8mb4_unicode_ci';
			}
		}

		database::query(
			"alter table `". $table['TABLE_NAME'] ."`
			convert to character set utf8mb4 collate ". database::input($new_collation) .";"
		);

		database::query(
			"alter table `". $table['TABLE_NAME'] ."`
			engine=InnoDB;"
		);
	});

	if (in_array('utf8mb4_0900_ai_ci', $collations)) {
		$new_collation = 'utf8mb4_0900_ai_ci';
	} else {
		$new_collation = 'utf8mb4_unicode_ci';
	}

	database::query(
		"alter database `". DB_DATABASE ."`
		default character set utf8mb4 collate ". database::input($new_collation) .";"
	);

	// Rename date_updated to updated_at
	database::query(
		"select TABLE_NAME, COLUMN_NAME from information_schema.COLUMNS
		where TABLE_SCHEMA = '". DB_DATABASE ."'
		and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
		and COLUMN_NAME = 'date_updated';"
	)->each(function($column) {
		database::query(
			"alter table `". $column['TABLE_NAME'] ."`
			change column `date_updated` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;"
		);
	});

	// Rename date_created to created_at
	database::query(
		"select TABLE_NAME, COLUMN_NAME from information_schema.COLUMNS
		where TABLE_SCHEMA = '". DB_DATABASE ."'
		and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
		and COLUMN_NAME = 'date_created';"
	)->each(function($column) {
		database::query(
			"alter table `". $column['TABLE_NAME'] ."`
			change column `date_created` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;"
		);
	});

	// Remove some indexes if they exist
	if (database::query(
		"select * from INFORMATION_SCHEMA.STATISTICS
		where TABLE_NAME = '". DB_TABLE_PREFIX ."brands_info'
		and INDEX_NAME = 'brand'
		and INDEX_SCHEMA = '". DB_TABLE_PREFIX ."brands_info';"
	)->num_rows) {
		database::query(
			"ALTER TABLE `". DB_TABLE_PREFIX ."brands_info`
			DROP INDEX `manufacturer`;"
		);
	}

	if (database::query(
		"select * from INFORMATION_SCHEMA.STATISTICS
		where TABLE_NAME = '". DB_TABLE_PREFIX ."brands_info'
		and INDEX_NAME = 'brand_info'
		and INDEX_SCHEMA = '". DB_TABLE_PREFIX ."brands_info';"
	)->num_rows) {
		database::query(
			"ALTER TABLE `". DB_TABLE_PREFIX ."manufacturers_info`
			DROP INDEX `brand_info`;"
		);
	}

	// Migrate order items to line items
	database::query(
		"select * from ". DB_TABLE_PREFIX ."orders_items
		order by id asc;"
	)->each(function($item){

		$item['userdata'] = unserialize($item['userdata']);

		database::query(
			"insert into ". DB_TABLE_PREFIX ."orders_lines (order_id, product_id, code, name, userdata, quantity, price, tax_class_id, discount, priority)
			values (
				". (int)$item['order_id'] .",
				". (int)$item['product_id'] .",
				'". database::input($item['name']) ."',
				". json_encode($item['userdata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .",
				". (int)$item['quantity'] .",
				". (float)$item['price'] .",
				". ($item['tax_class_id'] ? (int)$item['tax_class_id'] : "NULL") .",
				". (($item['tax'] > 0) ? round($item['tax'] / $item['price'], 2) : "NULL") .",
				". (float)$item['discount'] .",
				". ($item['price'] * (float)$item['quantity']) .",
				". ($item['tax'] * (float)$item['quantity']) .",
				". (int)$item['priority'] ."
			);"
		);

		$line_id = database::insert_id();

		database::query(
			"update ". DB_TABLE_PREFIX ."orders_items
			set line_id = ". (int)$line_id ."
			where id = ". (int)$item['id'] ."
			limit 1;"
		);
	});

	// Separate product customizations from stock options
	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_stock_options;"
	)->each(function($stock_option){
		foreach (explode(',', $stock_option['attributes']) as $pair) {

			list($group_id, $value_id) = explode('-', $pair);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_customizations_values
				where product_id = ". (int)$stock_option['product_id'] ."
				and (group_id = ". (int)$group_id ." and value_id = ". (int)$value_id .");"
			);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_customizations
				where product_id = ". (int)$stock_option['product_id'] ."
				and group_id = ". (int)$group_id ."
				and product_id not in (
					select product_id from ". DB_TABLE_PREFIX ."products_customizations_values
					where product_id = ". (int)$stock_option['product_id'] ."
					and group_id = ". (int)$group_id ."
				);"
			);
		}
	});

 	// Migrate PHP serialized userdata to JSON
	database::query(
		"select * from ". DB_TABLE_PREFIX ."cart_items;"
	)->each(function($item){

		$item['userdata'] = $item['userdata'] ? unserialize($item['userdata']) : [];

		database::query(
			"update ". DB_TABLE_PREFIX ."cart_items
			set userdata = '". (!empty($item['userdata']) ? json_encode($item['userdata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '') ."'
			where id = ". (int)$item['id'] ."
			limit 1;"
		);
	});

	database::query(
		"select * from ". DB_TABLE_PREFIX ."orders_items;"
	)->each(function($item){

		$item['userdata'] = $item['userdata'] ? unserialize($item['userdata']) : [];

		database::query(
			"update ". DB_TABLE_PREFIX ."orders_items
			set userdata = '". (!empty($item['userdata']) ? json_encode($item['userdata'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '') ."'
			where id = ". (int)$item['id'] ."
			limit 1;"
		);
	});

 	// Migrate Stock Options
	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_stock_options;"
	)->each(function($stock_option){

		$attributes = preg_split('#\s*,\s*#', $stock_option['attributes'], -1, PREG_SPLIT_NO_EMPTY);

		database::query(
			"update ". DB_TABLE_PREFIX ."orders_items
			set stock_option_id = ". (int)$stock_option['id'] .",
				attributes = trim(both ',' from replace(concat(',', attributes, ','), concat(',', '". $stock_option['attributes'] ."', ','), ','))
			where product_id = ". (int)$stock_option['product_id'] ."
			and attributes regexp '(^|,)". $stock_option['attributes'] ."(,|$)';"
		);
	});

	// Set subtotal for all previous orders
	database::query(
		"update ". DB_TABLE_PREFIX ."orders o
		left join (
			select order_id, sum(quantity * price) as subtotal, sum(quantity * tax) as subtotal_tax
			from ". DB_TABLE_PREFIX ."orders_items
			group by order_id
		) oi on (oi.order_id = o.id)
		set o.subtotal = if(oi.subtotal, oi.subtotal, 0),
			o.subtotal_tax = if(oi.subtotal_tax, oi.subtotal_tax, 0);"
	);

	// Set hostname for recent orders
	database::query(
		"select ip_address from ". DB_TABLE_PREFIX ."orders
		order by created_at desc
		limit 250;"
	)->each(function($order) {
		database::query(
			"update ". DB_TABLE_PREFIX ."orders
			set hostname = '". gethostbyaddr($order['ip_address']) ."'
			where ip_address = '". $order['ip_address'] ."';"
		);
	});

	// Migrate product campaign prices to campaigns
	$campaigns = [];

	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_campaigns;"
	)->each(function($campaign_product) use (&$campaigns) {

		$valid_from = $campaign_product['start_date'] ? date('YmdHis', strtotime($campaign_product['start_date'])) : '0';
		$valid_to = $campaign_product['end_date'] ? date('YmdHis', strtotime($campaign_product['end_date'])) : '0';

		$campaigns[$valid_from.'-'.$valid_to][] = $campaign_product;
	});

	// Migrate product campaign prices to campaigns
	$campaigns = [];

	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_campaigns;"
	)->each(function($campaign_product) use (&$campaigns) {

		$valid_from = $campaign_product['start_date'] ? date('YmdHis', strtotime($campaign_product['start_date'])) : '0';
		$valid_to = $campaign_product['end_date'] ? date('YmdHis', strtotime($campaign_product['end_date'])) : '0';

		$campaigns[$valid_from.'-'.$valid_to][] = $campaign_product;
	});

	foreach ($campaigns as $campaign_products) {

		database::query(
			"insert into ". DB_TABLE_PREFIX ."campaigns
			(name, valid_from, valid_to)
			values ('', ". (!empty($campaign[0]['valid_from']) ? "'". $campaign[0]['valid_from'] ."'" : "null") .", ". (!empty($campaign[0]['valid_to']) ? "'". $campaign[0]['valid_to'] ."'" : "null") .");"
		);

		$campaign_id = database::insert_id();

		foreach ($campaign_products as $campaign_product) {

			$prices = array_filter($campaign_product, function ($key) {
				return (preg_match('#^[A-Z]{3}$#', $key));
			}, ARRAY_FILTER_USE_KEY);

			database::query(
				"insert into ". DB_TABLE_PREFIX ."campaigns_products
				(campaign_id, product_id, price)
				values (". (int)$campaign_id .", ". (int)$campaign_product['product_id'] .", '". database::input(json_encode($prices)) ."');"
			);
		}
	}

	database::query(
		"drop table ". DB_TABLE_PREFIX ."products_campaigns;"
	);

	// Migrate product prices

	$currencies = database::query(
		"select * from ". DB_TABLE_PREFIX ."currencies;"
	)->fetch_all('code');

	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_prices;"
	)->each(function($product_price) use ($currencies) {

		$prices = array_filter($product_price, function ($key) {
			return (preg_match('#^[A-Z]{3}$#', $key));
		}, ARRAY_FILTER_USE_KEY);

		database::query(
			"update ". DB_TABLE_PREFIX ."products_prices
			set price = '". database::input(json_encode(array_filter($prices))) ."'
			where id = ". (int)$product_price['id'] ."
			limit 1;"
		);
	});

	// Drop currency columns from table
	foreach ($currencies as $currency_code) {
		database::query(
			"alter table ". DB_TABLE_PREFIX ."products_prices
			drop column `". database::input($currency_code) ."`;"
		);
	}

	// Migrate product custization value prices
	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_prices;"
	)->each(function($product_price) use ($currencies) {

		$price_adjustment = array_filter($product_price, function ($key) {
			return (preg_match('#^[A-Z]{3}$#', $key));
		}, ARRAY_FILTER_USE_KEY);

		database::query(
			"update ". DB_TABLE_PREFIX ."products_customizations_values
			set price_adjustment = '". database::input(json_encode(array_filter($price_adjustment))) ."'
			where id = ". (int)$product_price['id'] ."
			limit 1;"
		);
	});

	// Drop currency columns from table
	foreach ($currencies as $currency_code) {
		database::query(
			"alter table ". DB_TABLE_PREFIX ."products_customizations_values
			drop column `". database::input($currency_code) ."`;"
		);
	}

	// Migrate products_customizations price adjustments that are bound to a stock option
	database::query(
		"select * from ". DB_TABLE_PREFIX ."products_stock_options
		where attributes != '';"
	)->each(function($stock_option) {

		$attributes = preg_split('#\s*,\s*#', $stock_option['attributes'], -1, PREG_SPLIT_NO_EMPTY);
		$attributes = array_map(function($attribute) {
			return explode('-', $attribute);
		}, $attributes);

		foreach ($attributes as $attribute) {
			list($group_id, $value_id) = $attribute;

			database::query(
				"select * from ". DB_TABLE_PREFIX ."products_customizations_values
				where product_id = ". (int)$stock_option['product_id'] ."
				and group_id = ". (int)$group_id ."
				and value_id = ". (int)$value_id ."
				limit 1;"
			)->fetch(function($customization_value) {
				database::query(
					"update ". DB_TABLE_PREFIX ."products_products_stock_options
					set price_modifier = '". database::input($customization_value['price_modifier']) ."'
						price_adjustment = '". database::input($customization_value['price_adjustment']) ."'
					where id = ". (int)$stock_option['id'] ."
					limit 1;"
				);
			});

			// Remove customization value now as we migrated it to the stock option
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_customizations_values
				where product_id = ". (int)$stock_option['product_id'] ."
				and group_id = ". (int)$group_id ."
				and value_id = ". (int)$value_id .";"
			);

			// Delete orpan customization groups
			database::query(
				"delete from ". DB_TABLE_PREFIX ."products_customizations
				where product_id = ". (int)$stock_option['product_id'] ."
				and group_id not in (
					select id from ". DB_TABLE_PREFIX ."products_customizations_values
					where product_id = ". (int)$stock_option['product_id'] ."
					and group_id = ". (int)$group_id ."
				);"
			);
		}
	});

	// Migrate products (having a quantity but no stock option) to stock options
	database::query(
		"select id, sku, weight, weight_unit, length, width, height, length_unit, quantity
		from ". DB_TABLE_PREFIX ."products
		where id not in (
			select product_id from ". DB_TABLE_PREFIX ."products_stock_options
		)
		and quantity != 0;"
	)->each(function($product) {

		database::query(
			"insert into ". DB_TABLE_PREFIX ."products_stock_options
			(product_id, sku, weight, weight_unit, length, width, height, length_unit, quantity)
			values (
				". (int)$product['id'] .",
				'". database::input($product['sku']) ."',
				". (float)$product['weight'] .",
				". (int)$product['weight_unit'] .",
				". (float)$product['length'] .",
				". (float)$product['width'] .",
				". (float)$product['height'] .",
				". (int)$product['length_unit'] .",
				". (int)$product['quantity'] ."
			);"
		);

		$stock_option_id = database::insert_id();

		database::query(
			"update ". DB_TABLE_PREFIX ."orders_items
			set stock_option_id = ". (int)$stock_option_id."
			where product_id = ". (int)$product['id'] ."
			and stock_option_id is null;"
		);
	});

	// Migrate stock options to stock items
	database::query(
		"select pso.id, pso.product_id, p.brand_id, p.supplier_id, p.name, pso.sku, p.gtin, p.mpn, p.taric, pso.weight, pso.weight_unit, pso.length, pso.width, pso.height, pso.length_unit, pso.quantity, p.quantity_unit_id, p.purchase_price, p.purchase_price_currency_code, pso.updated_at, pso.created_at
		from ". DB_TABLE_PREFIX ."products_stock_options pso
		left join ". DB_TABLE_PREFIX ."products p on (p.id = pso.product_id);"
	)->each(function($stock_option) {

		database::query(
			"insert into ". DB_TABLE_PREFIX ."stock_items
			(brand_id, supplier_id, name, sku, gtin, mpn, taric, weight, weight_unit, length, width, height, length_unit, quantity, quantity_unit_id, purchase_price, purchase_price_currency_code, updated_at, created_at)
			values (
				". ($stock_option['brand_id'] ? (int)$stock_option['brand_id'] : "null") .",
				". ($stock_option['supplier_id'] ? (int)$stock_option['supplier_id'] : "null") .",
				'". database::input($stock_option['name']) ."',
				'". database::input($stock_option['sku']) ."',
				'". database::input($stock_option['gtin']) ."',
				'". database::input($stock_option['mpn']) ."',
				'". database::input($stock_option['taric']) ."',
				". (float)$stock_option['weight'] .",
				". (int)$stock_option['weight_unit'] .",
				". (float)$stock_option['length'] .",
				". (float)$stock_option['width'] .",
				". (float)$stock_option['height'] .",
				". (int)$stock_option['length_unit'] .",
				". (int)$stock_option['quantity'] .",
				". (int)$stock_option['quantity_unit_id'] .",
				". (float)$stock_option['purchase_price'] .",
				'". database::input($stock_option['purchase_price_currency_code']) ."',
				'". database::input($stock_option['updated_at']) ."',
				'". database::input($stock_option['created_at']) ."'
			);"
		);

		$stock_item_id = database::insert_id();

		database::query(
			"update ". DB_TABLE_PREFIX ."products_stock_options
			set stock_item_id = ". (int)$stock_item_id ."
			where id = ". (int)$stock_option['id'] ."
			limit 1;"
		);

		database::query(
			"update ". DB_TABLE_PREFIX ."orders_items
			set stock_items = '". json_encode(['id' => $stock_item_id, 'quantity' => 1], true) ."'
			where product_id = ". (int)$stock_option['product_id'] ."
			and stock_option_id = ". (int)$stock_option['id'] .";"
		);
	});

	// Create initial stock transaction
	database::query(
		"insert into `". DB_TABLE_PREFIX ."stock_transactions` (id, name, description)
		values (1, 'Initial Stock Transaction', 'This is an initial system generated stock transaction to deposit stock for all sold items and items in stock. We need this for future inconcistency checks.');"
	);

	// Insert initial stock into stock transactions contents
	database::query(
		"insert into `". DB_TABLE_PREFIX ."stock_transactions_contents` (transaction_id, stock_item_id, quantity_adjustment)
		select '1', stock_item_id, sum(quantity)
		from `". DB_TABLE_PREFIX ."products_stock_options`
		group by product_id, stock_item_id;"
	);

	// Append initial stock with sold quantities
	database::query(
		"select product_id, stock_items, quantity
		from `". DB_TABLE_PREFIX ."orders_items` oi
		where oi.order_id in (
			select id from `". DB_TABLE_PREFIX ."orders`
			where order_status_id in (
				select id from `". DB_TABLE_PREFIX ."order_statuses`
				where stock_action = 'withdraw'
			)
		)
		group by product_id;"
	)->each(function($order_item) {
		$order_item['stock_items'] = json_decode($order_item['stock_items'], true);
		foreach ($order_item['stock_items'] as $stock_item) {
			database::query(
				"insert into `". DB_TABLE_PREFIX ."stock_transactions_contents` (transaction_id, stock_item_id, quantity_adjustment)
				values (1, ". (int)$stock_item_id .", ". (int)$order_item['quantity'] .")
				on duplicate key update quantity_adjustment = quantity_adjustment + ". ((float)$order_item['quantity'] * $stock_item['quantity']) .";"
			);
		}
	});

	// Make all 11 digit unsigned integers standard int(10) unsigned
	database::query(
		"select * from `information_schema`.`COLUMNS`
		where TABLE_SCHEMA = '". database::input(DB_DATABASE) ."'
		and COLUMN_TYPE = 'int(11) unsigned';"
	)->each(function($column) {
		database::query(
			"alter table `". database::input($column['TABLE_NAME']) ."`
			change column `". database::input($column['COLUMN_NAME']) ."` `". database::input($column['COLUMN_NAME']) ."` int(10) unsigned ". ($column['IS_NULLABLE'] == 'YES' ? "null" : "not null") . " ". ($column['COLUMN_DEFAULT'] ? "default ". $column['COLUMN_DEFAULT'] : "") .";"
		);
	});

	// Make all 11 digit unsigned floating points standard float(10,4) unsigned
	database::query(
		"select * from `information_schema`.`COLUMNS`
		where TABLE_SCHEMA = '". database::input(DB_DATABASE) ."'
		and COLUMN_TYPE = 'float(11,4) unsigned';"
	)->each(function($column) {
		database::query(
			"alter table `". database::input($column['TABLE_NAME']) ."`
			change column `". database::input($column['COLUMN_NAME']) ."` `". database::input($column['COLUMN_NAME']) ."` float(10,4) unsigned ". ($column['IS_NULLABLE'] == 'YES' ? "null" : "not null") . " ". ($column['COLUMN_DEFAULT'] ? "default ". $column['COLUMN_DEFAULT'] : "") .";"
		);
	});

	// Move info tables data to main table as JSON
	$collections = [
		[
			'singular' => 'attribute_group',
			'plural' => 'attribute_groups',
		],
		[
			'singular' => 'attribute_value',
			'plural' => 'attribute_values',
		],
		[
			'singular' => 'brand',
			'plural' => 'brands',
		],
		[
			'singular' => 'category',
			'plural' => 'categories',
		],
		[
			'singular' => 'delivery_status',
			'plural' => 'delivery_statuses',
		],
		[
			'singular' => 'order_status',
			'plural' => 'order_statuses',
		],
		[
			'singular' => 'page',
			'plural' => 'pages',
		],
		[
			'singular' => 'product',
			'plural' => 'products',
		],
		[
			'singular' => 'quantity_unit',
			'plural' => 'quantity_units',
		],
		[
			'singular' => 'sold_out_status',
			'plural' => 'sold_out_statuses',
		],
	];

	$aliases = [
		'attribute_group_id' => 'group_id',
		'attribute_value_id' => 'value_id',
	];

	// Step through collections
	foreach ($collections as $collection) {

		// Get all columns in main table
		$columns = database::query(
			"show fields from `". DB_TABLE_PREFIX . $collection['plural'] ."`;"
		)->fetch_all('Field');

		// Get all columns in info table
		$info_columns = database::query(
			"show fields from ". DB_TABLE_PREFIX . $collection['plural'] ."_info;"
		)->fetch_all(function($field) use ($collection) {
			if (in_array($field['Field'], ['id', 'language_code']) || preg_match('#_id$#', $field['Field'])) return false;
			return $field['Field'];
		});

		// Create missing columns in main table
		foreach (array_diff($info_columns, $columns) as $column) {
			database::query(
				"alter table `". DB_TABLE_PREFIX . $collection['plural'] ."`
				add column `". database::input($column) ."` text not null default '{}';"
			);
		}

		// Copy data from info table to main table
		database::query(
			"SELECT `". database::input(strtr($collection['singular'].'_id', $aliases)) ."` as entity_id,
				". implode(',' . PHP_EOL, array_map(function($field){
					return "CONCAT('{', GROUP_CONCAT(CONCAT('\"', language_code, '\":', JSON_QUOTE(`". database::input($field) ."`)) ORDER BY language_code), '}') AS ". $field;
				}, $info_columns)) ."
			from `". DB_TABLE_PREFIX . database::input($collection['plural']) ."_info`
			group by `". database::input(strtr($collection['singular'].'_id', $aliases)) ."`;"
		)->each(function($info) use ($collection, $info_columns) {
			database::query(
				"update `". DB_TABLE_PREFIX . database::input($collection['plural']) ."`
				set ". implode(',' . PHP_EOL, array_map(function($field) use ($info) {
					return "`". database::input($field) ."` = '". database::input($info[$field]) ."'";
				}, $info_columns)) ."
				where id = ". (int)$info['entity_id'] ."
				limit 1;"
			);
		});

		// Drop info table
		database::query(
			"drop table `". DB_TABLE_PREFIX . $collection['plural'] ."_info`;"
		);
	}

	// Remove deprecated columns
	database::query(
		"alter table `". DB_TABLE_PREFIX ."orders_items`
		drop column `attributes`,
		drop column `tax`;"
	);

	// Drop deprecated columns from products_stock_options
	database::query(
		"alter table ". DB_TABLE_PREFIX ."products_stock_options
		drop column `attributes`,
		drop column `sku`,
		drop column `weight`,
		drop column `weight_unit`,
		drop column `length`,
		drop column `width`,
		drop column `height`,
		drop column `length_unit`,
		drop column `quantity`;"
	);
