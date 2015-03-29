<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc'=> 'edit_product_group'), array('app')), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_product_groups', 'Product Groups'); ?></h1>

<?php echo functions::form_draw_form_begin('product_groups_form', 'post'); ?>
<table width="100%" class="dataTable">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_id', 'ID'); ?></th>
    <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
    <th><?php echo language::translate('title_values', 'Values'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
  $product_groups_query = database::query(
    "select pg.id, pgi.name from ". DB_TABLE_PRODUCT_GROUPS ." pg
    left join ". DB_TABLE_PRODUCT_GROUPS_INFO ." pgi on (pgi.product_group_id = pg.id and pgi.language_code = '". database::input(language::$selected['code']) ."')
    order by pgi.name asc;"
  );
  while ($product_group = database::fetch($product_groups_query)) {
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo functions::form_draw_checkbox('product_groups['. $product_group['id'] .']', $product_group['id']); ?></td>
    <td style="text-align: center;"><?php echo $product_group['id']; ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_product_group', 'product_group_id' =>$product_group['id']), array('app')); ?>"><?php echo $product_group['name']; ?></a></td>
    <td style="text-align: center;"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ." where product_group_id = '". (int)$product_group['id'] ."';")); ?></td>
    <td><a href="<?php echo document::href_link('', array('doc' => 'edit_product_group', 'product_group_id' => $product_group['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fontawesome_icon('pencil'); ?></a></td>
  </tr>
<?php
  }
?>
  <tr class="footer">
    <td colspan="5"><?php echo language::translate('title_product_groups', 'Product Groups'); ?>: <?php echo database::num_rows($product_groups_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php echo functions::form_draw_form_end(); ?>