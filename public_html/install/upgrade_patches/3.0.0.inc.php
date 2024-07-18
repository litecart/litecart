<?php

  perform_action('copy', [
    FS_DIR_APP . 'install/data/default/storage/files' => FS_DIR_STORAGE,
  ]);

  perform_action('delete', [
    FS_DIR_ADMIN . 'addons.widget/addons.inc.php',
    FS_DIR_ADMIN . 'addons.widget/config.inc.php',
    FS_DIR_ADMIN . 'addons.widget/index.html',
    FS_DIR_ADMIN . 'appearance.app/config.inc.php',
    FS_DIR_ADMIN . 'appearance.app/logotype.inc.php',
    FS_DIR_ADMIN . 'appearance.app/template.inc.php',
    FS_DIR_ADMIN . 'appearance.app/template_settings.inc.php',
    FS_DIR_ADMIN . 'catalog.app/attribute_groups.inc.php',
    FS_DIR_ADMIN . 'catalog.app/attribute_values.json.inc.php',
    FS_DIR_ADMIN . 'catalog.app/catalog.inc.php',
    FS_DIR_ADMIN . 'catalog.app/config.inc.php',
    FS_DIR_ADMIN . 'catalog.app/csv.inc.php',
    FS_DIR_ADMIN . 'catalog.app/delivery_statuses.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_attribute_group.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_category.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_delivery_status.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_brand.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_product.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_quantity_unit.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_sold_out_status.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_supplier.inc.php',
    FS_DIR_ADMIN . 'catalog.app/index.html',
    FS_DIR_ADMIN . 'catalog.app/brands.inc.php',
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
    FS_DIR_ADMIN . 'translations.app/config.inc.php',
    FS_DIR_ADMIN . 'translations.app/csv.inc.php',
    FS_DIR_ADMIN . 'translations.app/index.html',
    FS_DIR_ADMIN . 'translations.app/scan.inc.php',
    FS_DIR_ADMIN . 'translations.app/search.inc.php',
    FS_DIR_ADMIN . 'users.app/config.inc.php',
    FS_DIR_ADMIN . 'users.app/edit_user.inc.php',
    FS_DIR_ADMIN . 'users.app/index.html',
    FS_DIR_ADMIN . 'users.app/users.inc.php',
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
    FS_DIR_APP . 'includes/abstracts/abs_module.inc.php',
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
    FS_DIR_APP . 'includes/modules/order_total/ot_subtotal.inc.php',
    FS_DIR_APP . 'includes/templates/default.admin/',
    FS_DIR_APP . 'includes/templates/default.catalog/config.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/css',
    FS_DIR_APP . 'includes/templates/default.catalog/images',
    FS_DIR_APP . 'includes/templates/default.catalog/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/js',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts',
    FS_DIR_APP . 'includes/templates/default.catalog/less',
    FS_DIR_APP . 'includes/templates/default.catalog/pages',
    FS_DIR_APP . 'includes/templates/default.catalog/views',
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
    FS_DIR_APP . 'includes/templates/default.catalog/images/cart.svg',
    FS_DIR_APP . 'includes/templates/default.catalog/images/cart_filled.svg',
    FS_DIR_APP . 'includes/templates/default.catalog/images/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/images/loader.svg',
    FS_DIR_APP . 'includes/templates/default.catalog/js/app.js',
    FS_DIR_APP . 'includes/templates/default.catalog/js/app.min.js',
    FS_DIR_APP . 'includes/templates/default.catalog/js/app.min.js.map',
    FS_DIR_APP . 'includes/templates/default.catalog/js/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts/ajax.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts/blank.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts/checkout.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts/default.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/layouts/printable.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/checkout.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/less/printable.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/variables.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app/boxes.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app/listing.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app/product.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/app/theme.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/animations.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/base.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/breadcrumbs.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/buttons.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/carousel.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/chat.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/dropdown.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/effects.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/grid.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/images.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/inputs.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/lists.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/loader.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/nav.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/navbar.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/normalize.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/notices.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/pagination.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/tables.less',
    FS_DIR_APP . 'includes/templates/default.catalog/less/framework/typography.less',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/categories.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/category.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/checkout.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/create_account.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/customer_service.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/edit_account.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/index.html',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/index.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/information.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/login.ajax.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/login.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/maintenance_mode.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/brand.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/brands.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/order.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/order_history.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/order_success.inc.php',
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
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_brand_links.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_brand_logotypes.inc.php',
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
    FS_DIR_APP . 'logs/.htaccess',
    FS_DIR_APP . 'logs/index.html',
    FS_DIR_APP . 'pages/ajax/cart.json.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_cart.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_customer.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_payment.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_shipping.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_summary.inc.php',
    FS_DIR_APP . 'pages/ajax/get_address.json.inc.php',
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
    FS_DIR_APP . 'vqmod/.htaccess',
  ]);

  perform_action('move', [
    FS_DIR_APP . 'includes/config.inc.php' => FS_DIR_STORAGE . 'config.inc.php',
    FS_DIR_APP . 'favicon.ico' => FS_DIR_STORAGE . 'storage/images/favicons/favicon.ico',
  ]);

  foreach (glob(FS_DIR_ADMIN . '*.app') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'backend/apps/' . preg_replace('#\.app$#', '', basename($file))]);
  }

  foreach (glob(FS_DIR_ADMIN . '*.widget') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'backend/widgets/' . preg_replace('#\.widget$#', '', basename($file))]);
  }

  foreach (glob(FS_DIR_APP . 'cache/*') as $file) {
    perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'cache/', '#') .'#', FS_DIR_STORAGE . 'cache/', $file)]);
  }

  foreach (glob(FS_DIR_APP . 'data/*') as $file) {
    perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'data/', '#') .'#', FS_DIR_STORAGE, $file)]);
  }

  foreach (glob(FS_DIR_APP . 'ext/*') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'assets/' . basename($file)]);
  }

  foreach (glob(FS_DIR_APP . 'images/*') as $file) {
    perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'images/', '#') .'#', FS_DIR_STORAGE . 'images/', $file)]);
  }

  foreach (glob(FS_DIR_APP . 'logs/*') as $file) {
    perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'logs/', '#') .'#', FS_DIR_STORAGE . 'logs/', $file)]);
  }

  foreach (glob(FS_DIR_APP . 'includes/boxes/*') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'frontend/partials/' . basename($file)]);
  }

  foreach (glob(FS_DIR_APP . 'includes/library/*') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'inlcudes/nodes/' . preg_replace('#^lib_#', 'nod_', basename($file))]);
  }

  foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/views/') as $directory) {
    perform_action('move', [$directory => preg_replace(['#\.catalog#', '#/views/#'], ['', '/partials/'], $directory)]);
  }

  foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'frontend/templates/' . preg_replace('#\.catalog#', '', basename($file))]);
  }

  foreach (glob(FS_DIR_APP . 'vqmod/xml/*') as $file) {
    perform_action('move', [$file => FS_DIR_STORAGE . 'vmods/' . basename($file)]);
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
    FS_DIR_APP . 'includes/templates/',
    FS_DIR_APP . 'includes/library/',
    FS_DIR_APP . 'logs/',
    FS_DIR_APP . 'ext/',
    FS_DIR_APP . 'pages/',
    FS_DIR_APP . 'vqmod/',
  ]);

  perform_action('copy', [
    FS_DIR_APP . 'install/data/default/storage/images/favicon*' => FS_DIR_STORAGE . 'images/',
  ]);

  perform_action('modify', [
    FS_DIR_STORAGE . 'config.inc.php' => [
      [
        'search'  => '/'. preg_quote('## Backwards Compatible Directory Definitions (LiteCart <2.2) ########', '/') . PHP_EOL .'#{70}'. PHP_EOL .'.*?(#{70})/',
        'replace' => '$1',
        'regexp'  => true,
      ],
      [
        'search'  => "  define('DB_CONNECTION_CHARSET', (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? 'latin1' : 'utf8'); // utf8 or latin1" . PHP_EOL,
        'replace' => "  define('DB_CONNECTION_CHARSET', 'utf8'); // utf8 or latin1" . PHP_EOL,
      ],
      [
        'search'  => '/'. preg_quote('// Database Tables - Backwards Compatibility (LiteCart <2.3)', '/') .'.*?(#{70})/',
        'replace' => '$1',
        'regexp'  => true,
      ],
      [
        'search'  => "  error_reporting(version_compare(PHP_VERSION, '5.4.0', '<') ? E_ALL | E_STRICT : E_ALL);",
        'replace' => "  error_reporting(E_ALL);",
      ],
      [
        'search'  => "  define('DOCUMENT_ROOT',      str_replace('\\', '/', rtrim(realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..'), '/')));",
        'replace' => "  define('DOCUMENT_ROOT',      rtrim(str_replace('\\', '/', realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..')), '/'));",
      ],
      [
        'search'  => "  define('FS_DIR_APP',         str_replace('\\', '/', rtrim(realpath(__DIR__.'/..'), '/')) . '/');",
        'replace' => "  define('FS_DIR_APP',         rtrim(str_replace('\\', '/', realpath(__DIR__.'/..')), '/') . '/');",
      ],
    ],
  ]);

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => '/  (#)?' . preg_quote('RewriteCond %{HTTP_HOST} !^www\.' . PHP_EOL, '/') .'/',
        'replace' => '  $1RewriteCond %{HTTP_HOST} !^www\.' . PHP_EOL
                   . '  $1RewriteCond %{HTTP_HOST} !^static\.' . PHP_EOL,
        'regexp'  => true,
      ],
      [
        'search'  => '/  (#)?' . preg_quote('RewriteCond %{HTTP_HOST} ^www\.(.*)$' . PHP_EOL, '/') .'/',
        'replace' => '  $1RewriteCond %{HTTP_HOST} ^www\.(.*)' . PHP_EOL
                   . '  $1RewriteCond %{HTTP_HOST} !^static\.' . PHP_EOL,
        'regexp'  => true,
      ],
      [
        'search'  => '/  (#)?' . preg_quote('RewriteCond %{HTTP_HOST} !^www.mydomain.com' . PHP_EOL, '/') .'/',
        'replace' => '  $1RewriteCond %{HTTP_HOST} !^www\.mydomain\.com' . PHP_EOL
                   . '  $1RewriteCond %{HTTP_HOST} !^static\.' . PHP_EOL,
        'regexp'  => true,
      ],
      [
        'search'  => '  # Web path to catalog root' . PHP_EOL,
        'replace' => '  # Deny access to non-static content on static domain' . PHP_EOL
                   . '  RewriteCond %{HTTP_HOST} ^static\.' . PHP_EOL
                   . '  RewriteCond %{REQUEST_URI} !\.(css|eot|gif|jpe?g|js|map|otf|png|svg|ttf|woff2?)(\?.*?)?$ [NC]' . PHP_EOL
                   . '  RewriteCond %{REQUEST_URI} !/handlers/ [NC]' . PHP_EOL
                   . '  RewriteRule ^ - [R=403,L]' . PHP_EOL
                   . PHP_EOL
                   . '  # Remove bogus URL query parameters without values (MSNBot)'. PHP_EOL
                   . '  RewriteCond %{QUERY_STRING} ^[0-9a-z]{6,8}=$' . PHP_EOL
                   . '  RewriteRule ^(.*)$ $1 [R=301,L]' . PHP_EOL
                   . PHP_EOL
                   . ' # Favicons' . PHP_EOL
                   . ' RewriteCond %{REQUEST_URI} /favicon\.ico$' . PHP_EOL
                   . ' RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL
                   . ' RewriteRule ^ {BASE_DIR}storage/images/favicons/favicon.ico [L]' . PHP_EOL
                   . PHP_EOL
                   . ' RewriteCond %{REQUEST_URI} /(android-chrome|android-icon|apple-icon|apple-touch-icon|favicon)(-\d{2,3}x\d{2,3})?(-precomposed)?\.png$' . PHP_EOL
                   . ' RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL
                   . ' RewriteRule ^ {BASE_DIR}storage/images/favicons/favicon-256x256.png [L]' . PHP_EOL
                   . PHP_EOL
                   . '  # Web path to catalog root' . PHP_EOL,
      ],
      [
        'search'  => "  RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]" . PHP_EOL,
        'replace' => "  # Resolve some storage content" . PHP_EOL
                   . "  RewriteRule ^(cache|images)/ storage/%{REQUEST_URI} [L]" . PHP_EOL
                   . PHP_EOL
                   . "  RewriteRule ^ index.php [QSA,L]" . PHP_EOL,
      ],
      [
        'search'  => "#". preg_quote('<FilesMatch "\.(css|js)$">', '#') .".*?". preg_quote('<FilesMatch "\.(a?png|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">', '#') ."#",
        'replace' => '<FilesMatch "\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'regexp'  => true,
      ],
      [
        'search'  => '#AuthUserFile ".*?.htpasswd"#',
        'replace' => 'AuthUserFile "'. FS_DIR_APP .'.htpasswd"',
        'regexp'  => true,
      ],
    ],
    FS_DIR_STORAGE . 'config.inc.php' => [
      [
        'search'  => "  define('DB_CONNECTION_CHARSET', 'utf8'); // utf8 or latin1" . PHP_EOL,
        'replace' => "  define('DB_CONNECTION_CHARSET', 'utf8mb4');",
      ],
      [
        'search'  => "  define('DB_PERSISTENT_CONNECTIONS', 'false');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'brands_info`');" . PHP_EOL,
        'replace' => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'brands_info`');" . PHP_EOL
                   . "  define('DB_TABLE_NEWSLETTER_RECIPIENTS',             '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'newsletter_recipients`');" . PHP_EOL,
      ],
      [
        'search'  => '/'. preg_quote('## Backwards Compatible Directory Definitions (LiteCart <2.2)', '#') .'.*?('. preg_quote('## Database ##########################################################', '/') .')/',
        'replace' => '$1',
        'regexp'  => true,
      ],
      [
        'search'  => '#'. preg_quote(PHP_EOL, '#') .'// Password Encryption Salt.*?\);'. preg_quote(PHP_EOL, '#') .'#m',
        'replace' => '',
        'regexp'  => true,
      ],
    ],
  ], 'abort');

// Change indentation from spaces to tabs in files
  $files = [
    FS_DIR_APP . '**/.htaccess',
    FS_DIR_STORAGE . 'config.inc.php',
  ];

  foreach ($files as $file_pattern) {

    perform_action('custom', [
      $file_pattern => function($file){

        $contents = file_get_contents($file);

        while (true) {
          $contents = preg_replace('#^(\t*)  #m', "$1\t", $contents, -1, $replacements);
          if (!$replacements) break;
        }

        $contents = preg_replace('#^(\t*)//#m', "$1\t//", $contents);

        return (bool)file_put_contents($file, $contents);
      },
    ], 'skip');
  }

// Remove some indexes if they exist
  if (database::query(
    "SELECT * FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_NAME = '". DB_TABLE_PREFIX ."brands_info'
    AND INDEX_NAME = 'brand'
    AND INDEX_SCHEMA = '". DB_TABLE_PREFIX ."brands_info';"
  )->num_rows) {
    database::query(
      "ALTER TABLE `". DB_TABLE_PREFIX ."brands_info`
      DROP INDEX `manufacturer`;"
    );
  }

  if (database::query(
    "SELECT * FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_NAME = '". DB_TABLE_PREFIX ."brands_info'
    AND INDEX_NAME = 'brand_info'
    AND INDEX_SCHEMA = '". DB_TABLE_PREFIX ."brands_info';"
  )->num_rows) {
    database::query(
      "ALTER TABLE `". DB_TABLE_PREFIX ."manufacturers_info`
      DROP INDEX `brand_info`;"
    );
  }

 // Migrate PHP serialized configurations to JSON
  $order_items_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."orders_items;"
  );

  while ($item = database::fetch($order_items_query)) {
    $item['configuration'] = unserialize($item['configuration']);

    database::query(
      "update ". DB_TABLE_PREFIX ."orders_items
      set configuration = '". (!empty($item['configuration']) ? json_encode($item['configuration'], JSON_UNESCAPED_SLASHES) : '') ."'
      where id = ". (int)$item['id'] ."
      limit 1;"
    );
  }

// Download Product Configurations Add-On
  if (database::query(
    "select id from ". DB_TABLE_PREFIX ."products_customizations;"
  )->num_rows) {

    // ...

// Remove Product Configurations
  } else {
    database::query(
      "drop table ". DB_TABLE_PREFIX ."products_customizations;"
    );
    database::query(
      "drop table ". DB_TABLE_PREFIX ."products_customizations_values;"
    );
  }

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

// Convert Table Charset and Collations
  $collations = [];

  $collations = database::query(
    "select COLLATION_NAME FROM `information_schema`.`COLLATIONS`
    where CHARACTER_SET_NAME = 'utf8mb4'
    order by COLLATION_NAME;"
  )->fetch_all('COLLATION_NAME');

  $tables_query = database::query(
    "SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '". DB_DATABASE ."'
    AND TABLE_NAME like '". DB_TABLE_PREFIX ."%'
    order by TABLE_NAME;"
  );

  while ($table = database::fetch($tables_query)) {

    $new_collation = preg_replace('#^(.*?)_.*$#', 'utf8mb4_$1', $table['TABLE_COLLATION']);

    if (!in_array($new_collation, $collations)) {
      if (in_array('utf8mb4_0900_ai_ci', $collations)) {
        $new_collation = 'utf8mb4_0900_ai_ci';
      } else {
        $new_collation = 'utf8mb4_swedish_ci';
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
  }

  database::query(
    "alter database `". DB_DATABASE ."`
    default character set utf8mb4 collate ". database::input($new_collation) .";"
  );

// Change VARCHAR length 256 to 255 (InnoDB limitation)

  $columns_query = database::query(
    "select TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT from information_schema.COLUMNS
    where TABLE_SCHEMA = '". DB_DATABASE ."'
    and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
    and COLUMN_TYPE like 'varchar(256)'
    order by TABLE_NAME;"
  );

  while ($column = database::fetch($columns_query)) {
    database::query(
      "alter table `". $column['TABLE_NAME'] ."`
      change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". strtr($column['COLUMN_TYPE'], ['256' => '255']) ." ". (($column['IS_NULLABLE'] == 'YES') ? "NOT NULL" : "NULL") . (!empty($column['COLUMN_DEFAULT']) ? " DEFAULT " . $column['COLUMN_DEFAULT'] : "") .";"
    );
  }

// Change VARCHAR to CHAR

  $columns_query = database::query(
    "select TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT from information_schema.COLUMNS
    where TABLE_SCHEMA = '". DB_DATABASE ."'
    and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
    and (COLUMN_NAME like '%country_code%' or COLUMN_NAME like '%language_code%')
    and COLUMN_TYPE = 'varchar(2)'
    order by TABLE_NAME;"
  );

  while ($column = database::fetch($columns_query)) {
    database::query(
      "alter table `". $column['TABLE_NAME'] ."`
      change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` CHAR(2) ". (($column['IS_NULLABLE'] == 'YES') ? "NOT NULL" : "NULL") . (!empty($column['COLUMN_DEFAULT']) ? " DEFAULT " . $column['COLUMN_DEFAULT'] : "") .";"
    );
  }

  $columns_query = database::query(
    "select TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT from information_schema.COLUMNS
    where TABLE_SCHEMA = '". DB_DATABASE ."'
    and TABLE_NAME like '". DB_TABLE_PREFIX ."%'
    and COLUMN_NAME like '%currency_code%')
    and COLUMN_TYPE = 'varchar(3)'
    order by TABLE_NAME;"
  );

  while ($column = database::fetch($columns_query)) {
    database::query(
      "alter table `". $column['TABLE_NAME'] ."`
      change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` CHAR(3) ". (($column['IS_NULLABLE'] == 'YES') ? "NOT NULL" : "NULL") . (!empty($column['COLUMN_DEFAULT']) ? " DEFAULT " . $column['COLUMN_DEFAULT'] : "") .";"
    );
  }

// Update config

  $timezone = database::query(
    "select `value` from ". DB_TABLE_PREFIX ."settings
    where key = 'store_timezone'
    limit 1;"
  )->fetch('value');

  perform_action('modify', [
    FS_DIR_STORAGE . 'config.inc.php' => [
      [
        'search'  => "#  define\('DB_TYPE', [^\)]+);(\r\n|\n)#",
        'replace' => "",
        'regexp'  => true,
      ],
      [
        'search'  => "#  define\('DB_CONNECTION_CHARSET', [^\)]+);(\r\n|\n)#",
        'replace' => "",
        'regexp'  => true,
      ],
      [
        'search'  => '#(\r\n?|\n)// Errors#',
        'replace' => implode(PHP_EOL, [
          "",
          "// Character Set Encoding",
          "  mb_internal_encoding('UTF-8');",
          "  mb_regex_encoding('UTF-8');",
          "",
          "// Errors",
        ]),
        'regexp'  => true,
      ],
      [
        'search'  => '#$#',
        'replace' => implode(PHP_EOL, [
          "",
          "// Float Precision",
          "  ini_set('serialize_precision', 6);",
          "",
          "// Sessions",
          "  ini_set('session.name', 'LCSESSID');",
          "  ini_set('session.use_cookies', 1);",
          "  ini_set('session.use_only_cookies', 1);",
          "  ini_set('session.use_strict_mode', 1);",
          "  ini_set('session.use_trans_sid', 0);",
          "  ini_set('session.cookie_httponly', 1);",
          "  ini_set('session.cookie_lifetime', 0);",
          "  ini_set('session.cookie_path', WS_DIR_APP);",
          "  ini_set('session.cookie_samesite', 'Lax');'",
          "  ini_set('session.gc_maxlifetime', 1440);'",
          "",
          "// Timezone",
          "  ini_set('date.timezone', '". $timezone ."');",
          "",
          "// Output Compression",
          "  ini_set('zlib.output_compression', 1);",
          "",
        ]),
        'regexp'  => true,
      ],
    ],
  ]);

// Set hostname for recent orders
  database::query(
    "select ip_address from ". DB_TABLE_PREFIX ."orders
    order by date_created desc
    limit 250;"
  )->each(function($order) {
    database::query(
      "update ". DB_TABLE_PREFIX ."orders
      set hostname = '". gethostbyaddr($order['ip_address']) ."'
      where ip_address = '". $order['ip_address'] ."';"
    );
  });
