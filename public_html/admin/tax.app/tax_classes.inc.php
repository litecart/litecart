<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_tax_class'), true), language::translate('title_add_new_tax_class', 'Add New Tax Class'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_tax_classes', 'Tax Classes'); ?></h1>

<?php echo functions::form_draw_form_begin('tax_classs_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th><?php echo language::translate('title_name', 'Name'); ?></th>
        <th class="main"><?php echo language::translate('title_description', 'Description'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $tax_classses_query = database::query(
    "select * from ". DB_TABLE_TAX_CLASSES ."
    order by name asc;"
  );

  if (database::num_rows($tax_classses_query) > 0) {

    while ($tax_class = database::fetch($tax_classses_query)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('tax_classes['. $tax_class['id'] .']', $tax_class['id']); ?></td>
        <td><?php echo $tax_class['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_tax_class', 'tax_class_id' => $tax_class['id']), true); ?>"><?php echo $tax_class['name']; ?></a></td>
        <td style="color: #999;"><?php echo $tax_class['description']; ?></td>
        <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_tax_class', 'tax_class_id' => $tax_class['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><?php echo language::translate('title_tax_classes', 'Tax Classes'); ?>: <?php echo database::num_rows($tax_classses_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>