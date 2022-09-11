<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_attribute_groups', 'Attribute Groups');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_attribute_groups', 'Attribute Groups'));

// Table Rows
  $attribute_groups = [];

  $attribute_groups_query = database::query(
    "select ag.id, ag.code, agi.name from ". DB_TABLE_PREFIX ."attribute_groups ag
    left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
    order by agi.name asc;"
  );

  if ($_GET['page'] > 1) database::seek($attribute_groups_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($attribute_group = database::fetch($attribute_groups_query)) {
    $attribute_groups[] = $attribute_group;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($attribute_groups_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_attribute_groups', 'Attribute Groups'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc'=> 'edit_attribute_group'], ['app']), language::translate('title_create_new_group', 'Create New Group'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">

    <?php echo functions::form_draw_form_begin('attributes_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th class="text-center"><?php echo language::translate('title_id', 'ID'); ?></th>
            <th class="text-center"><?php echo language::translate('title_code', 'Code'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_values', 'Values'); ?></th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($attribute_groups as $attribute_group) { ?>
          <tr>
            <td><?php echo functions::form_draw_checkbox('attributes[]', $attribute_group['id']); ?></td>
            <td style="text-align: center;"><?php echo $attribute_group['id']; ?></td>
            <td><?php echo $attribute_group['code']; ?></td>
            <td><a href="<?php echo document::href_link('', ['doc' => 'edit_attribute_group', 'group_id' => $attribute_group['id']], ['app']); ?>"><?php echo $attribute_group['name']; ?></a></td>
            <td style="text-align: center;"><?php echo database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."attribute_values where group_id = ". (int)$attribute_group['id'] .";")); ?></td>
            <td><a href="<?php echo document::href_link('', ['doc' => 'edit_attribute_group', 'group_id' => $attribute_group['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="6"><?php echo language::translate('title_attributes', 'Attributes'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
