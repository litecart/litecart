<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc'=> 'edit_product_group'), array('app')), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_product_groups', 'Product Groups'); ?></h1>

<?php echo functions::form_draw_form_begin('product_groups_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th class="text-center"><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_values', 'Values'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $product_groups_query = database::query(
    "select pg.id, pgi.name from ". DB_TABLE_PRODUCT_GROUPS ." pg
    left join ". DB_TABLE_PRODUCT_GROUPS_INFO ." pgi on (pgi.product_group_id = pg.id and pgi.language_code = '". database::input(language::$selected['code']) ."')
    order by pgi.name asc;"
  );
  while ($product_group = database::fetch($product_groups_query)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('product_groups['. $product_group['id'] .']', $product_group['id']); ?></td>
        <td style="text-align: center;"><?php echo $product_group['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_product_group', 'product_group_id' =>$product_group['id']), array('app')); ?>"><?php echo $product_group['name']; ?></a></td>
        <td style="text-align: center;"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ." where product_group_id = '". (int)$product_group['id'] ."';")); ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_product_group', 'product_group_id' => $product_group['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><?php echo language::translate('title_product_groups', 'Product Groups'); ?>: <?php echo database::num_rows($product_groups_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>