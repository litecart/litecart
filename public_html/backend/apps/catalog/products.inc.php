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
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['clone'])) {

    try {
      if (!empty($_POST['categories'])) throw new Exception(language::translate('error_cant_clone_category', 'You can\'t clone a category'));
      if (empty($_POST['products'])) throw new Exception(language::translate('error_must_select_products', 'You must select products'));
      if (empty($_POST['category_id'])) throw new Exception(language::translate('error_must_select_category', 'You must select a category'));

      foreach ($_POST['products'] as $product_id) {
        $original = new ent_product($product_id);
        $product = new ent_product();

        $product->data = $original->data;
        $product->data['id'] = null;
        $product->data['status'] = 0;
        $product->data['code'] = '';
        $product->data['sku'] = '';
        $product->data['mpn'] = '';
        $product->data['gtin'] = '';
        $product->data['categories'] = [$_POST['category_id']];
        $product->data['quantity'] = 0;
        $product->data['image'] = null;
        $product->data['images'] = [];

        foreach (['attributes', 'campaigns', 'stock_items'] as $field) {
          if (empty($product->data[$field])) continue;
          foreach (array_keys($product->data[$field]) as $key) {
            $product->data[$field][$key]['id'] = null;
          }
        }

        if (!empty($original->data['images'])) {
          foreach ($original->data['images'] as $image) {
            $product->add_image(FS_DIR_STORAGE . 'images/' . $image['filename']);
          }
        }

        foreach (array_keys($product->data['name']) as $language_code) {
          $product->data['name'][$language_code] .= ' (copy)';
        }

        $product->data['status'] = 0;
        $product->save();
      }

      notices::add('success', sprintf(language::translate('success_cloned_d_products', 'Cloned %d products'), count($_POST['products'])));
      header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (!empty($_POST['categories'])) throw new Exception(language::translate('error_only_products_are_supported', 'Only products are supported for this operation'));
      if (empty($_POST['products'])) throw new Exception(language::translate('error_must_select_products', 'You must select products'));

      foreach ($_POST['products'] as $product_id) {
        $product = new ent_product($product_id);
        $product->delete();
      }

      notices::add('success', sprintf(language::translate('success_deleted_d_products', 'Deleted %d products'), count($_POST['products'])));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $products = [];

  if (!empty($_GET['query'])) {

    $products_query = database::query(
      "select p.id, p.status, p.sold_out_status_id, p.image, p.quantity, p.date_valid_from, p.date_valid_to, pi.name, b.name,
      (
        if(p.id = '". database::input($_GET['query']) ."', 10, 0)
        + (match(pi.name) against ('*". database::input($_GET['query']) ."*'))
        + (match(pi.short_description) against ('*". database::input($_GET['query']) ."*') / 2)
        + (match(pi.description) against ('*". database::input($_GET['query']) ."*') / 3)
        + (match(pi.name) against ('". database::input($_GET['query']) ."' in boolean mode))
        + (match(pi.short_description) against ('". database::input($_GET['query']) ."' in boolean mode) / 2)
        + (match(pi.description) against ('". database::input($_GET['query']) ."' in boolean mode) / 3)
        + if(pi.name like '%". database::input($_GET['query']) ."%', 3, 0)
        + if(pi.short_description like '%". database::input($_GET['query']) ."%', 2, 0)
        + if(pi.description like '%". database::input($_GET['query']) ."%', 1, 0)
        + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
        + if (p.id in (
          select product_id from ". DB_TABLE_PREFIX ."stock_items
          where sku regexp '". database::input($code_regex) ."'
        ), 5, 0)
        + if(b.name like '%". database::input($_GET['query']) ."%', 3, 0)
        + if(s.name like '%". database::input($_GET['query']) ."%', 2, 0)
      ) as relevance
      from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
      left join ". DB_TABLE_PREFIX ."brands b on (p.brand_id = b.id)
      left join ". DB_TABLE_PREFIX ."suppliers s on (p.supplier_id = s.id)
      having relevance > 0
      order by relevance desc;"
    );

    if (!empty($_GET['category_id'])) {
      unset($_GET['category_id']);
    }

  } else if (!empty($_GET['category_id'])) {

    $products_query = database::query(
      "select p.id, p.status, pi.name, p.sku, p.gtin, p.image, p.quantity, p.date_valid_from, p.date_valid_to, p.date_created from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (p.id = pi.product_id and language_code = '". database::input(language::$selected['code']) ."')
      where p.id in (
        select product_id from ". DB_TABLE_PREFIX ."products_to_categories ptc
        where category_id = ". (int)$_GET['category_id'] ."
      )
      order by status desc, pi.name asc;"
    );

  } else {

    $products_query = database::query(
      "select p.id, p.status, pi.name, p.sku, p.gtin, p.image, p.quantity, p.date_valid_from, p.date_valid_to, p.date_created from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (p.id = pi.product_id and language_code = '". database::input(language::$selected['code']) ."')
      order by status desc, pi.name asc;"
    );
  }

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

<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_products', 'Products'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_product'), language::translate('title_add_new_product', 'Add New Product'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app', true); ?>
    <?php echo functions::form_draw_hidden_field('doc', true); ?>
    <div class="card-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::ilink(null, [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
      <div style="min-width: 300px;"><?php echo functions::form_draw_category_field('category_id', true); ?></div>
      <div style="min-width: 250px;"><?php echo functions::form_draw_brands_list('brand_id', true); ?></div>
      <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <?php echo functions::form_draw_form_begin('products_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th class="text-end"><?php echo language::translate('title_created', 'Created'); ?></th>
          <th>&nbsp;</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($products as $product) { ?>
        <tr class="<?php echo empty($product['status']) ? 'semi-transparent' : ''; ?>">
          <td><?php echo functions::form_draw_checkbox('products['. $product['id'] .']', $product['id']); ?></td>
          <td><?php echo functions::draw_fonticon($product['status'] ? 'on' : 'off'); ?></td>
          <td class="warning"><?php echo !empty($warning) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. htmlspecialchars($warning) .'"') : ''; ?></td>
          <td><img class="thumbnail" src="<?php echo document::href_link(functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . ($product['image'] ? $product['image'] : 'no_image.png'), 64, 64, 'FIT_USE_WHITESPACING')); ?>" alt="" /></td>
          <td><?php echo $product['id']; ?></td>
          <td><?php echo $product['sku']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['id']]); ?>"><?php echo $product['name']; ?></a></td>
          <td class="text-end"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($product['date_created'])); ?></td>
          <td class="text-end"><a href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="9"><?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <ul class="list-inline">
      <li>
        <div class="btn-group">
          <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
          <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
        </div>
      </li>
      <li>
        <?php echo functions::form_draw_button('clone', language::translate('title_clone', 'Clone'), 'submit', '', 'fa-copy'); ?>
      </li>
      <li>
        <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"', 'delete'); ?>
      </li>
    </ul>

  <?php echo functions::form_draw_form_end(); ?>

  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>

<script>
$('input[name="category_id"]').change(function(e){
  $(this).closest('form').submit();
});
</script>
