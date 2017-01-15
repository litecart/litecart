<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('app' => $_GET['app'], 'doc' => 'edit_supplier')), language::translate('title_add_new_supplier', 'Add New Supplier'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_suppliers', 'Suppliers'); ?></h1>

<?php echo functions::form_draw_form_begin('suppliers_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
    $suppliers_query = database::query(
      "select id, name from ". DB_TABLE_SUPPLIERS ."
      order by name asc;"
    );

    if (database::num_rows($suppliers_query) > 0) {
      while ($supplier = database::fetch($suppliers_query)) {
?>
    <tr>
      <td><?php echo functions::form_draw_checkbox('suppliers['. $supplier['id'] .']', $supplier['id']); ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_supplier', 'supplier_id' => $supplier['id']), array('app')); ?>"><?php echo $supplier['name']; ?></a></td>
      <td><a href="<?php echo document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_supplier', 'supplier_id' => $supplier['id'])); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
      }
    }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3"><?php echo language::translate('title_suppliers', 'Suppliers'); ?>: <?php echo database::num_rows($suppliers_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>