<?php
  require_once('../includes/app_header.inc.php');

  user::require_login();

  document::$template = settings::get('store_template_admin');
  document::$layout = 'ajax';

  $app_themes = array_column(functions::admin_get_apps(), 'theme', 'code');

  $search_results = [];

  try {

    if (empty($_GET['query'])) throw new Exception('Nothing to search for');

  // Products
    $search_results['products'] = [
      'name' => language::translate('title_products', 'Products'),
      'theme' => $app_themes['catalog'],
      'results' => [],
    ];

    $code_regex = functions::format_regex_code($_GET['query']);

    $products_query = database::query(
      "select p.id, p.default_category_id, pi.name,
      (
        if(p.id = '". database::input($_GET['query']) ."', 10, 0)
        + (match(pi.name) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode))
        + (match(pi.short_description) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode) / 2)
        + (match(pi.description) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode) / 3)
        + if(pi.name like '%". database::input($_GET['query']) ."%', 3, 0)
        + if(pi.short_description like '%". database::input($_GET['query']) ."%', 2, 0)
        + if(pi.description like '%". database::input($_GET['query']) ."%', 1, 0)
        + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
        + if (p.id in (
          select product_id from ". DB_TABLE_PREFIX ."products_options_stock
          where sku regexp '". database::input($code_regex) ."'
        ), 5, 0)
      ) as relevance

      from ". DB_TABLE_PREFIX ."products p

      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

      having relevance > 0
      order by relevance desc, id asc
      limit 5;"
    );

    while ($product = database::fetch($products_query)) {
      $search_results['products']['results'][] = [
        'id' => $product['id'],
        'title' => $product['name'],
        'description' => $product['default_category_id'] ? reference::category($product['default_category_id'])->name : '['.language::translate('title_root', 'Root').']',
        'url' => document::link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'edit_product', 'product_id' => $product['id']]),
      ];
    }

  // Customers
    $search_results['customers'] = [
      'name' => language::translate('title_customers', 'Customers'),
      'theme' => $app_themes['customers'],
      'results' => [],
    ];

    $customers_query = database::query(
      "select id, concat(firstname, ' ', lastname) as name, email,
      (
        if(id = '". database::input($_GET['query']) ."', 10, 0)
        + if(email like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(tax_id like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(concat(company, ' ', firstname, ' ', lastname, ' ', address1, ' ', address2, ' ', postcode, ' ', city) like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(concat(shipping_company, ' ', shipping_firstname, ' ', shipping_lastname, ' ', shipping_address1, ' ', shipping_address2, ' ', shipping_postcode, ' ', shipping_city) like '%". database::input($_GET['query']) ."%', 5, 0)
      ) as relevance
      from ". DB_TABLE_PREFIX ."customers
      having relevance > 0
      order by relevance desc, id desc
      limit 5;"
    );

    while ($customer = database::fetch($customers_query)) {
      $search_results['customers']['results'][] = [
        'id' => $customer['id'],
        'title' => $customer['name'],
        'description' => $customer['email'],
        'url' => document::link(WS_DIR_ADMIN, ['app' => 'customers', 'doc' => 'edit_customer', 'customer_id' => $customer['id']]),
      ];
    }

  // Orders
    $search_results['orders'] = [
      'name' => language::translate('title_orders', 'Orders'),
      'theme' => $app_themes['orders'],
      'results' => [],
    ];

    $orders_query = database::query(
      "select id, concat(customer_firstname, ' ', customer_lastname) as customer_name,
      (
        if(id = '". database::input($_GET['query']) ."', 10, 0)
        + if(reference like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(customer_email like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(customer_tax_id like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(concat(customer_company, ' ', customer_firstname, ' ', customer_lastname, ' ', customer_address1, ' ', customer_address2, ' ', customer_postcode, ' ', customer_city) like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(concat(shipping_company, ' ', shipping_firstname, ' ', shipping_lastname, ' ', shipping_address1, ' ', shipping_address2, ' ', shipping_postcode, ' ', shipping_city) like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(shipping_tracking_id like '%". database::input($_GET['query']) ."%', 5, 0)
        + if(payment_transaction_id like '%". database::input($_GET['query']) ."%', 5, 0)
      ) as relevance
      from ". DB_TABLE_PREFIX ."orders
      having relevance > 0
      order by relevance desc, id desc
      limit 5;"
    );

    while ($order = database::fetch($orders_query)) {
      $search_results['orders']['results'][] = [
        'id' => $order['id'],
        'title' => language::translate('title_order', 'Order') .' '. $order['id'],
        'description' => $order['customer_name'],
        'url' => document::link(WS_DIR_ADMIN, ['app' => 'orders', 'doc' => 'edit_order', 'order_id' => $order['id']]),
      ];
    }

  } catch(Exception $e) {
    // Do nothing
  }

  echo json_encode($search_results, JSON_UNESCAPED_SLASHES);
  exit;
