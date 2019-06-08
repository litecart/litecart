<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  breadcrumbs::add(language::translate('title_option_groups', 'Option Groups'));

// Table Rows
  $option_groups = array();

  $option_groups_query = database::query(
    "select pcg.id, pcg.function, pcgi.name from ". DB_TABLE_OPTION_GROUPS ." pcg
    left join ". DB_TABLE_OPTION_GROUPS_INFO ." pcgi on (pcgi.group_id = pcg.id and pcgi.language_code = '". database::input(language::$selected['code']) ."')
    order by pcgi.name asc;"
  );

  while ($group = database::fetch($option_groups_query)) {
    $option_groups[] = $group;
  }

// Number of Rows
  $num_rows = database::num_rows($option_groups_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_option_groups', 'Option Groups'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, array('doc'=> 'edit_option_group'), array('app')), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('option_groups_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
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
          <?php foreach ($option_groups as $option_group) { ?>
          <tr>
            <td><?php echo functions::form_draw_checkbox('configuration_groups['. $option_group['id'] .']', $option_group['id']); ?></td>
            <td style="text-align: center;"><?php echo $option_group['id']; ?></td>
            <td><a href="<?php echo document::href_link('', array('doc' => 'edit_option_group', 'option_group_id' => $option_group['id']), array('app')); ?>"><?php echo $option_group['name']; ?></a></td>
            <td><?php echo $option_group['function']; ?></td>
            <td><a href="<?php echo document::href_link('', array('doc' => 'edit_option_group', 'option_group_id' => $option_group['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="5"><?php echo language::translate('title_option_groups', 'Option Groups'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
