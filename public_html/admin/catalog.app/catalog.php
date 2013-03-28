<?php
  if (!isset($_GET['category_id']) || $_GET['category_id'] == '') $_GET['category_id'] = 0;
  
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['categories'])) {
      foreach ($_POST['categories'] as $key => $value) $_POST['categories'][$key] = $system->database->input($value);
      $system->database->query(
        "update ". DB_TABLE_CATEGORIES ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['categories']) ."');"
      );
    }
    
    if (!empty($_POST['products'])) {
      foreach ($_POST['products'] as $key => $value) $_POST['products'][$key] = $system->database->input($value);
      $system->database->query(
        "update ". DB_TABLE_PRODUCTS ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['products']) ."');"
      );
    }
    
    header('Location: '. $system->document->link());
    exit;
  }
  
  // Move products
  if (isset($_POST['copy'])) {

    if (!empty($_POST['categories'])) $system->notices->add('errors', $system->language->translate('error_cant_copy_category', 'You can\'t copy a category'));
    if (empty($_POST['products'])) $system->notices->add('errors', $system->language->translate('error_must_select_products', 'You must select products'));
    if (isset($_POST['category_id']) && $_POST['category_id'] == '') $system->notices->add('errors', $system->language->translate('error_must_select_category', 'You must select a category'));
    
    if (empty($system->notices->data['errors'])) {
      
      foreach ($_POST['products'] as $product_id) {
        $product = new ctrl_product($product_id);
        $product->data['categories'][] = $_POST['category_id'];
        $product->save();
      }
      
      $system->notices->add('success', sprintf($system->language->translate('success_copied_d_products', 'Copied %d products'), count($_POST['products'])));
      header('Location: '. $system->document->link('', array('category_id' => $_POST['category_id']), true));
      exit;
    }
  }
  
  // Move categories or products
  if (isset($_POST['move'])) {
    
    if (empty($_POST['categories']) && empty($_POST['products'])) $system->notices->add('errors', $system->language->translate('error_must_select_category_or_product', 'You must select a category or product'));
    if (isset($_POST['category_id']) && $_POST['category_id'] == '') $system->notices->add('errors', $system->language->translate('error_must_select_category', 'You must select a category'));
    if (isset($_POST['category_id']) && isset($_POST['categories']) && in_array($_POST['category_id'], $_POST['categories'])) $system->notices->add('errors', $system->language->translate('error_cant_move_category_to_itself', 'You can\'t move a category to itself'));
    
    if (isset($_POST['category_id']) && isset($_POST['categories'])) {
      foreach ($_POST['categories'] as $category_id) {
        if (in_array($_POST['category_id'], array_keys($system->functions->catalog_category_descendants($category_id)))) {
          $system->notices->add('errors', $system->language->translate('error_cant_move_category_to_descendant', 'You can\'t move a category to a descendant'));
          break;
        }
      }
    }
    
    if (empty($system->notices->data['errors'])) {
      
      if (!empty($_POST['products'])) {
        foreach ($_POST['products'] as $product_id) {
          $product = new ctrl_product($product_id);
          $product->data['categories'] = array($_POST['category_id']);
          $product->save();
        }
        $system->notices->add('success', sprintf($system->language->translate('success_moved_d_products', 'Moved %d products'), count($_POST['products'])));
      }
      
      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category = new ctrl_category($category_id);
          $category->data['parent_id'] = $_POST['category_id'];
          $category->save();
        }
        $system->notices->add('success', sprintf($system->language->translate('success_moved_d_categories', 'Moved %d categories'), count($_POST['categories'])));
      }
      
      header('Location: '. $system->document->link('', array('category_id' => $_POST['category_id']), true));
      exit;
    }
  }
  
  // Unmount
  if (isset($_POST['unmount'])) {
    
    if (empty($_POST['categories']) && empty($_POST['products'])) $system->notices->add('errors', $system->language->translate('error_must_select_category_or_product', 'You must select a category or product'));
    if (empty($_GET['category_id'])) $system->notices->add('errors', $system->language->translate('error_no_be_nested_category', 'No nested in a category'));
    
    if (empty($system->notices->data['errors'])) {
      
      if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $category_id) {
          $category->load($category_id);
          if ($category->data['parent_id'] == $_GET['category_id']) {
            $category->data['parent_id'] = 0;
            $category->save();
          }
        }
        $system->notices->add('success', sprintf($system->language->translate('success_unmounted_d_categories', 'Unmounted %d categories'), count($_POST['categories'])));
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
        $system->notices->add('success', sprintf($system->language->translate('success_unmounted_d_products', 'Unmounted %d products'), count($_POST['products'])));
      }
      
      if (in_array($_GET['category_id'], $_POST['categories'])) unset($_GET['category_id']);
      
      header('Location: '. $system->document->link('', array(), true));
      exit;
    }
  }
  
  // Delete products
  if (isset($_POST['delete'])) {

    if (!empty($_POST['categories'])) $system->notices->add('errors', $system->language->translate('error_only_products_are_supported', 'Only products are supported for this operation'));
    if (empty($_POST['products'])) $system->notices->add('errors', $system->language->translate('error_must_select_products', 'You must select products'));
    
    if (empty($system->notices->data['errors'])) {
      foreach ($_POST['products'] as $product_id) {
        $product = new ctrl_product($product_id);
        $product->delete();
      }
      
      $system->notices->add('success', sprintf($system->language->translate('success_deleted_d_products', 'Deleted %d products'), count($_POST['products'])));
      header('Location: '. $system->document->link());
      exit;
    }
  }
?>
<div style="float: right;"><a class="button" href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc'=> 'edit_category.php', 'parent_id' => $_GET['category_id'])); ?>"><?php echo $system->language->translate('title_add_new_category', 'Add New Category'); ?></a> <a class="button" href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc'=> 'edit_product.php'), array('category_id')); ?>"><?php echo $system->language->translate('title_add_new_product', 'Add New Product'); ?></a></div>
<div style="float: right; padding-right: 10px;"><?php echo $system->functions->form_draw_input_field('query', isset($_GET['query']) ? $_GET['query'] : $system->language->translate('title_search', 'Search'), 'text', 'style="width: 175px;" onkeydown=" if (event.keyCode == 13) location=(\''. $system->document->link('', array(), true, array('page', 'query')) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_catalog', 'Catalog'); ?></h1>

<script type="text/javascript">
  $("input[name=query]").live("click", function(event) {
    if ($(this).val() == "<?php echo $system->language->translate('title_search', 'Search'); ?>") {
      $(this).val("");
    }
  });
  $("input[name=query]").live("blur", function(event) {
    if ($(this).val() == "") {
      $(this).val("<?php echo $system->language->translate('title_search', 'Search'); ?>");
    }
  });
</script>

<?php echo $system->functions->form_draw_form_begin('catalog_form', 'post'); ?>
<table class="dataTable" width="100%">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th align="left" width="100%"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th align="center"></th>
    <th>&nbsp;</th>
  </tr>
<?php
  $num_category_rows = 0;
  $num_product_rows = 0;
  
  if (!empty($_GET['query'])) {
    
    $products_query = $system->database->query(
      "select p.*, pi.name
      from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". $system->language->selected['code'] ."')
      left join ". DB_TABLE_MANUFACTURERS ." m on (p.manufacturer_id = m.id)
      left join ". DB_TABLE_DESIGNERS ." d on (p.designer_id = d.id)
      where (
        p.id = '". $system->database->input($_GET['query']) ."'
        or pi.name like '%". $system->database->input($_GET['query']) ."%'
        or p.code like '%". $system->database->input($_GET['query']) ."%'
        or pi.short_description like '%". $system->database->input($_GET['query']) ."%'
        or pi.description like '%". $system->database->input($_GET['query']) ."%'
        or m.name like '%". $system->database->input($_GET['query']) ."%'
        or d.name like '%". $system->database->input($_GET['query']) ."%'
      )
      order by pi.name asc;"
    );
    
    if ($system->database->num_rows($products_query) > 0) {
      while ($product = $system->database->fetch($products_query)) {
        $num_product_rows++;
        
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
?>
  <tr class="<?php echo $rowclass . (($product['status']) ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($product['status']) ? 'on.png' : 'off.png'); ?>" width="16" height="16" align="absbottom" /> <?php echo $system->functions->form_draw_checkbox('products['. $product['id'] .']', $product['id']); ?></td>
    <td><?php echo '<img src="'. (!empty($product['image']) ? $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png') .'" width="16" height="16" align="absbottom" />'; ?><a href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product.php', 'product_id' => $product['id'])); ?>"> <?php echo $product['name']; ?></a></td>
    <td align="right" nowrap="nowrap"></td>
    <td><a href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product.php', 'product_id' => $product['id'])); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/edit.png" width="16" height="16" alt="<?php echo $system->language->translate('title_edit', 'Edit'); ?>" align="absbottom" /></a></td>
  </tr>
<?php
      }
    }
?>
  <tr class="footer">
    <td colspan="4" align="left"><?php echo $system->language->translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
  </tr>
<?php
    
  } else {
  
    $category_trail = $system->functions->catalog_category_trail($_GET['category_id']);
    if (is_array($category_trail)) {
      $category_trail = array_keys($category_trail);
    } else {
      $category_trail = array();
    }
    
    $rowclass = 'odd';
    
    function admin_catalog_category_tree($category_id=0, $depth=1) {
      global $system, $category_trail, $rowclass, $num_category_rows;
      
      $output = '';
      
      if (empty($category_id)) {
        $rowclass = 'odd';
        $output .= '<tr class="'. $rowclass .'">' . PHP_EOL
                 . '  <td></td>' . PHP_EOL
                 . '  <td><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_opened.png" width="16" height="16" align="absbottom" /> <strong><a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc'=> $_GET['doc'], 'category_id' => '0')) .'">'. $system->language->translate('title_root', '[Root]') .'</a></strong></td>' . PHP_EOL
                 . '  <td>&nbsp;</td>' . PHP_EOL
                 . '  <td>&nbsp;</td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
      }
      
    // Output subcategories
      $categories_query = $system->database->query(
        "select c.id, c.status, ci.name
        from ". DB_TABLE_CATEGORIES ." c, ". DB_TABLE_CATEGORIES_INFO ." ci
        where c.parent_id = '". (int)$category_id ."'
        and (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
        order by c.priority asc, ci.name asc;"
      );
      
      while ($category = $system->database->fetch($categories_query)) {
        $num_category_rows++;
        
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        $output .= '<tr class="'. $rowclass . (($category['status']) ? false : ' semi-transparent') .'">' . PHP_EOL
                 . '  <td nowrap="nowrap"><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($category['status']) ? 'on.png' : 'off.png') .'" width="16" height="16" align="absbottom" /> '. $system->functions->form_draw_checkbox('categories['. $category['id'] .']', $category['id'], !empty($_POST['categories'][$category['id']]) ? $_POST['categories'][$category['id']] : '') .'</td>' . PHP_EOL;
        if (@in_array($category['id'], $category_trail)) {
          $output .= '  <td nowrap="nowrap"><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_opened.png" width="16" height="16" align="absbottom" style="margin-left: '. ($depth*16) .'px;" /> <strong><a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc'=> $_GET['doc'], 'category_id' => $category['id'])) .'">'. $category['name'] .'</a></strong></td>' . PHP_EOL;
        } else {
          $output .= '  <td nowrap="nowrap"><img src="'. WS_DIR_IMAGES .'icons/16x16/folder_closed.png" width="16" height="16" align="absbottom" style="margin-left: '. ($depth*16) .'px;" /> <a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc'=> $_GET['doc'], 'category_id' => $category['id'])) .'">'. $category['name'] .'</a></td>' . PHP_EOL;
        }
        $output .= '  <td>&nbsp;</td>' . PHP_EOL
                 . '  <td><a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_category.php', 'category_id' => $category['id'])) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/edit.png" width="16" height="16" align="absbottom" /></a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
        
        if (in_array($category['id'], $category_trail)) {
        
          if ($system->database->num_rows($system->database->query("select id from ". DB_TABLE_CATEGORIES ." where parent_id = '". (int)$category['id'] ."' limit 1;")) > 0
           || $system->database->num_rows($system->database->query("select id from ". DB_TABLE_PRODUCTS ." where find_in_set ('". (int)$category['id'] ."', categories) limit 1;")) > 0) {
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
                     . '  <td><em style="margin-left: '. (($depth+1)*16) .'px;">'. $system->language->translate('title_empty', 'Empty') .'</em></td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '  <td>&nbsp;</td>' . PHP_EOL
                     . '</tr>' . PHP_EOL;
          }
        }
      }
      
      $system->database->free($categories_query);
      
      // Output products
      if (empty($category_id)) {
        $output .= admin_catalog_category_products($category_id, $depth);
      }
      
      return $output;
    }
    
    function admin_catalog_category_products($category_id=0, $depth=1) {
      global $system, $rowclass, $num_product_rows;
      
      $output = '';
      
      $products_query = $system->database->query(
        "select p.id, p.status, p.image, pi.name from ". DB_TABLE_PRODUCTS ." p, ". DB_TABLE_PRODUCTS_INFO ." pi
        where (find_in_set('". (int)$category_id ."', p.categories)
        ". (($category_id == 0) ? "or p.categories = ''" : false) .")
        and (pi.product_id = p.id and pi.language_code = '". $system->language->selected['code'] ."')
        order by pi.name asc;"
      );
      
      $display_images = true;
      if ($system->database->num_rows($products_query) > 100) {
        $display_images = false;
      }
      
      while ($product=$system->database->fetch($products_query)) {
        $num_product_rows++;
        
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        
        $output .= '<tr class="'. $rowclass . (($product['status']) ? false : ' semi-transparent') .'">' . PHP_EOL
                 . '  <td nowrap="nowrap"><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($product['status']) ? 'on.png' : 'off.png') .'" width="16" height="16" align="absbottom" /> '. $system->functions->form_draw_checkbox('products['. $product['id'] .']', $product['id'], !empty($_POST['products'][$product['id']]) ? $_POST['products'][$product['id']] : '') .'</td>' . PHP_EOL;
        
        if ($display_images) {
          $output .= '  <td align="left"><img src="'. (!empty($product['image']) ? $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png') .'" width="16" height="16" align="absbottom" style="margin-left: '. ($depth*16) .'px;" /> <a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product.php', 'category_id' => $category_id, 'product_id' => $product['id'])) .'">'. $product['name'] .'</a></td>' . PHP_EOL;
        } else {
          $output .= '  <td align="left"><span style="margin-left: '. (($depth+1)*16) .'px;">&nbsp;<a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product.php', 'category_id' => $category_id, 'product_id' => $product['id'])) .'">'. $product['name'] .'</a></span></td>' . PHP_EOL;
        }
        
        $output .= '  <td align="right" nowrap="nowrap"></td>' . PHP_EOL
                 . '  <td><a href="'. $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_product.php', 'category_id' => $category_id, 'product_id' => $product['id'])) .'"><img src="'. WS_DIR_IMAGES .'icons/16x16/edit.png" width="16" height="16" align="absbottom" /></a></td>' . PHP_EOL
                 . '</tr>' . PHP_EOL;
      }
      $system->database->free($products_query);
        
      return $output;
    }

    echo admin_catalog_category_tree();
?>
  <tr class="footer">
    <td colspan="4" align="left"><?php echo $system->language->translate('title_categories', 'Categories'); ?>: <?php echo $num_category_rows; ?> | <?php echo $system->language->translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
  </tr>
<?php
  }
?>
</table>

<script type="text/javascript">
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
  <ul class="navigation-horizontal">
    <li><?php echo $system->language->translate('text_with_selected', 'With selected'); ?>:</li>
    <li><?php echo $system->functions->form_draw_button('enable', $system->language->translate('title_enable', 'Enable'), 'submit'); ?> <?php echo $system->functions->form_draw_button('disable', $system->language->translate('title_disable', 'Disable'), 'submit'); ?></li>
    <li><?php echo $system->functions->form_draw_categories_list('category_id', isset($_POST['category_id']) ? $_POST['category_id'] : '', 'style="width: 100px;"'); ?> <?php echo $system->functions->form_draw_button('move', $system->language->translate('title_move', 'Move'), 'submit', 'onclick="if (!confirm(\''. str_replace("'", "\\\'", $system->language->translate('warning_multiple_references_will_be_lost', 'Warning: Multiple references will be lost.')) .'\')) return false;"'); ?> <?php echo $system->functions->form_draw_button('copy', $system->language->translate('title_copy', 'Copy'), 'submit'); ?></li>
    <li><?php echo $system->functions->form_draw_button('unmount', $system->language->translate('title_unmount', 'Unmount'), 'submit'); ?></li>
    <li><?php echo $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. str_replace("'", "\\\'", $system->language->translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"'); ?></li>
  </ul>
</p>

<?php echo $system->functions->form_draw_form_end(); ?>