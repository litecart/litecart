<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;
  if (empty($_GET['sort'])) $_GET['sort'] = 'date_created';

  document::$snippets['title'][] = language::translate('title_shopping_carts', 'Shopping Carts');

  breadcrumbs::add(language::translate('title_shopping_carts', 'Shopping Carts'));

// Table Rows
  $shopping_carts = [];

  if (!empty($_GET['query'])) {
    $sql_where_query = [
      "sc.id = '". database::input($_GET['query']) ."'",
      "sc.uid = '". database::input($_GET['query']) ."'",
      "sc.customer_email like '%". database::input($_GET['query']) ."%'",
      "sc.customer_tax_id like '%". database::input($_GET['query']) ."%'",
      "concat(sc.customer_company, '\\n', sc.customer_firstname, ' ', sc.customer_lastname, '\\n', sc.customer_address1, '\\n', sc.customer_address2, '\\n', sc.customer_postcode, '\\n', sc.customer_city) like '%". database::input($_GET['query']) ."%'",
      "concat(sc.shipping_company, '\\n', sc.shipping_firstname, ' ', sc.shipping_lastname, '\\n', sc.shipping_address1, '\\n', sc.shipping_address2, '\\n', sc.shipping_postcode, '\\n', sc.shipping_city) like '%". database::input($_GET['query']) ."%'",
      "sc.id in (
        select cart_id from ". DB_TABLE_PREFIX ."shopping_carts_items
        where name like '%". database::input($_GET['query']) ."%'
        or sku like '%". database::input($_GET['query']) ."%'
      )",
    ];
  }

  switch($_GET['sort']) {
    case 'id':
      $sql_sort = "sc.id desc";
      break;
    case 'country':
      $sql_sort = "sc.customer_country_code";
      break;
    default:
      $sql_sort = "sc.date_created desc, sc.id desc";
      break;
  }

  $shopping_carts_query = database::query(
    "select sc.*, sci.num_items from ". DB_TABLE_PREFIX ."shopping_carts sc
    left join (
      select cart_id, count(id) as num_items
      from ". DB_TABLE_PREFIX ."shopping_carts_items
      group by cart_id
    ) sci on (sc.id = sci.cart_id)
    where sc.id
    ". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
    order by $sql_sort;"
  );

  if ($_GET['page'] > 1) database::seek($shopping_carts_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($shopping_cart = database::fetch($shopping_carts_query)) {
    $shopping_carts[] = $shopping_cart;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($shopping_carts_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_shopping_carts', 'Shopping Carts'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_shopping_cart', ['redirect_url' => $_SERVER['REQUEST_URI']]), language::translate('title_create_new_shopping_cart', 'Create New Shopping Cart'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <div class="card-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
      <?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <?php echo functions::form_draw_form_begin('shopping_carts_form', 'post'); ?>

    <table class="table table-striped table-hover table-sortable data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th data-sort="id"><?php echo language::translate('title_id', 'ID'); ?></th>
          <th data-sort="customer" class="main"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
          <th data-sort="country"><?php echo language::translate('title_country', 'Country'); ?></th>
          <th data-sort="items"><?php echo language::translate('title_items', 'Items'); ?></th>
          <th class="text-center"><?php echo language::translate('title_subtotal', 'Subtotal'); ?></th>
          <th data-sort="date_created"><?php echo language::translate('title_date', 'Date'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($shopping_carts as $shopping_cart) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('shopping_carts['.$shopping_cart['id'].']', $shopping_cart['id'], (isset($_POST['shopping_carts']) && in_array($shopping_cart['id'], $_POST['shopping_carts'])) ? $shopping_cart['id'] : false); ?></td>
          <td><?php echo $shopping_cart['id']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_shopping_cart', ['cart_id' => $shopping_cart['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>"><?php echo $shopping_cart['customer_company'] ? $shopping_cart['customer_company'] : $shopping_cart['customer_firstname'] .' '. $shopping_cart['customer_lastname']; ?><?php echo empty($shopping_cart['customer_id']) ? ' <em>('. language::translate('title_guest', 'Guest') .')</em>' : ''; ?></a> <span style="opacity: 0.5;"><?php echo $shopping_cart['customer_tax_id']; ?></span></td>
          <td><?php echo !empty($shopping_cart['customer_country_code']) ? reference::country($shopping_cart['customer_country_code'])->name : ''; ?></td>
          <td class="text-end"><?php echo $shopping_cart['num_items']; ?></td>
          <td class="text-end"><?php echo currency::format($shopping_cart['subtotal'], false, $shopping_cart['currency_code']); ?></td>
          <td class="text-end"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($shopping_cart['date_created'])); ?></td>
          <td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_shopping_cart', ['cart_id' => $shopping_cart['id'], 'redirect_url' => $_SERVER['REQUEST_URI']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="8"><?php echo language::translate('title_shopping_carts', 'Shopping Carts'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_draw_form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>

<script>
  $('input[name="query"]').keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $(this).closest('form').submit();
    }
  });
</script>
