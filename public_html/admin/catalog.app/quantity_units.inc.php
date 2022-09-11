<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_quantity_units', 'Quantity Units');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_quantity_units', 'Quantity Units'));

// Table Rows
  $quantity_units = [];

  $quantity_units_query = database::query(
    "select qu.id, qui.name, qui.description from ". DB_TABLE_PREFIX ."quantity_units qu
    left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qu.id = qui.quantity_unit_id and qui.language_code = '". database::input(language::$selected['code']) ."')
    order by qu.priority, qui.name asc;"
  );

  if ($_GET['page'] > 1) database::seek($quantity_units_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($unit = database::fetch($quantity_units_query)) {
    $quantity_units[] = $unit;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($quantity_units_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_quantity_units', 'Quantity Units'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc' => 'edit_quantity_unit'], true), language::translate('title_add_new_unit', 'Add New Unit'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('quantity_units_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th><?php echo language::translate('title_name', 'Name'); ?></th>
            <th class="main"><?php echo language::translate('title_description', 'Description'); ?></th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($quantity_units as $quantity_unit) { ?>
          <tr>
            <td><?php echo functions::form_draw_checkbox('quantity_units[]', $quantity_unit['id']); ?></td>
            <td><?php echo $quantity_unit['id']; ?></td>
            <td><a href="<?php echo document::href_link('', ['doc' => 'edit_quantity_unit', 'quantity_unit_id' => $quantity_unit['id']], true); ?>"><?php echo $quantity_unit['name']; ?></a></td>
            <td><?php echo $quantity_unit['description']; ?></td>
            <td class="text-end"><a href="<?php echo document::href_link('', ['doc' => 'edit_quantity_unit', 'quantity_unit_id' => $quantity_unit['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="5"><?php echo language::translate('title_quantity_units', 'Quantity Units'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
