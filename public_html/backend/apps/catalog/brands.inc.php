<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_brands', 'Brands');

  breadcrumbs::add(language::translate('title_brands', 'Brands'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['brands'])) throw new Exception(language::translate('error_must_select_brands', 'You must select brands'));

      foreach ($_POST['brands'] as $brand_id) {
        $brand = new ent_brand($brand_id);
        $brand->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $brand->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $brands = [];

  $brands_query = database::query(
    "select * from ". DB_PREFIX ."brands
    order by name asc;"
  );

  if ($_GET['page'] > 1) database::seek($brands_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($brand = database::fetch($brands_query)) {

    $products_query = database::query(
      "select id from ". DB_PREFIX ."products
      where brand_id = ". (int)$brand['id'] .";"
    );

    $brand['num_products'] = database::num_rows($products_query);

    $brands[] = $brand;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($brands_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_brands', 'Brands'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'edit_brand']), language::translate('title_add_new_brand', 'Add New Brand'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('brands_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_products', 'Products'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($brands as $brand) { ?>
          <tr class="<?php echo empty($brand['status']) ? 'semi-transparent' : ''; ?>">
            <td><?php echo functions::form_draw_checkbox('brands['. $brand['id'] .']', $brand['id']); ?></td>
            <td><?php echo functions::draw_fonticon($brand['status'] ? 'on' : 'off'); ?></td>
            <td><?php echo $brand['featured'] ? functions::draw_fonticon('fa-star', 'style="color: #ffd700;"') : ''; ?></td>
            <td><img src="<?php echo document::href_link($brand['image'] ? WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand['image'], 16, 16, 'FIT_USE_WHITESPACING') : 'images/no_image.png'); ?>" alt="" style="width: 16px; height: 16px; vertical-align: bottom;" /> <a href="<?php echo document::href_link('', ['doc' => 'edit_brand', 'brand_id' => $brand['id']], ['app']); ?>"><?php echo $brand['name']; ?></a></td>
            <td class="text-center"><?php echo (int)$brand['num_products']; ?></td>
            <td class="text-right"><a href="<?php echo document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_brand', 'brand_id' => $brand['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6"><?php echo language::translate('title_brands', 'Brands'); ?>: <?php echo $num_rows; ?></td>
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
