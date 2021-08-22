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
    FS_DIR_ADMIN . 'vqmods.app/config.inc.php',
    FS_DIR_ADMIN . 'vqmods.app/download.inc.php',
    FS_DIR_ADMIN . 'vqmods.app/index.html',
    FS_DIR_ADMIN . 'vqmods.app/test.inc.php',
    FS_DIR_ADMIN . 'vqmods.app/view.inc.php',
    FS_DIR_ADMIN . 'vqmods.app/vqmods.inc.php',
    FS_DIR_ADMIN . '.htaccess',
    FS_DIR_ADMIN . '.htpasswd',
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
    FS_DIR_APP . 'data/captcha.ttf',
    FS_DIR_APP . 'data/index.html',
    FS_DIR_APP . 'images/index.html',
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
    FS_DIR_APP . 'includes/boxes/box_site_footer.inc.php',
    FS_DIR_APP . 'includes/boxes/box_site_menu.inc.php',
    FS_DIR_APP . 'includes/boxes/box_slides.inc.php',
    FS_DIR_APP . 'includes/boxes/index.html',
    FS_DIR_APP . 'includes/functions/func_password.inc.php',
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
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_site_footer.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_site_menu.inc.php',
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
    perform_action('move', [$file => preg_replace('#^'. preg_quote(FS_DIR_APP . 'data/', '#') .'#', FS_DIR_STORAGE . 'data/', $file)]);
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

  ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');

  foreach (glob(FS_DIR_APP . 'includes/boxes/*') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'frontend/boxes/' . basename($file)]);
  }

  foreach (glob(FS_DIR_APP . 'includes/library/*') as $file) {
    perform_action('move', [$file => FS_DIR_APP . 'inlcudes/nodes/' . preg_replace('#^lib_#', 'nod_', basename($file))]);
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
        'search'  => "  ini_set('error_log', FS_DIR_APP . 'logs/errors.log');",
        'replace' => "  ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');",
      ],
    ],
  ]);

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => '/  (#)?' . preg_quote('RewriteCond %{HTTP_HOST} !^www\.' . PHP_EOL, '/') .'/',
        'replace' => '  $1RewriteCond %{HTTP_HOST} !^www\.' . PHP_EOL
                  .  '  $1RewriteCond %{HTTP_HOST} !^static\.' . PHP_EOL,
        'regexp'  => true,
      ],
      [
        'search'  => '/  (#)?' . preg_quote('RewriteCond %{HTTP_HOST} ^www\.(.*)$' . PHP_EOL, '/') .'/',
        'replace' => '  $1RewriteCond %{HTTP_HOST} ^www\.(.*)' . PHP_EOL
                  .  '  $1RewriteCond %{HTTP_HOST} !^static\.' . PHP_EOL,
        'regexp'  => true,
      ],
      [
        'search'  => '/  (#)?' . preg_quote('RewriteCond %{HTTP_HOST} !^www.mydomain.com' . PHP_EOL, '/') .'/',
        'replace' => '  $1RewriteCond %{HTTP_HOST} !^www\.mydomain\.com' . PHP_EOL
                  .  '  $1RewriteCond %{HTTP_HOST} !^static\.' . PHP_EOL,
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
                   . '  # Web path to catalog root' . PHP_EOL,
      ],
      [
        'search'  => "  RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]" . PHP_EOL,
        'replace' => "  # Resolve some storage content" . PHP_EOL
                   . "  RewriteRule ^(cache|images)/ /storage/%{REQUEST_URI} [L]" . PHP_EOL
                   . PHP_EOL
                   . "  RewriteRule ^ index.php [QSA,L]" . PHP_EOL,
      ],
      [
        'search'  => "#". preg_quote('<FilesMatch "\.(css|js)$">', '#') .".*?". preg_quote('<FilesMatch "\.(a?png|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">', '#') ."#",
        'replace' => '<FilesMatch "\.(a?png|bmp|css|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
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
        'search'  => "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
        'replace' => "  define('FS_DIR_ADMIN',       FS_DIR_APP . 'backend/');" . PHP_EOL
                   . "  define('FS_DIR_STORAGE',     FS_DIR_APP . 'storage/');",
      ],
      [
        'search'  => "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
        'replace' => "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                   . "  define('WS_DIR_STORAGE',     WS_DIR_APP . 'storage/');" . PHP_EOL,
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

// Remove some indexes if they exist
  if (database::num_rows(database::query("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '". DB_TABLE_PREFIX ."brands_info' AND INDEX_NAME = 'brand' AND INDEX_SCHEMA = '". DB_TABLE_PREFIX ."brands_info';"))) {
    database::query("ALTER TABLE `". DB_TABLE_PREFIX ."brands_info` DROP INDEX `brand`;");
  }

  if (database::num_rows(database::query("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '". DB_TABLE_PREFIX ."brands_info' AND INDEX_NAME = 'brand_info' AND INDEX_SCHEMA = '". DB_TABLE_PREFIX ."brands_info';"))) {
    database::query("ALTER TABLE `". DB_TABLE_PREFIX ."brands_info` DROP INDEX `brand_info`;");
  }

// Migrate products without options to stock items
  $products_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."products
    where id not in (
      select distinct product_id from ". DB_TABLE_PREFIX ."lc_products_stock_options
    )"
  );

  while ($product = database::fetch($stock_items_query)) {

    database::query(
      "insert into ". DB_TABLE_PREFIX ."stock_items
      (brand_id, supplier_id, sku, gtin, mpn, taric, weight, weight_unit, length, width, height, length_unit, quantity, quantity_unit)
      values (". $product['brand_id'] .", ". $product['supplier_id'] .", '". database::input($product['sku']) ."', '". database::input($product['gtin']) ."', '". database::input($product['mpn']) ."', '". database::input($product['taric']) ."', ". (float)$product['purchase_price']) .", '". database::input($product['purchase_price_currency_code']) ."', ". (float)$product['weight']) .", '". database::input($product['weight_unit']) ."', ". (float)$product['length']) .", ". (float)$product['width']) .", ". (float)$product['height']) .", '". database::input($product['length_unit']) ."', ". (float)$product['quantity']) .", '". database::input($product['quantity_unit']) ."');"
    );

    $product['stock_item_id'] = database::insert_id();

    database::query(
      "insert into ". DB_TABLE_PREFIX ."stock_items_info
      (stock_item_id, name, language_code)
      select '". $product['stock_item_id'] ."' as stock_item_id, name, language_code
      from ". DB_TABLE_PREFIX ."products_info
      where product_id = ". (int)$product['id'] .";"
    );

    database::query(
      "insert into ". DB_TABLE_PREFIX ."products_to_stock_items
      (product_id, stock_item_id, price_adjust, price_operator)
      values ('". database::input($product['id']) ."', '". database::input($product['stock_item_id']) ."', '". database::input($product['price_adjust']) ."', '". database::input($product['price_operator']) ."');"
    );
  }

// Complete missing data for stock items
  $stock_items_query = database::query(
    "select si.*, p.brand_id, p.supplier_id, p.gtin, p.mpn, p.taric, p.quantity_unit, p.purchase_price, p.purchase_price_currency_code
      if(si.sku != '', si.sku, p.sku) as sku,
      if(si.gtin != '', si.sku, p.gtin) as gtin,
      if(si.mpn != '', si.sku, p.mpn) as mpn,
      if(si.taric != '', si.sku, p.taric) as taric,
      if(si.weight > 0, si.weight, p.weight) as weight,
      if(si.weight_unit != '', si.weight_unit, p.weight_unit) as weight_unit,
      if(si.length > 0, si.length, p.length) as length,
      if(si.width > 0, si.width, p.width) as width,
      if(si.height > 0, si.height, p.height) as height,
      if(si.length_unit != '', si.length_unit, p.length_unit) as length_unit,
    from ". DB_TABLE_PREFIX ."lc_stock_items si
    left join ". DB_TABLE_PREFIX ."products p on (si.product_id = p.id);"
  );

  while ($stock_item = database::fetch($stock_items_query)) {
    database::query(
      "update ". DB_TABLE_PREFIX ."stock_items
      set brand_id = ". (int)$stock_item['brand_id'] .",
        supplier_id = ". (int)$stock_item['supplier_id'] .",
        sku = ". (int)$stock_item['sku'] .",
        gtin = ". (int)$stock_item['gtin'] .",
        mpn = ". (int)$stock_item['mpn'] .",
        taric = ". (int)$stock_item['taric'] .",
        weight = ". (int)$stock_item['weight'] .",
        weight_unit = '". database::input($stock_item['weight_unit']) ."',
        length = ". (int)$stock_item['length'] .",
        width = ". (int)$stock_item['width'] .",
        height = ". (int)$stock_item['height'] .",
        length_unit = '". database::input($stock_item['length_unit']) ."',
        quantity = ". (float)$stock_item['quantity'] .",
        quantity_unit_id = ". (int)$stock_item['quantity_unit_id'] .",
      where id = ". $stock_item['id'] ."
      limit 1;"
    );

    database::query(
      "insert into ". DB_TABLE_PREFIX ."products_to_stock_items
      (product_id, stock_item_id, price_adjust, price_operator, priority)
      values ('". database::input($stock_item['product_id']) ."', '". database::input($stock_item['stock_item_id']) ."', '". database::input($stock_item['price_adjust']) ."', '". database::input($stock_item['price_operator']) ."', ". (int)$stock_item['priority']) .", );"
    );
  }

// Make sure SKUs are unique
  $duplicate_skus_query = database::query(
    "select sku, count(sku)
    from ". DB_TABLE_PREFIX ."stock_items
    where sku ! = ''
    group by sku
    having count(sku) > 1;"
  );

  while ($duplicate_sku = database::fetch($duplicate)) {

    $stock_items_query = database::query(
      "select id from ". DB_TABLE_PREFIX ."stock_items
       where sku = '". database::input($duplicate_sku['sku']) ."'
       order by id
       limit 1, 9999;"
    );

    $i = 2;
    while ($stock_item = database::fetch($stock_items_query)) {
      database::query(
        "update ". DB_TABLE_PREFIX ."stock_items
        set sku = '". database::input($duplicate_sku['sku'].'-'.$i) ."'
        where id = ". $stock_item['id'] ."
        limit 1;"
      );
      $i++;
    }
  }

// Set SKU for items missing SKU
  $settings_query = database::query(
    "select value from ". DB_TABLE_PREFIX ."settings
    where `key` = 'site_language_code'
    limit 1;"
  );

  $site_language_code = database::fetch($settings_query, 'value');

  $missing_skus_query = database::query(
    "select si.id, sii.name from ". DB_TABLE_PREFIX ."stock_items si
    left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = s.id and sii.language_code = '". $site_language_code ."')
    where sku = '';"
  );

  while ($missing_sku = database::fetch($duplicate)) {

    $stock_item['sku'] = $missing_sku['id'] .'-'. ($missing_sku['name'] ? strtoupper(substr($missing_sku['name'], 0, 4)) : 'UNKN');

    $i = 1;
    while (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where sku = '". database::input($stock_item['sku']) ."' limit 1;"))) {
      $stock_item['sku'] = $missing_sku['id'] .'-'. ($missing_sku['name'] ? strtoupper(substr($missing_sku['name'], 0, 4)) : 'UNKN') .'-'. $i++;
    }

    $stock_items_query = database::query(
      "update ". DB_TABLE_PREFIX ."stock_items
      set sku = '". database::input($stock_item['sku']) ."'
      where id = ". $stock_item['id'] ."
      limit 1;"
    );
  }

// Insert stock before upgrade
  database::query(
    "INSERT INTO `lc_stock_transactions` (`id`, `name`, `notes`, `date_updated`, `date_created`) VALUES
    ('1', 'Initial Sales Deposit', 'Initial transaction to reflect order sales before upgrade.', NOW(), NOW()),
    ('2', 'Initial Stock Deposit', 'Initial transaction to reflect stock quantity before upgrade.', NOW(), NOW());"
  );

  database::query(
    "INSERT INTO `lc_stock_transactions_contents` (`transaction_id`, `product_id`, `combination`, `sku`, `quantity`)
    SELECT '1', product_id, combination, sku, sum(quantity) as quantity FROM `lc_orders_items`
    WHERE order_id IN (
      SELECT id FROM `lc_orders`
      WHERE order_status_id IN (
        SELECT id FROM `lc_order_statuses`
        WHERE is_sale
      )
    )
    GROUP BY product_id, combination, sku;"
  );

  database::query(
    "INSERT INTO `lc_stock_transactions_contents` (`transaction_id`, `stock_item_id`, `quantity_adjustment`)
    SELECT '2', id, quantity FROM `lc_stock_items`
    WHERE quantity != 0
    ORDER BY id;"
  );

// Separate product customization from
  $stock_items_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."products_stock_options;"
  );

  while ($stock_option = database::fetch($stock_items_query)) {
    foreach (explode(',', $stock_option['combination']) as $pair) {

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
  }

// Cleanup tables
  database::query(
    "ALTER TABLE `lc_products`
    DROP COLUMN `brand_id`,
    DROP COLUMN `supplier_id`,
    DROP COLUMN `sku`,
    DROP COLUMN `upc`,
    DROP COLUMN `mpn`,
    DROP COLUMN `gtin`,
    DROP COLUMN `taric`
    DROP COLUMN `purchase_price`
    DROP COLUMN `purchase_price_currency_code`;"
  );

  database::query(
    "ALTER TABLE `lc_stock_items`
    DROP INDEX `product_id`,
    DROP INDEX `sku`,
    DROP COLUMN `product_id`,
    ADD UNIQUE KEY `sku` (`sku`);"
  );

  database::query(
    "ALTER TABLE `lc_orders_items`
    DROP COLUMN `combination`;"
  );

 // Migrate PHP serialized options/customizations to JSON
  $order_items_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."orders_items;"
  );

  while ($item = database::fetch($order_items_query)) {
    $item['data'] = unserialize($item['data']);

    database::query(
      "update ". DB_TABLE_PREFIX ."orders_items
      set data = '". (!empty($item['data']) ? json_encode($item['data'], JSON_UNESCAPED_SLASHES) : '') ."'
      where id = ". (int)$item['id'] ."
      limit 1;"
    );
  }

// Download Product Customization Add-On
  if (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products_customizations;"))) {

    // ...

// Remove Product Customization
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
    "update `". DB_TABLE_PREFIX ."`orders o
    left join (
      select order_id, sum(quantity * price) as subtotal, sum(quantity * tax) as subtotal_tax
      from `". DB_TABLE_PREFIX ."`orders_items
      group by order_id
    ) oi on (oi.order_id = o.id)
    set o.subtotal = if(oi.subtotal, oi.subtotal, 0),
      o.subtotal_tax = if(oi.subtotal_tax, oi.subtotal_tax, 0);"
  );

// Convert Table Charset and Collations
  $collations = [];

  $collations_query = database::query(
    "select COLLATION_NAME FROM `information_schema`.`COLLATIONS`
    where CHARACTER_SET_NAME = 'utf8mb4'
    order by COLLATION_NAME;"
  );

  while ($collation = database::fetch($collations_query, 'COLLATION_NAME')) {
    $collations[] = $collation;
  }

  $engines_query = database::query(
    "show engines;"
  );

  while ($engine = database::fetch($engines_query)) {
    if ($engine['Engine'] == 'Aria') {
      $found_aria = true;
      break;
    }
  }

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
      "alter table `". DB_DATABASE ."`.`". $table['TABLE_NAME'] ."`
      convert to character set utf8mb4 collate ". database::input($new_collation) .";"
    );

    if (!empty($found_aria)) {
      database::query(
        "alter table `". DB_DATABASE ."`.`". $table['TABLE_NAME'] ."`
        engine=Aria;"
      );
    }
  }

  database::query(
    "alter database `". DB_DATABASE ."`
    default character set utf8mb4 collate ". database::input($new_collation) .";"
  );
