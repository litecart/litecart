<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc'=> 'edit_option_group'), array('app')), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_option_groups', 'Option Groups'); ?></h1>

<?php echo functions::form_draw_form_begin('option_groups_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th class="text-center"><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_function', 'Function'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $option_groups_query = database::query(
    "select pcg.id, pcg.function, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
    left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". database::input(language::$selected['code']) ."')
    order by pcgi.name asc;"
  );
  while ($option_group = database::fetch($option_groups_query)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('configuration_groups['. $option_group['id'] .']', $option_group['id']); ?></td>
        <td style="text-align: center;"><?php echo $option_group['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_option_group', 'option_group_id' => $option_group['id']), array('app')); ?>"><?php echo $option_group['name']; ?></a></td>
        <td><?php echo $option_group['function']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_option_group', 'option_group_id' => $option_group['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><?php echo language::translate('title_option_groups', 'Option Groups'); ?>: <?php echo database::num_rows($option_groups_query); ?></td>
      </tr>
    </tfoot>
  </table>

<?php echo functions::form_draw_form_end(); ?>