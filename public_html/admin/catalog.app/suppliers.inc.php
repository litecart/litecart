<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  breadcrumbs::add(language::translate('title_suppliers', 'Suppliers'));

// Table Rows
  $suppliers = array();

  $suppliers_query = database::query(
    "select id, name from ". DB_TABLE_SUPPLIERS ."
    order by name asc;"
  );

  if ($_GET['page'] > 1) database::seek($suppliers_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

  $page_items = 0;
  while ($supplier = database::fetch($suppliers_query)) {
    $suppliers[] = $supplier;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($suppliers_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_suppliers', 'Suppliers'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, array('app' => $_GET['app'], 'doc' => 'edit_supplier')), language::translate('title_add_new_supplier', 'Add New Supplier'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('suppliers_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($suppliers as $supplier) { ?>
          <tr>
            <td><?php echo functions::form_draw_checkbox('suppliers['. $supplier['id'] .']', $supplier['id']); ?></td>
            <td><a href="<?php echo document::href_link('', array('doc' => 'edit_supplier', 'supplier_id' => $supplier['id']), array('app')); ?>"><?php echo $supplier['name']; ?></a></td>
            <td><a href="<?php echo document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_supplier', 'supplier_id' => $supplier['id'])); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="3"><?php echo language::translate('title_suppliers', 'Suppliers'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
