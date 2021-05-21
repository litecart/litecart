<?php
	if (!isset($_GET['page'])) $_GET['page'] = 1;

// Table Rows
  $transactions = [];

  $stock_transactions_query = database::query(
    "select id, name, date_created from ". DB_TABLE_PREFIX ."stock_transactions t
    where id

    ". (!empty($_GET['query']) ? "t.name like '%". database::input($_GET['query']) ."%' or t.notes like '%". database::input($_GET['query']) ."%'" : "") ."
    ". (!empty($_GET['query']) ? "and id in (
      select transaction_id from ". DB_TABLE_PREFIX ."stock_transactions_contents
      where stock_item_id in (
        select id from ". DB_TABLE_PREFIX ."stock_items si
        left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = si.id and language_code = '". database::input(language::$selected['code']) ."')
        where (
          sii.name like '%". database::input($_GET['query']) ."%'
          or si.sku like '%". database::input($_GET['query']) ."%'
          or si.mpn like '%". database::input($_GET['query']) ."%'
          or si.gtin like '%". database::input($_GET['query']) ."%'
        )
      )
    )" : "") ."
    ". (!empty($_GET['date_from']) ? "and t.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', strtotime($_GET['date_from'])), date('d', strtotime($_GET['date_from'])), date('Y', strtotime($_GET['date_from'])))) ."'" : null) ."
    ". (!empty($_GET['date_to']) ? "and t.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($_GET['date_to'])), date('d', strtotime($_GET['date_to'])), date('Y', strtotime($_GET['date_to'])))) ."'" : null) ."
    order by date_created desc;"
  );

  if (database::num_rows($stock_transactions_query) > 0) {

    if ($_GET['page'] > 1) database::seek($stock_transactions_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

    $page_items = 0;
    while ($transaction = database::fetch($stock_transactions_query)) {
      $transactions[] = $transaction;
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }

// Number of Rows
  $num_rows = database::num_rows($stock_transactions_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_stock_transactions', 'Stock Transactions'); ?>
  </div>

  <div class="panel-action">
      <?php echo functions::form_draw_link_button(document::link('', ['app' => $_GET['app'], 'doc' => 'edit_stock_transaction']), language::translate('title_create_new_transaction', 'Create New Transaction'), '', 'add'); ?>
  </div>

    <?php echo functions::form_draw_form_begin('search_form', 'get') . functions::form_draw_hidden_field('app', true) . functions::form_draw_hidden_field('doc', true); ?>
  <div class="panel-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
      <div class="input-group">
        <?php echo functions::form_draw_date_field('date_from', true, 'style="width: 50%;"'); ?>
        <span class="input-group-text">-</span>
        <?php echo functions::form_draw_date_field('date_to', true, 'style="width: 50%;"'); ?>
      </div>
      <div>
        <?php echo functions::form_draw_button('search', language::translate('title_filter', 'Filter'), 'submit'); ?>
      </div>
  </div>
    <?php echo functions::form_draw_form_end(); ?>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('stock_transactions_form', 'post'); ?>

      <table class="table table-striped data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_date', 'Date'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $transaction) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('stock_transactions['. $transaction['id'] .']', $transaction['id']); ?></td>
          <td><?php echo $transaction['id']; ?></td>
          <td><a href="<?php echo document::href_link('', ['doc' => 'edit_stock_transaction', 'transaction_id' => $transaction['id']], ['app']); ?>"><?php echo $transaction['name']; ?></a></td>
          <td><?php echo language::strftime(language::$selected['format_datetime'], strtotime($transaction['date_created'])); ?></td>
          <td><a href="<?php echo document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_stock_transaction', 'transaction_id' => $transaction['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5"><?php echo language::translate('title_stock_transactions', 'Stock Transactions'); ?>: <?php echo database::num_rows($stock_transactions_query); ?></td>
          </tr>
        </tfoot>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination(ceil(database::num_rows($stock_transactions_query)/settings::get('data_table_rows_per_page'))); ?>
  </div>
</div>