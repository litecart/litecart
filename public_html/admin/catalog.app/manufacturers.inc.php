<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_manufacturers', 'Manufacturers');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['manufacturers'])) throw new Exception(language::translate('error_must_select_manufacturers', 'You must select manufacturers'));

      foreach ($_POST['manufacturers'] as $manufacturer_id) {
        $manufacturer = new ent_manufacturer($manufacturer_id);
        $manufacturer->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $manufacturer->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $manufacturers = [];

  $manufacturers_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."manufacturers
    order by name asc;"
  );

  if ($_GET['page'] > 1) database::seek($manufacturers_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($manufacturer = database::fetch($manufacturers_query)) {

    $products_query = database::query(
      "select id from ". DB_TABLE_PREFIX ."products
      where manufacturer_id = ". (int)$manufacturer['id'] .";"
    );

    $manufacturer['num_products'] = database::num_rows($products_query);

    $manufacturers[] = $manufacturer;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($manufacturers_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_manufacturers', 'Manufacturers'); ?>
    </div>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'edit_manufacturer']), language::translate('title_add_new_manufacturer', 'Add New Manufacturer'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('manufacturers_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th></th>
            <th></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_products', 'Products'); ?></th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($manufacturers as $manufacturer) { ?>
          <tr class="<?php echo empty($manufacturer['status']) ? 'semi-transparent' : null; ?>">
            <td><?php echo functions::form_draw_checkbox('manufacturers[]', $manufacturer['id']); ?></td>
            <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($manufacturer['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
            <td><?php echo $manufacturer['featured'] ? functions::draw_fonticon('fa-star', 'style="color: #ffd700;"') : ''; ?></td>
            <td><img src="<?php echo document::href_link($manufacturer['image'] ? WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $manufacturer['image'], 16, 16, 'FIT_USE_WHITESPACING') : 'images/no_image.png'); ?>" alt="" style="width: 16px; height: 16px; vertical-align: bottom;" /> <a href="<?php echo document::href_link('', ['doc' => 'edit_manufacturer', 'manufacturer_id' => $manufacturer['id']], ['app']); ?>"><?php echo $manufacturer['name']; ?></a></td>
            <td class="text-center"><?php echo (int)$manufacturer['num_products']; ?></td>
            <td class="text-end"><a href="<?php echo document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_manufacturer', 'manufacturer_id' => $manufacturer['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="btn-group">
        <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
        <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
