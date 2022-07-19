<?php

  document::$snippets['title'][] = language::translate('title_category_tree', 'Category Tree');

  breadcrumbs::add(language::translate('title_category_tree', 'Category Tree'));

  if (empty($_GET['category_id'])) $_GET['category_id'] = 0;

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['categories']) && empty($_POST['products'])) {
        throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
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
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['clone'])) {

    try {
      if (empty($_POST['categories']) && empty($_POST['products'])) {
        throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
      }

      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $original_category = new ent_category($category_id);
          $new_category = new ent_category();

          $new_category->data = $original_category->data;
          $new_category->data['id'] = null;
          $new_category->data['status'] = 0;
          $new_category->data['code'] = '';

          foreach (array_keys($new_category->data['name']) as $language_code) {
            $new_category->data['name'][$language_code] .= ' (copy)';
          }

          if (!empty($original_category->data['image'])) {
            $new_category->save_image('storage://images/' . $original_category->data['image']);
          }

          $new_category->save();
        }
      }

      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $original_product = new ent_product($product_id);
          $new_product = new ent_product();

          $new_product->data = $original_product->data;
          $new_product->data['id'] = null;
          $new_product->data['status'] = 0;
          $new_product->data['code'] = '';
          $new_product->data['categories'] = [$_POST['category_id']];
          $new_product->data['image'] = null;
          $new_product->data['images'] = [];

          foreach (['attributes', 'campaigns', 'stock_options'] as $field) {
            if (empty($new_product->data[$field])) continue;
            foreach (array_keys($new_product->data[$field]) as $key) {
              $new_product->data[$field][$key]['id'] = null;
            }
          }

          if (!empty($original_product->data['images'])) {
            foreach ($original_product->data['images'] as $image) {
              $new_product->add_image('storage://images/' . $image['filename']);
            }
          }

          foreach (array_keys($new_product->data['name']) as $language_code) {
            $new_product->data['name'][$language_code] .= ' (copy)';
          }

          $new_product->save();
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
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

      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ent_product($product_id);
          $product->data['categories'][] = $_POST['category_id'];
          $product->save();
        }
      }

      notices::add('success', sprintf(language::translate('success_copied_d_products', 'Copied %d products'), count($_POST['products'])));
      header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
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
      }

      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ent_category($category_id);
          $category->data['parent_id'] = $_POST['category_id'];
          $category->save();
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['unmount'])) {

    try {

      if (empty($_POST['categories']) && empty($_POST['products'])) {
        throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
      }

      if (empty($_GET['category_id'])) throw new Exception(language::translate('error_category_must_be_nested_in_another_category_to_unmount', 'A category must be nested in another category to be unmounted'));

      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ent_category($category_id);
          if ($category->data['parent_id'] == $_GET['category_id']) {
            $category->data['parent_id'] = 0;
            $category->save();
          }
        }
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
      }

      if (isset($_POST['categories']) && in_array($_GET['category_id'], $_POST['categories'])) unset($_GET['category_id']);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($_POST['categories']) && empty($_POST['products'])) {
        throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
      }

      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ent_product($product_id);
          $product->delete();
        }
      }

      if (!empty($_POST['categories'])) {
        foreach (array_reverse($_POST['categories']) as $category_id) {
          $category = new ent_category($category_id);
          $category->delete();
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<style>
.warning .fa {
  color: #f00;
}
.thumbnail {
  display: inline-block;
  width: 24px;
  height: 24px;
  vertical-align: middle;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_catalog', 'Catalog'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_category', ['parent_id' => $_GET['category_id']]), language::translate('title_create_new_category', 'Create New Category'), '', 'add'); ?></li>
      <li><?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_product', [], ['category_id']), language::translate('title_create_new_product', 'Create New Product'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('search_form', 'get'); ?>
    <div class="card-filter">
     <div class="expandable"><?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::ilink('', [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
     <div><?php echo functions::form_draw_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>

  <?php echo functions::form_draw_form_begin('catalog_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_price', 'Price'); ?></th>
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
      "select p.id, p.status, p.image, pi.name, p.image, pp.price, ptsi.num_stock_items, ptsi.quantity, ptsi.quantity - oi.total_reserved as quantity_available, p.sold_out_status_id, p.date_valid_from, p.date_valid_to, (
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
        + if (p.id in (
          select product_id from ". DB_TABLE_PREFIX ."products_to_stock_items
          where stock_item_id in (
            select id from ". DB_TABLE_PREFIX ."stock_items
            where sku regexp '". database::input($code_regex) ."'
            or gtin regexp '". database::input($code_regex) ."'
            or mpn regexp '". database::input($code_regex) ."'
          )
        ), 5, 0)
        + if(b.name like '%". database::input($_GET['query']) ."%', 3, 0)
        + if(s.name like '%". database::input($_GET['query']) ."%', 2, 0)
      ) as relevance

      from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
      left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)
      left join ". DB_TABLE_PREFIX ."suppliers s on (s.id = p.supplier_id)

      left join (
        select product_id, `". database::input(settings::get('store_currency_code')) ."` as price
        from ". DB_TABLE_PREFIX ."products_prices
      ) pp on (pp.product_id = p.id)

      left join (
        select ptsi.product_id, ptsi.stock_item_id, count(ptsi.stock_item_id) as num_stock_items, sum(si.quantity) as quantity
        from ". DB_TABLE_PREFIX ."products_to_stock_items ptsi
        left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = ptsi.stock_item_id)
        group by ptsi.product_id
      ) ptsi on (ptsi.product_id = p.id)

      left join (
        select oi.stock_item_id, sum(oi.quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
        left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
        where o.order_status_id in (
          select id from ". DB_TABLE_PREFIX ."order_statuses
          where stock_action = 'reserve'
        )
        group by oi.stock_item_id
      ) oi on (oi.stock_item_id = ptsi.stock_item_id)

      group by p.id
      having relevance > 0
      order by relevance desc;"
    );

    while ($product = database::fetch($products_query)) {
      $num_product_rows++;

      try {
        $warning = null;

        if (!empty($product['date_valid_from']) && strtotime($product['date_valid_from']) > time()) {
          throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_from']))]));
        }

        if (!empty($product['date_valid_to']) && strtotime($product['date_valid_to']) < time()) {
          throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_to']))]));
        }

        if ($product['num_stock_items'] && $product['quantity'] <= 0) {
          throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
        }

      } catch (Exception $e) {
        $warning = $e->getMessage();
      }
?>
        <tr class="<?php echo empty($product['status']) ? 'semi-transparent' : ''; ?>">
          <td><?php echo functions::form_draw_checkbox('products[]', $product['id']); ?></td>
          <td><?php echo functions::draw_fonticon($product['status'] ? 'on' : 'off'); ?></td>
          <td class="warning"><?php echo !empty($warning) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($warning) .'"') : ''; ?></td>
          <td><?php echo '<img class="thumbnail" src="'. document::href_rlink(functions::image_thumbnail('storage://images/' . $product['image'], 24, 24)) .'" alt="" />'; ?><a href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['id']]); ?>"> <?php echo $product['name'] ? $product['name'] : '('. language::translate('title_untitled', 'Untitled') .')'; ?></a></td>
          <td class="text-end"><?php echo currency::format($product['price']); ?></td>
          <td><a href="<?php echo document::href_ilink('f:product', ['product_id' => $product['id']]); ?>" title="<?php echo language::translate('title_view', 'View'); ?>" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
<?php
    }
?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="7"><?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
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
        "select p.id, p.status, p.code, p.sold_out_status_id, p.image, pi.name, pp.price, ptsi.num_stock_items, ptsi.quantity, ptsi.quantity - oi.total_reserved as quantity_available, p.date_valid_from, p.date_valid_to, p2c.category_id

        from ". DB_TABLE_PREFIX ."products p
        left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        left join ". DB_TABLE_PREFIX ."products_to_categories p2c on (p2c.product_id = p.id)

        left join (
          select product_id, `". database::input(settings::get('store_currency_code')) ."` as price
          from ". DB_TABLE_PREFIX ."products_prices
        ) pp on (pp.product_id = p.id)

        left join (
        select ptsi.product_id, ptsi.stock_item_id, count(ptsi.stock_item_id) as num_stock_items, sum(si.quantity) as quantity
        from ". DB_TABLE_PREFIX ."products_to_stock_items ptsi
        left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = ptsi.stock_item_id)
        group by ptsi.product_id
        ) ptsi on (ptsi.product_id = p.id)

        left join (
          select oi.stock_item_id, sum(oi.quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
          left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
          where o.order_status_id in (
            select id from ". DB_TABLE_PREFIX ."order_statuses
            where stock_action = 'reserve'
          )
          group by oi.stock_item_id
        ) oi on (oi.stock_item_id = ptsi.stock_item_id)

        where ". (!empty($category_id) ? "p.id in (
          select product_id from ". DB_TABLE_PREFIX ."products_to_categories ptc
          where category_id = ". (int)$category_id ."
        )" : "p2c.category_id = 0") ."

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

          if (!empty($product['date_valid_from']) && $product['date_valid_from'] > date('Y-m-d H:i:s')) {
            throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_from']))]));
          }

          if (!empty($product['date_valid_to']) && $product['date_valid_to'] < date('Y-m-d H:i:s')) {
            throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => language::strftime(language::$selected['format_date'], strtotime($product['date_valid_to']))]));
          }

          if ($product['num_stock_items'] && $product['quantity'] <= 0) {
            throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
          }

        } catch (Exception $e) {
          $warning = $e->getMessage();
        }

        $output .= '<tr class="'. (!$product['status'] ? ' semi-transparent' : '') .'">' . PHP_EOL
                 . '  <td>'. functions::form_draw_checkbox('products[]', $product['id'], true) .'</td>' . PHP_EOL
                 . '  <td>'. functions::draw_fonticon(!empty($product['status']) ? 'on' : 'off') .'</td>' . PHP_EOL
                 . '  <td class="warning">'. (!empty($warning) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($warning) .'"') : '') .'</td>' . PHP_EOL;

        if ($display_images) {
          $output .= '  <td><img class="thumbnail" src="'. document::href_rlink(functions::image_thumbnail('storage://images/' . $product['image'], 24, 24)) .'" style="margin-inline-start: '. ($depth*16) .'px;" /> <a href="'. document::href_ilink(__APP__.'/edit_product', ['category_id' => $category_id, 'product_id' => $product['id']]) .'">'. ($product['name'] ? $product['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        } else {
          $output .= '  <td><span style="margin-inline-start: '. (($depth+1)*16) .'px;">&nbsp;<a href="'. document::href_ilink(__APP__.'/edit_product', ['category_id' => $category_id, 'product_id' => $product['id']]) .'">'. $product['name'] .'</a></span></td>' . PHP_EOL;
        }

        $output .= '  <td class="text-end">'. currency::format($product['price']) .'</td>' . PHP_EOL
                 . '  <td><a href="'. document::href_ilink('f:product', ['product_id' => $product['id']]) .'" title="'. language::translate('title_view', 'View') .'" target="_blank">'. functions::draw_fonticon('fa-external-link') .'</a></td>' . PHP_EOL
                 . '  <td class="text-end"><a class="btn btn-default btn-sm" href="'. document::href_ilink(__APP__.'/edit_product', ['category_id' => $category_id, 'product_id' => $product['id']]) .'" title="'. language::translate('title_edit', 'Edit') .'">'. functions::draw_fonticon('fa-pencil').'</a></td>' . PHP_EOL
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
                 . '  <td></td>' . PHP_EOL
                 . '  <td>'. functions::draw_fonticon('fa-folder-open fa-lg', 'style="color: #cc6;"') .' <strong><a href="'. document::href_ilink(null, ['category_id' => '0']) .'">['. language::translate('title_root', 'Root') .']</a></strong></td>' . PHP_EOL
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

        $output .= '<tr class="'. ($category['status'] ? null : ' semi-transparent') .'">' . PHP_EOL
                 . '  <td>'. functions::form_draw_checkbox('categories[]', $category['id'], true) .'</td>' . PHP_EOL
                 . '  <td>'. functions::draw_fonticon($category['status'] ? 'on' : 'off') .'</td>' . PHP_EOL
                 . '  <td></td>' . PHP_EOL;

        if ($category['id'] == $_GET['category_id']) {
          $output .= '  <td>'. functions::draw_fonticon('fa-folder-open fa-lg', 'style="color: #cc6; margin-inline-start: '. ($depth*16) .'px;"') .' <strong><a href="'. document::href_ilink(null, ['category_id' => $category['id']]) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></strong></td>' . PHP_EOL;
        } else if (in_array($category['id'], $category_trail)) {
          $output .= '  <td>'. functions::draw_fonticon('fa-folder-open fa-lg', 'style="color: #cc6; margin-inline-start: '. ($depth*16) .'px;"') .' <a href="'. document::href_ilink(null, ['category_id' => $category['id']]) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        } else {
          $output .= '  <td>'. functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cc6; margin-inline-start: '. ($depth*16) .'px;"') .' <a href="'. document::href_ilink(null, ['category_id' => $category['id']]) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        }

        $output .= '  <td></td>' . PHP_EOL
                 . '  <td><a href="'. document::href_ilink('category', ['category_id' => $category['id']]) .'" target="_blank">'. functions::draw_fonticon('fa-external-link') .'</a></td>' . PHP_EOL
                 . '  <td class="text-end"><a class="btn btn-default btn-sm" href="'. document::href_ilink(__APP__.'/edit_category', ['category_id' => $category['id']]) .'" title="'. language::translate('title_edit', 'Edit') .'">'. functions::draw_fonticon('fa-pencil').'</a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;

        if (in_array($category['id'], $category_trail)) {

          if (database::query("select id from ". DB_TABLE_PREFIX ."categories where parent_id = ". (int)$category['id'] ." limit 1;")->num_rows
           || database::query("select category_id from ". DB_TABLE_PREFIX ."products_to_categories where category_id = ".(int)$category['id']." limit 1;")->num_rows) {
            $output .= $category_iterator($category['id'], $depth+1);

            // Output products
            if (in_array($category['id'], $category_trail)) {
              $output .= $output_products($category['id'], $depth+1);
            }

          } else {

            $output .= '<tr>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
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
            <td colspan="7"><?php echo language::translate('title_categories', 'Categories'); ?>: <?php echo $num_category_rows; ?>, <?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
          </tr>
        </tfoot>
<?php
  }
?>
    </table>

    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <ul class="list-inline">
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
              <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
            </div>
          </li>
          <li>
            <div style="min-width: 250px;">
              <?php echo functions::form_draw_category_field('category_id', true); ?>
            </div>
          </li>
          <li>
            <div class="btn-group">
              <?php echo functions::form_draw_button('move', language::translate('title_move', 'Move'), 'submit', 'onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('warning_mounting_points_will_be_replaced', 'Warning: All current mounting points will be replaced.')) .'\')) return false;"'); ?>
              <?php echo functions::form_draw_button('copy', language::translate('title_copy', 'Copy'), 'submit'); ?>
              <?php echo functions::form_draw_button('clone', language::translate('title_clone', 'Clone'), 'submit', '', 'fa-copy'); ?>
            </div>
          </li>
          <li>
            <?php echo functions::form_draw_button('unmount', language::translate('title_unmount', 'Unmount'), 'submit'); ?>
          </li>
          <li>
            <?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"', 'delete'); ?>
          </li>
        </ul>
      </fieldset>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>