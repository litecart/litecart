<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/index.html',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/cart.json.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/checkout_cart.html.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/checkout_customer.html.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/checkout_payment.html.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/checkout_shipping.html.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/checkout_summary.html.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/get_address.json.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/option_values.json.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax/zones.json.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'feeds/index.html',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'feeds/sitemap.xml.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/account.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/also_purchased_products.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/campaigns.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/cart.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/categories.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/category_tree.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/filter.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/footer_categories.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/footer_information.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/footer_manufacturers.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/latest_products.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/login.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/logotypes.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/manufacturers.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/most_popular.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/region.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/search.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/similar_products.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/site_links.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/site_menu.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/boxes/slider.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/library/lib_seo_links.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/modules/seo_links/url_category.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/modules/seo_links/url_customer_service.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/modules/seo_links/url_information.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/modules/seo_links/url_manufacturer.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/modules/seo_links/url_product.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/modules/seo_links/url_search.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/printable_order_copy.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/printable_packing_slip.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'categories.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'category.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'checkout.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'create_account.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'customer_service.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'edit_account.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'error_document.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'information.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'login.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'logout.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'manufacturer.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'manufacturers.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'order_history.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'order_process.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'order_success.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'printable_order_copy.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'product.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'push_jobs.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'search.php',
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'select_region.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  file_rename(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'ajax.deleteme');
  file_rename(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'feeds', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'feeds.deleteme');

  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . '.htaccess',
      'search'  => "# Denied content",
      'replace' => "# Solve 401 rewrite and auth conflict on some machines" . PHP_EOL
                .  "ErrorDocument 401 \"Access Forbidden\"" . PHP_EOL
                .  PHP_EOL
                .  "# Denied content",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('WS_DIR_INCLUDES',    WS_DIR_HTTP_HOME . 'includes/');",
      'replace' => "  define('WS_DIR_INCLUDES',    WS_DIR_HTTP_HOME . 'includes/');" . PHP_EOL
                 . "  define('WS_DIR_PAGES',       WS_DIR_HTTP_HOME . 'pages/');",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('WS_DIR_REFERENCES',  WS_DIR_INCLUDES  . 'references/');",
      'replace' => "  define('WS_DIR_REFERENCES',  WS_DIR_INCLUDES  . 'references/');" . PHP_EOL
                 . "  define('WS_DIR_ROUTES',      WS_DIR_INCLUDES  . 'routes/');",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_SERVER',",
      'replace' => "  define('DB_TYPE', 'mysql');" . PHP_EOL
                 . "  define('DB_SERVER',",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_DATABASE_CHARSET',",
      'replace' => "  define('DB_CONNECTION_CHARSET',",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "ErrorDocument 403 ". WS_DIR_HTTP_HOME ."error_document.php?code=403",
      'replace' => "ErrorDocument 403 ". WS_DIR_HTTP_HOME ."index.php/error_document?code=403",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "ErrorDocument 404 ". WS_DIR_HTTP_HOME ."error_document.php?code=404",
      'replace' => "ErrorDocument 404 ". WS_DIR_HTTP_HOME ."index.php/error_document?code=404",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "ErrorDocument 410 ". WS_DIR_HTTP_HOME ."error_document.php?code=410",
      'replace' => "ErrorDocument 410 ". WS_DIR_HTTP_HOME ."index.php/error_document?code=410",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "  RewriteRule ^(?:[a-z]{2}/)?.*-c-([0-9]+)/?$ category.php?category_id=$1&%{QUERY_STRING} [L]",
      'replace' => "  RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "RewriteRule ^(?:[a-z]{2}/)?.*-m-([0-9]+)/?$ manufacturer.php?manufacturer_id=$1&%{QUERY_STRING} [L]",
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "RewriteRule ^(?:[a-z]{2}/)?.*-i-([0-9]+)$ information.php?page_id=$1&%{QUERY_STRING} [L]",
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "RewriteRule ^(?:[a-z]{2}/)?.*-s-([0-9]+)$ customer_service.php?page_id=$1&%{QUERY_STRING} [L]",
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]",
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "RewriteRule ^(?:[a-z]{2}/)?search/(.*)?$ search.php?query=$1&%{QUERY_STRING} [L]",
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "RewriteRule ^(?:[a-z]{2}/)?(.*) $1?%{QUERY_STRING} [L]",
      'replace' => "",
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }
