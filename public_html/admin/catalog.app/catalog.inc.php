<?php
  if (!isset($_GET['category_id']) || $_GET['category_id'] == '') $_GET['category_id'] = 0;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['categories'])) {
      foreach ($_POST['categories'] as $category_id) {
        $category = new ctrl_category($category_id);
        $category->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $category->save();
      }
    }
    
    if (!empty($_POST['products'])) {
      foreach ($_POST['products'] as $product_id) {
        $product = new ctrl_product($product_id);
        $product->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $product->save();
      }
    }
    
    header('Location: '. document::link());
    exit;
  }
  
// Duplicate products
  if (isset($_POST['duplicate'])) {

    if (!empty($_POST['categories'])) notices::add('errors', language::translate('error_cant_duplicate_category', 'You can\'t duplicate a category'));
    if (empty($_POST['products'])) notices::add('errors', language::translate('error_must_select_products', 'You must select products'));
    if (isset($_POST['category_id']) && $_POST['category_id'] == '') notices::add('errors', language::translate('error_must_select_category', 'You must select a category'));
    
    if (empty(notices::$data['errors'])) {
      
      foreach ($_POST['products'] as $product_id) {
        $old_product = new ctrl_product($product_id);
        $product = new ctrl_product();
        
        $product->data = $old_product->data;
        
        $product->data['id'] = null;
        $product->data['categories'] = array($_POST['category_id']);
        $product->data['image'] = null;
        $product->data['images'] = array();
        
        foreach (array('campaigns', 'options', 'options_stock') as $field) {
          if (empty($product->data[$field])) continue;
          foreach (array_keys($product->data[$field]) as $key) {
            $product->data[$field][$key]['id'] = null;
          }
        }
        
        if (!empty($old_product->data['images'])) {
          foreach ($old_product->data['images'] as $image) {
            $product->add_image(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image['filename']);
          }
        }
        
        foreach (array_keys($product->data['name']) as $language_code) {
          $product->data['name'][$language_code] .= ' (copy)';
        }
        
        $product->save();
      }
      
      notices::add('success', sprintf(language::translate('success_duplicated_d_products', 'Duplicated %d products'), count($_POST['products'])));
      header('Location: '. document::link('', array('category_id' => $_POST['category_id']), true));
      exit;
    }
  }
  
// Copy products
  if (isset($_POST['copy'])) {

    if (!empty($_POST['categories'])) notices::add('errors', language::translate('error_cant_copy_category', 'You can\'t copy a category'));
    if (empty($_POST['products'])) notices::add('errors', language::translate('error_must_select_products', 'You must select products'));
    if (isset($_POST['category_id']) && $_POST['category_id'] == '') notices::add('errors', language::translate('error_must_select_category', 'You must select a category'));
    
    if (empty(notices::$data['errors'])) {
      
      foreach ($_POST['products'] as $product_id) {
        $product = new ctrl_product($product_id);
        $product->data['categories'][] = $_POST['category_id'];
        $product->save();
      }
      
      notices::add('success', sprintf(language::translate('success_copied_d_products', 'Copied %d products'), count($_POST['products'])));
      header('Location: '. document::link('', array('category_id' => $_POST['category_id']), true));
      exit;
    }
  }
  
  // Move categories or products
  if (isset($_POST['move'])) {
    
    if (empty($_POST['categories']) && empty($_POST['products'])) notices::add('errors', language::translate('error_must_select_category_or_product', 'You must select a category or product'));
    if (isset($_POST['category_id']) && $_POST['category_id'] == '') notices::add('errors', language::translate('error_must_select_category', 'You must select a category'));
    if (isset($_POST['category_id']) && isset($_POST['categories']) && in_array($_POST['category_id'], $_POST['categories'])) notices::add('errors', language::translate('error_cant_move_category_to_itself', 'You can\'t move a category to itself'));
    
    if (isset($_POST['category_id']) && isset($_POST['categories'])) {
      foreach ($_POST['categories'] as $category_id) {
        if (in_array($_POST['category_id'], array_keys(functions::catalog_category_descendants($category_id)))) {
          notices::add('errors', language::translate('error_cant_move_category_to_descendant', 'You can\'t move a category to a descendant'));
          break;
        }
      }
    }
    
    if (empty(notices::$data['errors'])) {
      
      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ctrl_product($product_id);
          $product->data['categories'] = array($_POST['category_id']);
          $product->save();
        }
        notices::add('success', sprintf(language::translate('success_moved_d_products', 'Moved %d products'), count($_POST['products'])));
      }
      
      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ctrl_category($category_id);
          $category->data['parent_id'] = $_POST['category_id'];
          $category->save();
        }
        notices::add('success', sprintf(language::translate('success_moved_d_categories', 'Moved %d categories'), count($_POST['categories'])));
      }
      
      header('Location: '. document::link('', array('category_id' => $_POST['category_id']), true));
      exit;
    }
  }
  
  // Unmount
  if (isset($_POST['unmount'])) {
    
    if (empty($_POST['categories']) && empty($_POST['products'])) notices::add('errors', language::translate('error_must_select_category_or_product', 'You must select a category or product'));
    if (empty($_GET['category_id'])) notices::add('errors', language::translate('error_category_must_be_nested_in_another_category_to_unmount', 'A category must be nested in another category to be unmounted'));
    
    if (empty(notices::$data['errors'])) {
      
      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ctrl_category($category_id);
          if ($category->data['parent_id'] == $_GET['category_id']) {
            $category->data['parent_id'] = 0;
            $category->save();
          }
        }
        notices::add('success', sprintf(language::translate('success_unmounted_d_categories', 'Unmounted %d categories'), count($_POST['categories'])));
      }
      
      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ctrl_product($product_id);
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
      
      header('Location: '. document::link('', array(), true));
      exit;
    }
  }
  
  // Delete products
  if (isset($_POST['delete'])) {

    if (!empty($_POST['categories'])) notices::add('errors', language::translate('error_only_products_are_supported', 'Only products are supported for this operation'));
    if (empty($_POST['products'])) notices::add('errors', language::translate('error_must_select_products', 'You must select products'));
    
    if (empty(notices::$data['errors'])) {
      foreach ($_POST['products'] as $product_id) {
        $product = new ctrl_product($product_id);
        $product->delete();
      }
      
      notices::add('success', sprintf(language::translate('success_deleted_d_products', 'Deleted %d products'), count($_POST['products'])));
      header('Location: '. document::link());
      exit;
    }
  }
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('app' => $_GET['app'], 'doc'=> 'edit_category', 'parent_id' => $_GET['category_id'])), language::translate('title_add_new_category', 'Add New Category'), '', 'add'); ?> <?php echo functions::form_draw_link_button(document::link('', array('app' => $_GET['app'], 'doc'=> 'edit_product'), array('category_id')), language::translate('title_add_new_product', 'Add New Product'), '', 'add'); ?></div>
<div style="float: right; padding-right: 10px;"><?php echo functions::form_draw_form_begin('search_form', 'get', '', false, 'onsubmit="return false;"') . functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::link('', array(), true, array('page', 'query')) .'&query=\' + encodeURIComponent(this.value))"') . functions::form_draw_form_end(); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_catalog', 'Catalog'); ?></h1>

<?php echo functions::form_draw_form_begin('catalog_form', 'post'); ?>

  <table class="dataTable" width="100%">
    <tr class="header">
      <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
      <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th style="text-align: center;"></th>
      <th>&nbsp;</th>
    </tr>
<?php
  $num_category_rows = 0;
  $num_product_rows = 0;
  
  if (!empty($_GET['query'])) {
    
    $products_query = database::query(
      "select p.*, pi.name
      from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')
      left join ". DB_TABLE_MANUFACTURERS ." m on (p.manufacturer_id = m.id)
      left join ". DB_TABLE_SUPPLIERS ." s on (p.supplier_id = s.id)
      where (
        p.id = '". database::input($_GET['query']) ."'
        or pi.name like '%". database::input($_GET['query']) ."%'
        or p.code like '%". database::input($_GET['query']) ."%'
        or p.sku like '%". database::input($_GET['query']) ."%'
        or p.upc like '%". database::input($_GET['query']) ."%'
        or pi.short_description like '%". database::input($_GET['query']) ."%'
        or pi.description like '%". database::input($_GET['query']) ."%'
        or m.name like '%". database::input($_GET['query']) ."%'
        or s.name like '%". database::input($_GET['query']) ."%'
      )
      order by pi.name asc;"
    );
    
    if (database::num_rows($products_query) > 0) {
      while ($product = database::fetch($products_query)) {
        $num_product_rows++;
        
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
?>
    <tr class="<?php echo $rowclass . (($product['status']) ? false : ' semi-transparent'); ?>">
      <td><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($product['status']) ? 'on.png' : 'off.png'); ?>" width="16" height="16" align="absbottom" /> <?php echo functions::form_draw_checkbox('products['. $product['id'] .']', $product['id']); ?></td>
      <td><?php echo '<img src="'. (!empty($product['image']) ? functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png') .'" width="16" height="16" align="absbottom" />'; ?><a href="<?php echo document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product', 'product_id' => $product['id'])); ?>"> <?php echo $product['name']; ?></a></td>
      <td style="text-align: right;"></td>
      <td><a href="<?php echo document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product', 'product_id' => $product['id'])); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/edit.png" width="16" height="16" alt="<?php echo language::translate('title_edit', 'Edit'); ?>" align="absbottom" /></a></td>
    </tr>
<?php
      }
    }
?>
    <tr class="footer">
      <td colspan="4"><?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
    </tr>
<?php
    
  } else {
  
    $category_trail = functions::catalog_category_trail($_GET['category_id']);
    if (!empty($category_trail)) {
      $category_trail = array_keys($category_trail);
    } else {
      $category_trail = array();
    }
    
    $rowclass = 'odd';
    
    function admin_catalog_category_tree($category_id=0, $depth=1) {
      global $category_trail, $rowclass, $num_category_rows;
      
      $output = '';
      
      if (empty($category_id)) {
        $rowclass = 'odd';
        $output .= '<tr class="'. $rowclass .'">' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '  <td><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_opened.png" width="16" height="16" align="absbottom" /> <strong><a href="'. document::href_link('', array('category_id' => '0'), true) .'">'. language::translate('title_root', '[Root]') .'</a></strong></td>' . PHP_EOL
                 . '  <td>&nbsp;</td>' . PHP_EOL
                 . '  <td>&nbsp;</td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
      }
      
    // Output subcategories
      $categories_query = database::query(
        "select c.id, c.status, ci.name
        from ". DB_TABLE_CATEGORIES ." c
        left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
        where c.parent_id = '". (int)$category_id ."'
        order by c.priority asc, ci.name asc;"
      );
      
      while ($category = database::fetch($categories_query)) {
        $num_category_rows++;
        
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        $output .= '<tr class="'. $rowclass . (($category['status']) ? false : ' semi-transparent') .'">' . PHP_EOL
                 . '  <td><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($category['status']) ? 'on.png' : 'off.png') .'" width="16" height="16" align="absbottom" /> '. functions::form_draw_checkbox('categories['. $category['id'] .']', $category['id'], true) .'</td>' . PHP_EOL;
        if (@in_array($category['id'], $category_trail)) {
          $output .= '  <td><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_opened.png" width="16" height="16" align="absbottom" style="margin-left: '. ($depth*16) .'px;" /> <strong><a href="'. document::href_link('', array('category_id' => $category['id']), true) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></strong></td>' . PHP_EOL;
        } else {
          $output .= '  <td><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_closed.png" width="16" height="16" align="absbottom" style="margin-left: '. ($depth*16) .'px;" /> <a href="'. document::href_link('', array('category_id' => $category['id']), true) .'">'. ($category['name'] ? $category['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        }
        $output .= '  <td>&nbsp;</td>' . PHP_EOL
                 . '  <td><a href="'. document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_category', 'category_id' => $category['id'])) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/edit.png" width="16" height="16" align="absbottom" /></a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
        
        if (in_array($category['id'], $category_trail)) {
          if (database::num_rows(database::query("select id from ". DB_TABLE_CATEGORIES ." where parent_id = '". (int)$category['id'] ."' limit 1;")) > 0
           || database::fetch(database::query("select exists (select 1 from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."  where category_id =".(int)$category['id'].")"))[0]) {
            $output .= admin_catalog_category_tree($category['id'], $depth+1);
            
            // Output products
            if (in_array($category['id'], $category_trail)) {
              $output .= admin_catalog_category_products($category['id'], $depth+1);
            }
            
          } else {
          
            if (!isset($rowclass) || $rowclass == 'even') {
              $rowclass = 'odd';
            } else {
              $rowclass = 'even';
            }
            $output .= '<tr class="'. $rowclass .'">' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td><em style="margin-left: '. (($depth+1)*16) .'px;">'. language::translate('title_empty', 'Empty') .'</em></td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '</tr>' . PHP_EOL;
          }
        }
      }
      
      database::free($categories_query);
      
      // Output products
      if (empty($category_id)) {
        $output .= admin_catalog_category_products($category_id, $depth);
      }
      
      return $output;
    }
    
    function admin_catalog_category_products($category_id=0, $depth=1) {
      global $rowclass, $num_product_rows;
      
      $output = '';

      $products_query = database::query(
        "select c.* , p.id, p.status, p.image, pi.name from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ." c
        left join  ". DB_TABLE_PRODUCTS ." p ON ( p.id = c.product_id)
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". language::$selected['code'] ."')
        where c.category_id=". (int)$category_id ."
        group by(p.id)
        order by pi.name asc;"
      );

      $display_images = true;
      if (database::num_rows($products_query) > 100) {
        $display_images = false;
      }
      
      while ($product=database::fetch($products_query)) {
        $num_product_rows++;
        
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        
        $output .= '<tr class="'. $rowclass . (($product['status']) ? false : ' semi-transparent') .'">' . PHP_EOL
                 . '  <td><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($product['status']) ? 'on.png' : 'off.png') .'" width="16" height="16" align="absbottom" /> '. functions::form_draw_checkbox('products['. $product['id'] .']', $product['id'], true) .'</td>' . PHP_EOL;
        
        if ($display_images) {
          $output .= '  <td><img src="'. (!empty($product['image']) ? functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png') .'" width="16" height="16" align="absbottom" style="margin-left: '. ($depth*16) .'px;" /> <a href="'. document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product', 'category_id' => $category_id, 'product_id' => $product['id'])) .'">'. ($product['name'] ? $product['name'] : '[untitled]') .'</a></td>' . PHP_EOL;
        } else {
          $output .= '  <td><span style="margin-left: '. (($depth+1)*16) .'px;">&nbsp;<a href="'. document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product', 'category_id' => $category_id, 'product_id' => $product['id'])) .'">'. $product['name'] .'</a></span></td>' . PHP_EOL;
        }
        
        $output .= '  <td style="text-align: right;"></td>' . PHP_EOL
                 . '  <td><a href="'. document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product', 'category_id' => $category_id, 'product_id' => $product['id'])) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/edit.png" width="16" height="16" align="absbottom" /></a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
      }
      database::free($products_query);
        
      return $output;
    }

    echo admin_catalog_category_tree();
?>
    <tr class="footer">
      <td colspan="4"><?php echo language::translate('title_categories', 'Categories'); ?>: <?php echo $num_category_rows; ?> | <?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
    </tr>
<?php
  }
?>
  </table>

  <script>
    $(".dataTable input[name='checkbox_toggle']").click(function() {
      $(this).closest("form").find(":checkbox").each(function() {
        $(this).attr('checked', !$(this).attr('checked'));
      });
      $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
    });

    $('.dataTable tr').click(function(event) {
      if ($(event.target).is('input:checkbox')) return;
      if ($(event.target).is('a, a *')) return;
      if ($(event.target).is('th')) return;
      $(this).find('input:checkbox').trigger('click');
    });
  </script>

<p>
  <ul class="list-horizontal">
    <li><?php echo language::translate('text_with_selected', 'With selected'); ?>:</li>
    <li><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span></li>
    <li><?php echo functions::form_draw_categories_list('category_id', isset($_POST['category_id']) ? $_POST['category_id'] : ''); ?> <span class="button-set"><?php echo functions::form_draw_button('move', language::translate('title_move', 'Move'), 'submit', 'onclick="if (!confirm(\''. str_replace("'", "\\\'", language::translate('warning_mounting_points_will_be_replaced', 'Warning: All current mounting points will be replaced.')) .'\')) return false;"'); ?><?php echo functions::form_draw_button('copy', language::translate('title_copy', 'Copy'), 'submit'); ?><?php echo functions::form_draw_button('duplicate', language::translate('title_duplicate', 'Duplicate'), 'submit'); ?></span></li>
    <span class="button-set"><?php echo functions::form_draw_button('unmount', language::translate('title_unmount', 'Unmount'), 'submit'); ?><?php echo functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?></span></li>
  </ul>
</p>

<?php echo functions::form_draw_form_end(); ?>