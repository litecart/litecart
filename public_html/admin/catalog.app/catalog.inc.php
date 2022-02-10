<?php

  document::$snippets['title'][] = language::translate('title_catalog', 'Catalog');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));

  if (empty($_GET['category_id'])) $_GET['category_id'] = 0;

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['categories']) && empty($_POST['products'])) {
        throw new Exception(language::translate('error_must_select_categories_or_products', 'You must select categories or products'));
      }

      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ent_category($category_id);
          $category->data['status'] = !empty($_POST['enable']) ? 1 : 0;
          $category->save();
        }
      }

      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ent_product($product_id);
          $product->data['status'] = !empty($_POST['enable']) ? 1 : 0;
          $product->save();
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['duplicate'])) {

    try {
      if (!empty($_POST['categories'])) throw new Exception(language::translate('error_cant_duplicate_category', 'You can\'t duplicate a category'));
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
        $product->data['quantity'] = 0;
        $product->data['categories'] = [$_POST['category_id']];
        $product->data['image'] = null;
        $product->data['images'] = [];

        foreach (['attributes', 'campaigns', 'options', 'options_stock'] as $field) {
          if (empty($product->data[$field])) continue;
          foreach (array_keys($product->data[$field]) as $key) {
            $product->data[$field][$key]['id'] = null;
            if ($field == 'options' && empty($product->data['options'][$key]['values'])) {
              foreach (array_keys($product->data['options'][$key]['values']) as $k) {
                $product->data[$field]['options'][$key]['values'][$k]['id'] = null;
              }
            }
          }
        }

        if (!empty($original->data['images'])) {
          foreach ($original->data['images'] as $image) {
            $product->add_image(FS_DIR_APP . 'images/' . $image['filename']);
          }
        }

        foreach (array_keys($product->data['name']) as $language_code) {
          $product->data['name'][$language_code] .= ' (copy)';
        }

        $product->data['status'] = 0;
        $product->save();
      }

      notices::add('success', sprintf(language::translate('success_duplicated_d_products', 'Duplicated %d products'), count($_POST['products'])));
      header('Location: '. document::link(WS_DIR_ADMIN, ['category_id' => $_POST['category_id']], true));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['copy'])) {

    try {
      if (!empty($_POST['categories'])) throw new Exception(language::translate('error_cant_copy_category', 'You can\'t copy a category'));
      if (empty($_POST['products'])) throw new Exception(language::translate('error_must_select_products', 'You must select products'));
      if (isset($_POST['category_id']) && $_POST['category_id'] == '') throw new Exception(language::translate('error_must_select_category', 'You must select a category'));

      foreach ($_POST['products'] as $product_id) {
        $product = new ent_product($product_id);
        $product->data['categories'][] = $_POST['category_id'];
        $product->save();
      }

      notices::add('success', sprintf(language::translate('success_copied_d_products', 'Copied %d products'), count($_POST['products'])));
      header('Location: '. document::link(WS_DIR_ADMIN, ['category_id' => $_POST['category_id']], true));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['move'])) {

    try {
      if (empty($_POST['categories']) && empty($_POST['products'])) throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
      if (isset($_POST['category_id']) && $_POST['category_id'] == '') throw new Exception(language::translate('error_must_select_category', 'You must select a category'));
      if (isset($_POST['category_id']) && isset($_POST['categories']) && in_array($_POST['category_id'], $_POST['categories'])) throw new Exception(language::translate('error_cant_move_category_to_itself', 'You can\'t move a category to itself'));

      if (isset($_POST['category_id']) && isset($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          if (in_array($_POST['category_id'], array_keys(reference::category($category_id)->descendants))) {
            throw new Exception(language::translate('error_cant_move_category_to_descendant', 'You can\'t move a category to a descendant'));
            break;
          }
        }
      }

      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ent_product($product_id);
          $product->data['categories'] = [$_POST['category_id']];
          $product->save();
        }
        notices::add('success', sprintf(language::translate('success_moved_d_products', 'Moved %d products'), count($_POST['products'])));
      }

      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ent_category($category_id);
          $category->data['parent_id'] = $_POST['category_id'];
          $category->save();
        }
        notices::add('success', sprintf(language::translate('success_moved_d_categories', 'Moved %d categories'), count($_POST['categories'])));
      }

      header('Location: '. document::link(WS_DIR_ADMIN, ['category_id' => $_POST['category_id']], true));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['unmount'])) {

    try {
      if (empty($_POST['categories']) && empty($_POST['products'])) throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
      if (empty($_GET['category_id'])) throw new Exception(language::translate('error_category_must_be_nested_in_another_category_to_unmount', 'A category must be nested in another category to be unmounted'));

      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ent_category($category_id);
          if ($category->data['parent_id'] == $_GET['category_id']) {
            $category->data['parent_id'] = 0;
            $category->save();
          }
        }
        notices::add('success', sprintf(language::translate('success_unmounted_d_categories', 'Unmounted %d categories'), count($_POST['categories'])));
      }

      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ent_product($product_id);
          foreach (array_keys($product->data['categories']) as $key) {
            if ($product->data['categories'][$key] == $_GET['category_id']) {
              unset($product->data['categories'][$key]);
              $product->save();
            }
          }
        }
        notices::add('success', sprintf(language::translate('success_unmounted_d_products', 'Unmounted %d products'), count($_POST['products'])));
      }

      if (isset($_POST['categories']) && in_array($_GET['category_id'], $_POST['categories'])) unset($_GET['category_id']);

      header('Location: '. document::link(WS_DIR_ADMIN, [], true));
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
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<style>
.warning {
  color: #f00;
}
</style>

<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_catalog', 'Catalog'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc'=> 'edit_category', 'parent_id' => $_GET['category_id']]), language::translate('title_add_new_category', 'Add New Category'), '', 'add'); ?></li>
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc'=> 'edit_product'], ['category_id']), language::translate('title_add_new_product', 'Add New Product'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app', true); ?>
    <?php echo functions::form_draw_hidden_field('doc', true); ?>
    <div class="panel-filter">
      <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::link(WS_DIR_ADMIN, [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
      <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('catalog_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
<?php
  $num_category_rows = 0;
  $num_product_rows = 0;

  if (!empty($_GET['query'])) {

    $code_regex = functions::format_regex_code($_GET['query']);
    $query_fulltext = functions::format_mysql_fulltext($_GET['query']);

    $products_query = database::query(
      "select p.id, p.status, p.sold_out_status_id, p.image, p.quantity, p.date_valid_from, p.date_valid_to, pi.name,
      (
        if(p.id = '". database::input($_GET['query']) ."', 10, 0)
        + (match(pi.name) against ('*". database::input($query_fulltext) ."*'))
        + (match(pi.short_description) against ('*". database::input($query_fulltext) ."*') / 2)
        + (match(pi.description) against ('*". database::input($query_fulltext) ."*') / 3)
        + (match(pi.name) against ('". database::input($query_fulltext) ."' in boolean mode))
        + (match(pi.short_description) against ('". database::input($query_fulltext) ."' in boolean mode) / 2)
        + (match(pi.description) against ('". database::input($query_fulltext) ."' in boolean mode) / 3)
        + if(pi.name like '%". database::input($_GET['query']) ."%', 3, 0)
        + if(pi.short_description like '%". database::input($_GET['query']) ."%', 2, 0)
        + if(pi.description like '%". database::input($_GET['query']) ."%', 1, 0)
        + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
        + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
        + if (p.id in (
          select product_id from ". DB_TABLE_PREFIX ."products_options_stock
          where sku regexp '". database::input($code_regex) ."'
        ), 5, 0)
        + if(m.name like '%". database::input($_GET['query']) ."%', 3, 0)
        + if(s.name like '%". database::input($_GET['query']) ."%', 2, 0)
      ) as relevance
      from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
      left join ". DB_TABLE_PREFIX ."manufacturers m on (p.manufacturer_id = m.id)
      left join ". DB_TABLE_PREFIX ."suppliers s on (p.supplier_id = s.id)
      having relevance > 0
      order by relevance desc;"
    );

    if (database::num_rows($products_query)) {
      while ($product = database::fetch($products_query)) {
        $num_product_rows++;

        try {
          $warning = null;

          if ($product['date_valid_from'] > date('Y-m-d H:i:s')) {
            throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_from']))]));
          }

          if ($product['date_valid_to'] > 1970 && $product['date_valid_to'] < date('Y-m-d H:i:s')) {
            throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_to']))]));
          }

          if ($product['quantity'] <= 0) {
            throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
          }

        } catch (Exception $e) {
          $warning = $e->getMessage();
        }
?>
          <tr class="<?php echo empty($product['status']) ? 'semi-transparent' : null; ?>">
            <td><?php echo functions::form_draw_checkbox('products['. $product['id'] .']', $product['id']); ?></td>
            <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($product['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
            <td><?php echo '<img src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $product['image'], 16, 16, 'FIT_USE_WHITESPACING')) .'" alt="" style="width: 16px; height: 16px; vertical-align: bottom;" />'; ?><a href="<?php echo document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_product', 'product_id' => $product['id']]); ?>"> <?php echo $product['name']; ?></a></td>
            <td class="warning"><?php echo !empty($warning) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($warning) .'"') : ''; ?></td>
            <td><a href="<?php echo document::href_ilink('product', ['product_id' => $product['id']]); ?>" title="<?php echo language::translate('title_view', 'View'); ?>" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></td>
            <td class="text-end"><a href="<?php echo document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_product', 'product_id' => $product['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
<?php
      }
    }
?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5"><?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
          </tr>
        </tfoot>
<?php

  } else {

    $category_trail = array_keys(reference::category($_GET['category_id'])->path);
    $num_category_rows = 0;
    $num_product_rows = 0;

    $output_products = function($category_id=0, $depth=1) use (&$output_products, &$num_product_rows) {

      $output = '';

      $products_query = database::query(
        "select p.id, p.status, p.sold_out_status_id, p.image, p.quantity, pi.name, p.date_valid_from, p.date_valid_to, p2c.category_id from ". DB_TABLE_PREFIX ."products p
        left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        left join ". DB_TABLE_PREFIX ."products_to_categories p2c on (p2c.product_id = p.id)
        where ". (!empty($category_id) ? "p2c.category_id = ". (int)$category_id : "(p2c.category_id is null or p2c.category_id = 0)") ."
        group by p.id
        order by pi.name asc;"
      );

      $display_images = true;
      if (database::num_rows($products_query) > 100) {
        $display_images = false;
      }

      while ($product = database::fetch($products_query)) {
        $num_product_rows++;

        try {
          $warning = null;

          if ($product['date_valid_from'] > date('Y-m-d H:i:s')) {
            throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_from']))]));
          }

          if ($product['date_valid_to'] > 1970 && $product['date_valid_to'] < date('Y-m-d H:i:s')) {
            throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_to']))]));
          }

          if ($product['quantity'] <= 0) {
            throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
          }

        } catch (Exception $e) {
          $warning = $e->getMessage();
        }

        $output .= '<tr class="'. (!$product['status'] ? ' semi-transparent' : null) .'">' . PHP_EOL
                 . '  <td>'. functions::form_draw_checkbox('products['. $product['id'] .']', $product['id'], true) .'</td>' . PHP_EOL
                 . '  <td>'. functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($product['status']) ? '#88cc44' : '#ff6644') .';"') .'</td>' . PHP_EOL;

        if ($display_images) {
          $output .= '  <td><img src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $product['image'], 16, 16, 'FIT_USE_WHITESPACING')) .'" style="margin-inline-start: '. ($depth*16) .'px; width: 16px; height: 16px; vertical-align: bottom;" /> <a href="'. document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_product', 'category_id' => $category_id, 'product_id' => $product['id']]) .'">'. ($product['name'] ? $product['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        } else {
          $output .= '  <td><span style="margin-inline-start: '. (($depth+1)*16) .'px;">&nbsp;<a href="'. document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_product', 'category_id' => $category_id, 'product_id' => $product['id']]) .'">'. $product['name'] .'</a></span></td>' . PHP_EOL;
        }

        $output .= '  <td class="warning">'. (!empty($warning) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($warning) .'"') : '') .'</td>' . PHP_EOL
                 . '  <td><a href="'. document::href_ilink('product', ['product_id' => $product['id']]) .'" title="'. language::translate('title_view', 'View') .'" target="_blank">'. functions::draw_fonticon('fa-external-link') .'</a></td>' . PHP_EOL
                 . '  <td class="text-end"><a href="'. document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_product', 'category_id' => $category_id, 'product_id' => $product['id']]) .'" title="'. language::translate('title_edit', 'Edit') .'">'. functions::draw_fonticon('fa-pencil').'</a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
      }

      return $output;
    };

    $category_iterator = function($category_id, $depth) use (&$category_iterator, &$output_products, &$category_trail, &$num_category_rows) {

      $output = '';

      if (empty($category_id)) {
        $output .= '<tr>' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '  <td>'. functions::draw_fonticon('fa-folder-open', 'style="color: #cccc66;"') .' <strong><a href="'. document::href_link('', ['category_id' => '0'], true) .'">['. language::translate('title_root', 'Root') .']</a></strong></td>' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
      }

    // Output subcategories
      $categories_query = database::query(
        "select c.id, c.status, ci.name
        from ". DB_TABLE_PREFIX ."categories c
        left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
        where c.parent_id = ". (int)$category_id ."
        order by c.priority asc, ci.name asc;"
      );

      while ($category = database::fetch($categories_query)) {
        $num_category_rows++;

        $output .= '<tr class="'. (!$category['status'] ? ' semi-transparent' : null) .'">' . PHP_EOL
                 . '  <td>'. functions::form_draw_checkbox('categories['. $category['id'] .']', $category['id'], true) .'</td>' . PHP_EOL
                 . '  <td>'. functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($category['status']) ? '#88cc44' : '#ff6644') .';"') .'</td>' . PHP_EOL;

        if ($category['id'] == $_GET['category_id']) {
          $output .= '  <td>'. functions::draw_fonticon('fa-folder-open', 'style="color: #cccc66; margin-inline-start: '. ($depth*16) .'px;"') .' <strong><a href="'. document::href_link('', ['category_id' => $category['id']], true) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></strong></td>' . PHP_EOL;
        } else if (in_array($category['id'], $category_trail)) {
          $output .= '  <td>'. functions::draw_fonticon('fa-folder-open', 'style="color: #cccc66; margin-inline-start: '. ($depth*16) .'px;"') .' <a href="'. document::href_link('', ['category_id' => $category['id']], true) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        } else {
          $output .= '  <td>'. functions::draw_fonticon('fa-folder', 'style="color: #cccc66; margin-inline-start: '. ($depth*16) .'px;"') .' <a href="'. document::href_link('', ['category_id' => $category['id']], true) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        }

        $output .= '  <td>&nbsp;</td>' . PHP_EOL
                 . '  <td><a href="'. document::href_ilink('category', ['category_id' => $category['id']]) .'" target="_blank">'. functions::draw_fonticon('fa-external-link') .'</a></td>' . PHP_EOL
                 . '  <td class="text-end"><a href="'. document::href_link('', ['app' => $_GET['app'], 'doc' => 'edit_category', 'category_id' => $category['id']]) .'" title="'. language::translate('title_edit', 'Edit') .'">'. functions::draw_fonticon('fa-pencil').'</a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;

        if (in_array($category['id'], $category_trail)) {

          if (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."categories where parent_id = ". (int)$category['id'] ." limit 1;")) > 0
           || database::fetch(database::query("select category_id from ". DB_TABLE_PREFIX ."products_to_categories where category_id = ".(int)$category['id']." limit 1;")) > 0) {
            $output .= $category_iterator($category['id'], $depth+1);

            // Output products
            if (in_array($category['id'], $category_trail)) {
              $output .= $output_products($category['id'], $depth+1);
            }

          } else {

            $output .= '<tr>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td><em style="margin-inline-start: '. (($depth+1)*16) .'px;">'. language::translate('title_empty', 'Empty') .'</em></td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '</tr>' . PHP_EOL;
          }
        }
      }

      // Output products
      if (empty($category_id)) {
        $output .= $output_products($category_id, $depth);
      }

      return $output;
    };

    echo $category_iterator(0, 1);
?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6"><?php echo language::translate('title_categories', 'Categories'); ?>: <?php echo $num_category_rows; ?>, <?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
          </tr>
        </tfoot>
<?php
  }
?>
      </table>

      <p>
        <ul class="list-inline">
          <li><?php echo language::translate('text_with_selected', 'With selected'); ?>:</li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
              <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
            </div>
          </li>
          <li>
            <div>
              <?php echo functions::form_draw_category_field('category_id', isset($_POST['category_id']) ? $_POST['category_id'] : ''); ?>
            </div>
          </li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('move', language::translate('title_move', 'Move'), 'submit', 'onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('warning_mounting_points_will_be_replaced', 'Warning: All current mounting points will be replaced.')) .'\')) return false;"'); ?>
              <?php echo functions::form_draw_button('copy', language::translate('title_copy', 'Copy'), 'submit'); ?>
              <?php echo functions::form_draw_button('duplicate', language::translate('title_duplicate', 'Duplicate'), 'submit'); ?>
            </div>
          </li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('unmount', language::translate('title_unmount', 'Unmount'), 'submit'); ?>
              <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?>
            </div>
          </li>
        </ul>
      </p>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
