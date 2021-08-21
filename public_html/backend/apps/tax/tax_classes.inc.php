<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_tax_classes', 'Tax Classes');

  breadcrumbs::add(language::translate('title_tax_classes', 'Tax Classes'));

// Table Rows
  $tax_classes = [];

  $tax_classses_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."tax_classes
    order by name asc;"
  );

  if ($_GET['page'] > 1) database::seek($tax_classses_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($tax_class = database::fetch($tax_classses_query)) {
    $tax_classes[] = $tax_class;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($tax_classses_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_tax_classes', 'Tax Classes'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_tax_class'), language::translate('title_create_new_tax_class', 'Create New Tax Class'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('tax_classs_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th><?php echo language::translate('title_name', 'Name'); ?></th>
          <th class="main"><?php echo language::translate('title_description', 'Description'); ?></th>
          <th>&nbsp;</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($tax_classes as $tax_class) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('tax_classes['. $tax_class['id'] .']', $tax_class['id']); ?></td>
          <td><?php echo $tax_class['id']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_tax_class', ['tax_class_id' => $tax_class['id']]); ?>"><?php echo $tax_class['name']; ?></a></td>
          <td style="color: #999;"><?php echo $tax_class['description']; ?></td>
          <td class="text-end"><a href="<?php echo document::href_ilink(__APP__.'/edit_tax_class', ['tax_class_id' => $tax_class['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="5"><?php echo language::translate('title_tax_classes', 'Tax Classes'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_draw_form_end(); ?>

  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
