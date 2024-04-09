<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;

// Table Rows, Total Number of Rows, Total Number of Pages
  $transactions = database::query(
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
    ". (!empty($_GET['date_from']) ? "and t.date_created >= '". date('Y-m-d H:i:s', strtotime($_GET['date_from'])) ."'" : '') ."
    ". (!empty($_GET['date_to']) ? "and t.date_created <= '". date('Y-m-d H:i:s', strtotime($_GET['date_to'])) ."'" : '') ."
    order by date_created desc;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_stock_transactions', 'Stock Transactions'); ?>
    </div>
  </div>

  <div class="card-action">
      <?php echo functions::form_button_link(document::ilink(__APP__.'/edit_stock_transaction'), language::translate('title_create_new_transaction', 'Create New Transaction'), '', 'add'); ?>
  </div>

    <?php echo functions::form_begin('search_form', 'get'); ?>
  <div class="card-filter">
      <div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword').'"'); ?></div>
      <div class="input-group">
        <?php echo functions::form_input_datetime('date_from', true, 'style="width: 50%;"'); ?>
        <span class="input-group-text">-</span>
        <?php echo functions::form_input_datetime('date_to', true, 'style="width: 50%;"'); ?>
      </div>
      <div>
        <?php echo functions::form_button('search', language::translate('title_filter', 'Filter'), 'submit'); ?>
      </div>
    </div>
  <?php echo functions::form_end(); ?>

  <?php echo functions::form_begin('stock_transactions_form', 'post'); ?>

    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th class="text-end"><?php echo language::translate('title_date', 'Date'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($transactions as $transaction) { ?>
      <tr>
        <td><?php echo functions::form_checkbox('stock_transactions[]', $transaction['id']); ?></td>
        <td><?php echo $transaction['id']; ?></td>
        <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_stock_transaction', ['transaction_id' => $transaction['id']]); ?>"><?php echo $transaction['name']; ?></a></td>
        <td class="text-end"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($transaction['date_created'])); ?></td>
        <td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_transaction', ['transaction_id' => $transaction['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
      </tr>
      <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5"><?php echo language::translate('title_stock_transactions', 'Stock Transactions'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>