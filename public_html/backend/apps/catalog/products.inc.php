<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_products', 'Products');

  breadcrumbs::add(language::translate('title_products', 'Products'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['products'])) throw new Exception(language::translate('error_must_select_products', 'You must select products'));

      foreach ($_POST['products'] as $product_id) {
        $product = new ent_product($product_id);
        $product->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $product->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $products = [];

  $products_query = database::query(
    "select p.id, p.status, pi.name, p.sku, p.gtin, p.image, p.quantity, p.date_valid_from, p.date_valid_to, p.date_created from ". DB_TABLE_PREFIX ."products p
    left join ". DB_TABLE_PREFIX ."products_info pi on (p.id = pi.product_id and language_code = '". database::input(language::$selected['code']) ."')
    order by pi.name asc;"
  );

  if ($_GET['page'] > 1) database::seek($products_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($product = database::fetch($products_query)) {

    try {
      $warning = null;

      if (strtotime($product['date_valid_from']) > time()) {
        throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_from']))]));
      }

      if (strtotime($product['date_valid_to']) > '1971' && $product['date_valid_to'] < date('Y-m-d H:i:s')) {
        throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_to']))]));
      }

      if ($product['quantity'] <= 0) {
        throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
      }

    } catch (Exception $e) {
      $product['warning'] = $e->getMessage();
    }

    $products[] = $product;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($products_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));

  functions::draw_lightbox();
?>
<style>
.warning {
  color: #f00;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_products', 'Products'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'edit_product']), language::translate('title_add_new_product', 'Add New Product'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app', true); ?>
    <?php echo functions::form_draw_hidden_field('doc', true); ?>
    <div class="panel-filter">
      <div style="min-width: 300px;"><?php echo functions::form_draw_category_field('category_id', true); ?></div>
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::link(WS_DIR_ADMIN, [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
      <div style="min-width: 250px;"><?php echo functions::form_draw_brands_list('brand_id', true); ?></div>
      <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('products_form', 'post'); ?>


      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th><?php echo language::translate('title_SKU', 'SKU'); ?></th>
            <th><?php echo language::translate('title_gtin', 'GTIN'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_created', 'Created'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($products as $product) { ?>
          <tr class="<?php echo empty($product['status']) ? 'semi-transparent' : ''; ?>">
            <td><?php echo functions::form_draw_checkbox('products['. $product['id'] .']', $product['id']); ?></td>
            <td><?php echo functions::draw_fonticon($product['status'] ? 'on' : 'off'); ?></td>
            <td class="warning"><?php echo !empty($warning) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. htmlspecialchars($warning) .'"') : ''; ?></td>
            <td><?php echo $product['id']; ?></td>
            <td><?php echo $product['sku']; ?></td>
            <td><?php echo $product['gtin']; ?></td>
            <td><img src="<?php echo document::href_link($product['image'] ? WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $product['image'], 16, 16, 'FIT_USE_WHITESPACING') : 'images/no_image.png'); ?>" alt="" style="width: 16px; height: 16px; vertical-align: bottom;" /> <a href="<?php echo document::href_link('', ['doc' => 'edit_product', 'product_id' => $product['id']], ['app']); ?>"><?php echo $product['name']; ?></a></td>
            <td><?php echo language::strftime(language::$selected['format_datetime'], strtotime($product['date_created'])); ?></td>
            <td class="text-right"><a href="<?php echo document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_product', 'product_id' => $product['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="9"><?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_rows; ?></td>
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
