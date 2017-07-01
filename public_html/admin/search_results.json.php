<?php
  require_once('../includes/app_header.inc.php');

  user::require_login();

  document::$template = settings::get('store_template_admin');
  document::$layout = 'ajax';

  $app_themes = array_column(functions::admin_get_apps(), 'theme', 'code');

  $search_results = array();

// Products
  $search_results['products'] = array(
    'name' => language::translate('title_products', 'Products'),
    'theme' => $app_themes['catalog'],
    'results' => array(),
  );
  $products_query = database::query(
    "select p.id, p.default_category_id, pi.name from ". DB_TABLE_PRODUCTS ." p
    left join ".  DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
    where (
      p.id = '". database::input($_GET['query']) ."'
      or p.keywords like '%". database::input($_GET['query']) ."%'
      or pi.name like '%". database::input($_GET['query']) ."%'
    )
    order by pi.name asc
    limit 5;"
  );
  while ($product = database::fetch($products_query)) {
    $search_results['products']['results'][] = array(
      'id' => $product['id'],
      'title' => $product['name'],
      'description' => $product['default_category_id'] ? reference::category($product['default_category_id'])->name : '['.language::translate('title_root', 'Root').']',
      'url' => document::link(WS_DIR_ADMIN, array('app' => 'catalog', 'doc' => 'edit_product', 'product_id' => $product['id'])),
    );
  }

// Customers
  $search_results['customers'] = array(
    'name' => language::translate('title_customers', 'Customers'),
    'theme' => $app_themes['customers'],
    'results' => array(),
  );
  $customers_query = database::query(
    "select id, concat(firstname, ' ', lastname) as name, email from ". DB_TABLE_CUSTOMERS ."
    where (
      id = '". database::input($_GET['query']) ."'
      or concat(firstname, ' ', lastname) like '%". database::input($_GET['query']) ."%'
      or email like '%". database::input($_GET['query']) ."%'
    )
    order by date_created desc
    limit 5;"
  );
  while ($customer = database::fetch($customers_query)) {
    $search_results['customers']['results'][] = array(
      'id' => $customer['id'],
      'title' => $customer['name'],
      'description' => $customer['email'],
      'url' => document::link(WS_DIR_ADMIN, array('app' => 'customers', 'doc' => 'edit_customer', 'customer_id' => $customer['id'])),
    );
  }

// Orders
  $search_results['orders'] = array(
    'name' => language::translate('title_orders', 'Orders'),
    'theme' => $app_themes['orders'],
    'results' => array(),
  );
  $orders_query = database::query(
    "select id, concat(customer_firstname, ' ', customer_lastname) as customer_name from ". DB_TABLE_ORDERS ."
    where (
      id = '". database::input($_GET['query']) ."'
      or customer_email like '%". database::input($_GET['query']) ."%'
      or customer_tax_id like '%". database::input($_GET['query']) ."%'
      or concat(customer_firstname, ' ', customer_lastname) like '%". database::input($_GET['query']) ."%'
      or customer_company like '%". database::input($_GET['query']) ."%'
      or shipping_tracking_id like '%". database::input($_GET['query']) ."%'
      or payment_transaction_id like '%". database::input($_GET['query']) ."%'
    )
    order by date_created desc
    limit 5;"
  );
  while ($order = database::fetch($orders_query)) {
    $search_results['orders']['results'][] = array(
      'id' => $order['id'],
      'title' => language::translate('title_order', 'Order') .' '. $order['id'],
      'description' => $order['customer_name'],
      'url' => document::link(WS_DIR_ADMIN, array('app' => 'orders', 'doc' => 'edit_order', 'order_id' => $order['id'])),
    );
  }

  echo json_encode($search_results);
  exit;
