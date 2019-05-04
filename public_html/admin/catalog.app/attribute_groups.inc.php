<?php
  breadcrumbs::add(language::translate('title_attribute_groups', 'Attribute Groups'), document::link(WS_DIR_ADMIN, array('doc' => 'attribute_groups'), array('app')));
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc'=> 'edit_attribute_group'), array('app')), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_attribute_groups', 'Attribute Groups'); ?></h1>

<?php echo functions::form_draw_form_begin('attributes_form', 'post'); ?>

  <table class="table table-striped table-hover data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th class="text-center"><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="text-center"><?php echo language::translate('title_code', 'Code'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_values', 'Values'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $attribute_groups_query = database::query(
    "select ag.id, ag.code, agi.name from ". DB_TABLE_ATTRIBUTE_GROUPS ." ag
    left join ". DB_TABLE_ATTRIBUTE_GROUPS_INFO ." agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
    order by agi.name asc;"
  );
  while ($attribute_group = database::fetch($attribute_groups_query)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('attributes['. $attribute_group['id'] .']', $attribute_group['id']); ?></td>
        <td style="text-align: center;"><?php echo $attribute_group['id']; ?></td>
        <td><?php echo $attribute_group['code']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_attribute_group', 'group_id' =>$attribute_group['id']), array('app')); ?>"><?php echo $attribute_group['name']; ?></a></td>
        <td style="text-align: center;"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_ATTRIBUTE_VALUES ." where group_id = ". (int)$attribute_group['id'] .";")); ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_attribute_group', 'group_id' => $attribute_group['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6"><?php echo language::translate('title_attributes', 'Attributes'); ?>: <?php echo database::num_rows($attribute_groups_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>