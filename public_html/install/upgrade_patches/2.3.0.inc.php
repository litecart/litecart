<?php

// Delete old files
  $deleted_files = [
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
    FS_DIR_ADMIN . 'catalog.app/edit_manufacturer.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_product.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_quantity_unit.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_sold_out_status.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_supplier.inc.php',
    FS_DIR_ADMIN . 'catalog.app/index.html',
    FS_DIR_ADMIN . 'catalog.app/manufacturers.inc.php',
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
    FS_DIR_APP . 'ext/chartist/',
    FS_DIR_APP . 'ext/featherlight/',
    FS_DIR_APP . 'ext/fontawesome/',
    FS_DIR_APP . 'ext/index.html',
    FS_DIR_APP . 'ext/jquery/',
    FS_DIR_APP . 'ext/trumbowyg/',
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
    FS_DIR_APP . 'includes/templates/default.catalog/pages/manufacturer.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/pages/manufacturers.inc.php',
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
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturer_links.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturer_logotypes.inc.php',
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
    FS_DIR_APP . 'pages/ajax',
    FS_DIR_APP . 'pages/categories.inc.php',
    FS_DIR_APP . 'pages/category.inc.php',
    FS_DIR_APP . 'pages/checkout.inc.php',
    FS_DIR_APP . 'pages/create_account.inc.php',
    FS_DIR_APP . 'pages/customer_service.inc.php',
    FS_DIR_APP . 'pages/edit_account.inc.php',
    FS_DIR_APP . 'pages/error_document.inc.php',
    FS_DIR_APP . 'pages/feeds',
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
    FS_DIR_APP . 'pages/product.inc.php',
    FS_DIR_APP . 'pages/push_jobs.inc.php',
    FS_DIR_APP . 'pages/regional_settings.inc.php',
    FS_DIR_APP . 'pages/reset_password.inc.php',
    FS_DIR_APP . 'pages/search.inc.php',
    FS_DIR_APP . 'pages/ajax/cart.json.inc.php',
    FS_DIR_APP . 'frontend/pages/checkout/cart.inc.php',
    FS_DIR_APP . 'frontend/pages/checkout/customer.inc.php',
    FS_DIR_APP . 'frontend/pages/checkout/payment.inc.php',
    FS_DIR_APP . 'frontend/pages/checkout/shipping.inc.php',
    FS_DIR_APP . 'frontend/pages/checkout/summary.inc.php',
    FS_DIR_APP . 'pages/ajax/get_address.json.inc.php',
    FS_DIR_APP . 'pages/ajax/index.html',
    FS_DIR_APP . 'pages/ajax/zones.json.inc.php',
    FS_DIR_APP . 'pages/feeds/index.html',
    FS_DIR_APP . 'pages/feeds/sitemap.xml.inc.php',
    FS_DIR_APP . 'includes/library/lib_compression.inc.php',
    FS_DIR_APP . 'includes/functions/func_password.inc.php',
    FS_DIR_APP . 'frontend/templates/default.admin/',
    FS_DIR_APP . 'frontend/templates/default.catalog/',
  ];

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }

// Move some files
  file_rename($file, FS_DIR_ADMIN . '.htpasswd', FS_DIR_APP . '.htpasswd');

  file_rename($file, FS_DIR_APP . 'includes/config.inc.php', FS_DIR_STORAGE . 'config.inc.php');

  foreach (glob(FS_DIR_ADMIN . '*.app') as $file) {
    file_rename($file, FS_DIR_APP . 'backend/apps/' . preg_replace('\.app$', '', basename($file)));
  }

  foreach (glob(FS_DIR_ADMIN . '*.widget') as $file) {
    file_rename($file, FS_DIR_APP . 'backend/widgets/' . preg_replace('\.widget$', '', basename($file)));
  }

  foreach (glob(FS_DIR_APP . 'cache/*') as $file) {
    file_rename($file, FS_DIR_APP . 'storage/cache/' . preg_replace('#^'. preg_quote(FS_DIR_APP . 'cache/', '#') .'#', FS_DIR_STORAGE . 'cache/', $file));
  }

  foreach (glob(FS_DIR_APP . 'data/*') as $file) {
    file_rename($file, FS_DIR_APP . 'storage/data/' . preg_replace('#^'. preg_quote(FS_DIR_APP . 'data/', '#') .'#', FS_DIR_STORAGE . 'data/', $file));
  }

  foreach (glob(FS_DIR_APP . 'images/*') as $file) {
    file_rename($file, FS_DIR_APP . 'storage/images/' . preg_replace('#^'. preg_quote(FS_DIR_APP . 'images/', '#') .'#', FS_DIR_STORAGE . 'images/', $file));
  }

  foreach (glob(FS_DIR_APP . 'logs/*') as $file) {
    file_rename($file, FS_DIR_APP . 'storage/logs/' . preg_replace('#^'. preg_quote(FS_DIR_APP . 'logs/', '#') .'#', FS_DIR_STORAGE . 'logs/', $file));
  }

  file_delete(FS_DIR_ADMIN);
  file_delete(FS_DIR_APP .'cache/');
  file_delete(FS_DIR_APP .'data/');
  file_delete(FS_DIR_APP .'images/');
  file_delete(FS_DIR_APP .'logs/');

  foreach (glob(FS_DIR_APP . 'includes/boxes/*') as $file) {
    file_rename($file, FS_DIR_APP . 'frontend/boxes/' . basename($file);
  }

  file_delete(FS_DIR_APP . 'includes/boxes/');

  foreach (glob(FS_DIR_APP . 'ext/*') as $file) {
    file_rename($file, FS_DIR_APP . 'vendor/' . basename($file));
  }

  file_delete(FS_DIR_APP . 'ext/');

  foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog') as $file) {
    file_rename($file, FS_DIR_APP . 'frontend/templates/' . preg_replace('\.catalog$', '', basename($file)));
  }

  file_delete(FS_DIR_APP . 'includes/templates/');

  foreach (glob(FS_DIR_APP . 'vqmod/xml/*') as $file) {
    copy($file, FS_DIR_STORAGE. 'vmods/');
    file_delete(FS_DIR_APP . 'vqmod/');
  }

// Modify some files
  $modified_files = [
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "SetEnv HTTP_MOD_REWRITE On",
      'replace' => "SetEnv MOD_REWRITE On",
    ],
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]",
      'replace' => "RewriteRule ^.*$ index.php [QSA,L]",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "DB_TABLE_PREFIX",
      'replace' => "DB_PREFIX",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'brands_info`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'brands_info`');" . PHP_EOL
                 . "  define('DB_TABLE_NEWSLETTER_RECIPIENTS',             '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'newsletter_recipients`');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_CATEGORIES_INFO',                   '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'categories_info`');" . PHP_EOL,
      'replace' => "",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
      'replace' => "  define('FS_DIR_ADMIN',       FS_DIR_APP . 'backend/');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
      'replace' => "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                 . "  define('FS_DIR_STORAGE',     FS_DIR_APP . 'storage/');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
      'replace' => "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                 . "  define('WS_DIR_STORAGE',     WS_DIR_APP . 'storage/');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "## Backwards Compatible Directory Definitions (LiteCart <2.2)  #######",
      'replace' => "## Backward Compatible Directory Definitions (LiteCart <2.2) #########",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "// Database tables",
      'replace' => "// Database Tables - Backward Compatibility (LiteCart <2.3)",
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

  $modified_files = [
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => '#AuthUserFile ".*?.htpasswd"#',
      'replace' => 'AuthUserFile "'. FS_DIR_APP .'.htpasswd"',
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'pattern'  => '#'. preg_quote('## Backward Compatible Directory Definitions (LiteCart <2.2)', '#') .'.*?'. preg_quote('## Database ##########################################################', '#') .'#',
      'replace' => '## Database ##########################################################',
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'pattern'  => '#'. preg_quote(PHP_EOL, '#') .'// Password Encryption Salt.*?\);'. preg_quote(PHP_EOL, '#') .'#m',
      'replace' => '',
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify_regex($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

// Adjust tables
  $columns_query = database::query(
    "select * from information_schema.COLUMNS
    where TABLE_SCHEMA = '". DB_DATABASE ."'
    and TABLE_NAME like '". DB_PREFIX ."%';"
  );

  while ($column = database::fetch($columns_query)) {
    switch ($column['COLUMN_NAME']) {
      case 'id':
        break;

      case 'date_updated':
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp not null default current_timestamp on update current_timestamp;"
        );
        break;

      case 'date_created':
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp not null default current_timestamp;"
        );
        break;

      default:
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". $column['COLUMN_TYPE'] ." null". (!empty($column['COLUMN_DEFAULT']) ? ' default ' . $column['COLUMN_DEFAULT'] : '') .";"
        );
        break;
    }
  }

// Remove some indexes if they exist
  if (database::num_rows("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '". DB_PREFIX ."brands_info' AND INDEX_NAME = 'manufacturer' AND INDEX_SCHEMA = '". DB_PREFIX ."brands_info';")) {
    database::query("ALTER TABLE `". DB_PREFIX ."brands_info` DROP INDEX `manufacturer`;");
  }

  if (database::num_rows("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = '". DB_PREFIX ."brands_info' AND INDEX_NAME = 'manufacturer_info' AND INDEX_SCHEMA = '". DB_PREFIX ."brands_info';")) {
    database::query("ALTER TABLE `". DB_PREFIX ."brands_info` DROP INDEX `manufacturer_info`;");
  }
