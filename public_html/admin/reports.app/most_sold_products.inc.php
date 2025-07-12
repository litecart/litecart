<?php

  document::$snippets['title'][] = language::translate('title_most_sold_products', 'Most Sold Products');

  breadcrumbs::add(language::translate('title_reports', 'Reports'));
  breadcrumbs::add(language::translate('title_most_sold_products', 'Most Sold Products'));

  $_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : date('Y-01-01 00:00:00');
  $_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : date('Y-m-d');

  if ($_GET['date_from'] > $_GET['date_to']) {
    list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];
  }

  if ($_GET['date_from'] > date('Y-m-d')) {
    $_GET['date_from'] = date('Y-m-d');
  }

  if ($_GET['date_to'] > date('Y-m-d')) {
    $_GET['date_to'] = date('Y-m-d');
  }

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

// Table Rows
  $rows = [];

  $order_items_query = database::query(
    "select
      oi.product_id,
      oi.sku,
      oi.name,
      sum(oi.quantity) as total_quantity,
      sum(oi.price * oi.quantity) as total_sales,
      sum(oi.tax * oi.quantity) as total_tax

    from ". DB_TABLE_PREFIX ."orders_items oi
    left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
    left join ". DB_TABLE_PREFIX ."products p on (p.id = oi.product_id)

    where o.order_status_id in (
      select id from ". DB_TABLE_PREFIX ."order_statuses
      where is_sale
    )
    ". (!empty($_GET['country_code']) ? "and o.customer_country_code = '". database::input($_GET['country_code']) ."'" : '') ."
		". (!empty($_GET['category_id']) ? "and product_id in (
			select product_id from ". DB_TABLE_PREFIX ."products_to_categories
			where category_id = ". (int)$_GET['category_id'] ."
      or category_id in ('". implode("', '", database::input(array_keys(reference::category($_GET['category_id'])->descendants))) ."')
      )" : "") ."
      and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'
      and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'

    ". (!empty($_GET['manufacturer_id']) ? "and p.manufacturer_id = '". (int)$_GET['manufacturer_id'] ."'" : "") ."

    ". (!empty($_GET['query']) ? "and (
      oi.product_id = '". database::input($_GET['query']) ."'
      or oi.name like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
      or oi.sku like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
      or oi.gtin like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
    )" : "") ."

    group by oi.product_id, oi.sku
    order by total_quantity desc;"
  );

  if (!isset($_GET['download']) && $_GET['page'] > 1) database::seek($order_items_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($order_item = database::fetch($order_items_query)) {
    $rows[] = $order_item;
    if (!isset($_GET['download']) && ++$page_items == settings::get('data_table_rows_per_page')) break;
  }

  if (isset($_GET['download'])) {
    header('Content-Type: application/csv; charset='. language::$selected['charset']);
    header('Content-Disposition: filename="most_sold_products_'. date('Ymd', strtotime($_GET['date_from'])) .'-'. date('Ymd', strtotime($_GET['date_to'])) .'.csv"');
    echo functions::csv_encode($rows);
    exit;
  }

// Number of Rows
  $num_rows = database::num_rows($order_items_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>

<style>
form[name="filter_form"] li {
  vertical-align: middle;
}
select[name="manufacturer_id"] {
  width: 250px;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_most_sold_products', 'Most Sold Products'); ?>
    </div>
  </div>

  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
    <div class="card-filter">

      <?php echo functions::form_draw_hidden_field('app'); ?>
      <?php echo functions::form_draw_hidden_field('doc'); ?>

      <div class="expandable">
        <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. functions::escape_html(language::translate('title_item_name_or_sku', 'Item Name or SKU')) .'"'); ?>
      </div>

      <?php echo functions::form_draw_countries_list('country_code', isset($_GET['country_code']) ? $_GET['country_code'] : '', false, 'style="width: 250px;"'); ?>

      <?php echo functions::form_draw_categories_list('category_id', true, 'placeholder="'. functions::escape_html(language::translate('title_item_name', 'Item Name')) .'"', 'style="width: 320px;"'); ?>

      <?php echo functions::form_draw_manufacturers_list('manufacturer_id', true); ?>

      <div class="input-group">
        <?php echo functions::form_draw_date_field('date_from', true); ?>
        <span class="input-group-text"> - </span>
        <?php echo functions::form_draw_date_field('date_to', true); ?>
      </div>

      <?php echo functions::form_draw_button('filter', language::translate('title_filter_now', 'Filter')); ?>
    </div>

    <div class="card-action">
      <?php echo functions::form_draw_button('download', functions::draw_fonticon('fa-download') .' '. language::translate('title_download', 'Download')); ?>
    </div>

  <?php echo functions::form_draw_form_end(); ?>

  <table class="table table-striped table-hover data-table">
    <thead>
      <tr>
        <th class="main"><?php echo language::translate('title_product', 'Product'); ?></th>
        <th class="text-center"><?php echo language::translate('title_sku', 'SKU'); ?></th>
        <th class="text-center"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
        <th class="text-center"><?php echo language::translate('title_sales', 'Sales'); ?></th>
        <th class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($rows as $row) { ?>
      <tr>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['sku']; ?></td>
        <td class="text-center border-left"><?php echo (float)$row['total_quantity']; ?></td>
        <td class="text-end border-left"><?php echo currency::format($row['total_sales'], false, settings::get('store_currency_code')); ?></td>
        <td class="text-end border-left"><?php echo currency::format($row['total_tax'], false, settings::get('store_currency_code')); ?></td>
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

<script>
  $('select[name="manufacturer_id"]').on('change', function(){
    $('form[name="filter_form"]').submit();
  });

  $('select[name="country_code"] option[value=""]').text('-- <?php echo functions::escape_js(language::translate('title_all_countries', 'All Countries')); ?> --');

  $('select[name="country_code"]').on('change', function(){
    $('form[name="filter_form"]').submit();
  });
</script>