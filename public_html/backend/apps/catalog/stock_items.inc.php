<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  document::$title[] = language::translate('title_stock_items', 'Stock Items');

  breadcrumbs::add(language::translate('title_stock_items', 'Stock Items'));

  if (isset($_POST['delete'])) {

    try {

      if (empty($_POST['stock_items'])) {
        throw new Exception(language::translate('error_must_select_stock_items', 'You must select stock items'));
      }

      foreach ($_POST['stock_items'] as $stock_item_id) {
        $stock_item = new ent_stock_item($stock_item_id);
        $stock_item->delete();
      }

      notices::add('success', sprintf(language::translate('success_deleted_d_stock_items', 'Deleted %d stock_items'), count($_POST['stock_items'])));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (!empty($_GET['query'])) {
    $sql_where_query = [
      "si.id = '". database::input($_GET['query']) ."'",
      "sii.name like '%". database::input($_GET['query']) ."%'",
      "si.sku regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
      "si.mpn regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
      "si.gtin regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
    ];
  }

// Table Rows, Total Number of Rows, Total Number of Pages
  $stock_items = database::query(
    "select si.*, sii.name, oi.reserved, stt.total_deposited, oit.total_withdrawn from ". DB_TABLE_PREFIX ."stock_items si

    left join ". DB_TABLE_PREFIX ."stock_items_info sii on (si.id = sii.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')

    left join (
      select oi.stock_item_id, sum(oi.quantity) as reserved
      from ". DB_TABLE_PREFIX ."orders_items oi
      where oi.order_id in (
        select id from ". DB_TABLE_PREFIX ."orders o
        where order_status_id in (
          select id from ". DB_TABLE_PREFIX ."order_statuses os
          where stock_action = 'reserve'
        )
      )
      group by oi.stock_item_id
    ) oi on (oi.stock_item_id = si.id)

    left join (
      select stock_item_id, sum(quantity_adjustment) as total_deposited
      from ". DB_TABLE_PREFIX ."stock_transactions_contents
      group by stock_item_id
    ) stt on (stt.stock_item_id = si.id)

    left join (
      select stock_item_id, sum(quantity) as total_withdrawn
      from ". DB_TABLE_PREFIX ."orders_items oi
      where oi.order_id in (
        select id from ". DB_TABLE_PREFIX ."orders o
        where order_status_id in (
          select id from ". DB_TABLE_PREFIX ."order_statuses os
          where stock_action = 'withdraw'
        )
      )
      group by oi.stock_item_id
    ) oit on (oit.stock_item_id = si.id)

    where si.id
    ". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
    order by si.sku, sii.name;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

  foreach ($stock_items as $i => $stock_item) {
    if ($stock_item['quantity'] != $stock_item['total_deposited'] - $stock_item['total_withdrawn']) {
      $stock_items[$i]['warning'] = language::translate('text_stock_inconsistency_detected', 'Stock inconsistency detected');
    }
  }

?>
<style>
.fa-exclamation-triangle {
  color: #f00;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <div class="card-title">
        <?php echo $app_icon; ?> <?php echo language::translate('title_stock_items', 'Stock Items'); ?>
      </div>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_button_link(document::ilink(__APP__.'/edit_stock_item'), language::translate('title_create_new_stock_item', 'Create New Stock Item'), '', 'add'); ?>
  </div>

  <?php echo functions::form_begin('search_form', 'get'); ?>
    <div class="card-filter">
      <div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_items', 'Search items').'"'); ?></div>
      <?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
    </div>
  <?php echo functions::form_end(); ?>

  <?php echo functions::form_begin('stock_items_form', 'post'); ?>

    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th><?php echo language::translate('title_gtin', 'GTIN'); ?></th>
          <th><?php echo language::translate('title_mpn', 'MPN'); ?></th>
          <th><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
          <th><?php echo language::translate('title_reserved', 'Reserved'); ?></th>
          <th><?php echo language::translate('title_backordered', 'Backordered'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($stock_items as $stock_item) { ?>
        <tr>
          <td><?php echo functions::form_input_checkbox('stock_items[]', $stock_item['id']); ?></td>
          <td><?php echo $stock_item['id']; ?></td>
          <td><?php if (!empty($stock_item['warning'])) echo functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($stock_item['warning']) .'"'); ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]); ?>"><?php echo $stock_item['name']; ?></a></td>
          <td><?php echo $stock_item['sku']; ?></td>
          <td><?php echo $stock_item['gtin']; ?></td>
          <td><?php echo $stock_item['mpn']; ?></td>
          <td class="text-end"><?php echo (float)$stock_item['quantity']; ?></td>
          <td class="text-end"><?php echo (float)$stock_item['reserved']; ?></td>
          <td class="text-end"><?php echo (float)$stock_item['backordered']; ?></td>
          <td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="11"><?php echo language::translate('title_stock_items', 'Stock Items'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>
    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <ul class="list-inline">
          <li>
            <?php echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"', 'delete'); ?>
          </li>
        </ul>
      </fieldset>
    </div>

  <?php echo functions::form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>

<script>
$('input[name="category_id"]').change(function(e){
  $(this).closest('form').submit();
});

  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>