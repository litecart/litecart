<?php

  document::$snippets['title'][] = language::translate('title_most_shopping_customers', 'Most Shopping Customers');

  breadcrumbs::add(language::translate('title_reports', 'Reports'));
  breadcrumbs::add(language::translate('title_most_shopping_customers', 'Most Shopping Customers'));

  $_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : null;
  $_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : date('Y-m-d');

  if ($_GET['date_from'] > $_GET['date_to']) list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];

  $date_first_order = database::query("select min(date_created) from ". DB_TABLE_PREFIX ."orders limit 1;")->fetch();
  $date_first_order = date('Y-m-d', strtotime($date_first_order['min(date_created)']));
  if (empty($date_first_order)) $date_first_order = date('Y-m-d');
  if ($_GET['date_from'] < $date_first_order) $_GET['date_from'] = $date_first_order;

  if ($_GET['date_from'] > date('Y-m-d')) $_GET['date_from'] = date('Y-m-d');
  if ($_GET['date_to'] > date('Y-m-d')) $_GET['date_to'] = date('Y-m-d');

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

// Table Rows
  $customers = [];

  $customers_query = database::query(
    "select
      sum(o.total - total_tax) as total_amount,
      o.customer_id as id,
      if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) as name,
      customer_email as email
    from ". DB_TABLE_PREFIX ."orders o
    where o.order_status_id in (
      select id from ". DB_TABLE_PREFIX ."order_statuses
      where is_sale
    )
    ". (!empty($_GET['date_from']) ? "and o.date_created >= '". date('Y-m-d H:i:s', strtotime($_GET['date_from'])) ."'" : '') ."
    ". (!empty($_GET['date_to']) ? "and o.date_created <= '". date('Y-m-d H:i:s', strtotime($_GET['date_to'])) ."'" : '') ."
    group by if(o.customer_id, o.customer_id, o.customer_email)
    order by total_amount desc;"
  );

  if (!isset($_GET['download']) && $_GET['page'] > 1) database::seek($customers_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($customer = database::fetch($customers_query)) {
    $customers[] = $customer;
    if (!isset($_GET['download']) && ++$page_items == settings::get('data_table_rows_per_page')) break;
  }

  if (isset($_GET['download'])) {
    header('Content-Type: application/csv; charset='. mb_http_output());
    header('Content-Disposition: filename="most_shopping_customers_'. date('Ymd', strtotime($_GET['date_from'])) .'-'. date('Ymd', strtotime($_GET['date_to'])) .'.csv"');
    echo functions::csv_encode($customers);
    exit;
  }

// Number of Rows
  $num_rows = database::num_rows($customers_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));
?>

<style>
form[name="filter_form"] li {
  vertical-align: middle;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_most_shopping_customers', 'Most Shopping Customers'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_begin('filter_form', 'get'); ?>
      <ul class="list-inline">
        <li>
          <div class="input-group" style="max-width: 380px;">
            <?php echo functions::form_date_field('date_from', true); ?>
            <span class="input-group-text"> - </span>
            <?php echo functions::form_date_field('date_to', true); ?>
          </div>
        </li>
        <li><?php echo functions::form_button('filter', language::translate('title_filter_now', 'Filter')); ?></li>
        <li><?php echo functions::form_button('download', language::translate('title_download', 'Download')); ?></li>
      </ul>
    <?php echo functions::form_end(); ?>
  </div>

  <table class="table table-striped table-hover data-table">
    <thead>
      <tr>
        <th><?php echo language::translate('title_customer', 'Customer'); ?></th>
        <th width="100%"><?php echo language::translate('title_email_address', 'Email Address'); ?></th>
        <th class="text-center"><?php echo language::translate('title_total_amount', 'Total Amount'); ?></th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($customers as $customer) { ?>
      <tr>
        <td><?php echo !empty($customer['id']) ? '<a href="'. document::href_ilink('customers/edit_customer', ['customer_id' => $customer['id']]) .'">'. $customer['name'] .'</a>' : $customer['name'] .' <em>('. language::translate('title_guest', 'Guest') .')</em>'; ?></td>
        <td><?php echo $customer['email']; ?></td>
        <td class="text-end"><?php echo currency::format($customer['total_amount'], false, settings::get('store_currency_code')); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>
