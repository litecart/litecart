<?php
  require_once('includes/app_header.inc.php');
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
  $product = new ref_product($_GET['product_id']);
  
  if ($product->status == 0) {
    $system->notices->add('errors', $system->language->translate('error_page_not_found', 'The requested page could not be found'));
    header('Location: HTTP/1.1 301 Moved Permanently');
    header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
    exit;
  }
  
  $system->database->query(
    "update ". DB_TABLE_PRODUCTS ."
    set views = views + 1
    where id = '". (int)$_GET['product_id'] ."'
    limit 1;"
  );
  
  $system->document->snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars($system->document->link('product.php', array('product_id' => $_GET['product_id']))) .'" />';
  
  $system->document->snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';
  
  $system->functions->draw_fancybox('a.fancybox');
  
  $system->document->snippets['head_tags']['animate_from_to'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'jquery/jquery.animate_from_to-1.0.min.js"></script>';
  $system->document->snippets['javascript'][] =  '  $(document).ready(function(){' . PHP_EOL
                                               . '    $("button[name=\'add_cart_product\']").click(function(){' . PHP_EOL
                                               . '      $("button[name=\'add_cart_product\']").animate_from_to("#cart", {' . PHP_EOL
                                               . '          initial_css: {' . PHP_EOL
                                               . '            "border": "1px rgba(0,0,200,1) solid",' . PHP_EOL
                                               . '            "background-color": "rgba(0,0,200,0.5)",' . PHP_EOL
                                               . '          },' . PHP_EOL
                                               . '          callback: function(){' . PHP_EOL
                                               . '            // done, do something' . PHP_EOL
                                               . '          }' . PHP_EOL
                                               . '      });' . PHP_EOL
                                               . '    });' . PHP_EOL
                                               . '  });';
  
  if (empty($_GET['category_id']) && empty($product->manufacturer)) {
    if (count($product->category_ids)) $_GET['category_id'] = array_shift(array_values($product->category_ids));
  }
  
  if (!empty($_GET['category_id'])) {
    $system->breadcrumbs->add($system->language->translate('title_categories', 'Categories'), $system->document->link('categories.php'));
    foreach ($system->functions->catalog_category_trail($_GET['category_id']) as $category_id => $category_name) {
      $system->document->snippets['title'][] = $category_name;
      $system->breadcrumbs->add($category_name, $system->document->link('category.php', array('category_id' => $category_id)));
    }
  } else if (!empty($product->manufacturer)) {
    $system->document->snippets['title'][] = $product->manufacturer['id'];
    $system->breadcrumbs->add($system->language->translate('title_manufacturers', 'Manufacturers'), $system->document->link('manufacturers.php'));
    $system->breadcrumbs->add($system->functions->reference_get_manufacturer_name($product->manufacturer['id']), $system->document->link('manufacturer.php', array('manufacturer_id' => $product->manufacturer['id'])));
  }
  
  $system->document->snippets['title'][] = $product->head_title[$system->language->selected['code']] ? $product->head_title[$system->language->selected['code']] : $product->name[$system->language->selected['code']];
  $system->document->snippets['keywords'] = $product->meta_keywords[$system->language->selected['code']] ? $product->meta_keywords[$system->language->selected['code']] : $product->keywords;
  $system->document->snippets['description'] = $product->meta_description[$system->language->selected['code']] ? $product->meta_description[$system->language->selected['code']] : $product->short_description[$system->language->selected['code']];
  
  $system->breadcrumbs->add($product->name[$system->language->selected['code']], $system->document->link('', array('product_id' => $product->id), array('category_id')));
?>

<?php
  ob_start();
  echo '<div id="sidebar" class="shadow rounded-corners">' . PHP_EOL;
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'search.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'category_tree.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'manufacturers.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'account.inc.php');
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'login.inc.php');
  echo '</div>' . PHP_EOL;
  $system->document->snippets['column_left'] = ob_get_clean();
?>

<div class="box" id="box-product">
  <div class="heading">
    <?php echo (!empty($product->code)) ? '<div style="float: right; color: #999;">'. $product->code .'</div>' : false; ?>
    <h1><?php echo $product->name[$system->language->selected['code']]; ?></h1>
  </div>
  <div class="content">
    <table>
      <tr>
        <td style="width: 320px">
          <div class="product-images-wrapper">
<?php
  $first_image = true;
  foreach ($product->images as $image) {
    if ($first_image) {
    
      if (empty($product->campaigns) == false) {
        $sticker = '<img src="{snippet:template_path}images/icons/16x16/sticker_campaign_'. $system->language->selected['code'] .'.png" width="96" height="86" border="0" title="'. $system->language->translate('title_on_sale', 'On Sale') .'" style="position: absolute; top: 10px; left: '. ($product['date_created'] > date('Y-m-d', strtotime('-1 month')) ? '30px' : '10px') .';" class="" />';
      } else if ($product->date_created > date('Y-m-d', strtotime('-1 month'))) {
        $sticker = '<img src="{snippet:template_path}images/sticker_new_'. $system->language->selected['code'] .'.png" width="96" height="86" border="0" title="'. $system->language->translate('title_new', 'New') .'" style="position: absolute; top: 0; left: 0;" class="" />';
      } else {
        $sticker = '';
      }
      
      echo '<div style="position: relative;">' . PHP_EOL
         . '  <a href="'. WS_DIR_IMAGES . $image .'" class="fancybox" rel="product"><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image, FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 310, 0, 'FIT') .'" border="0" class="productImage zoomable shadow" title="'. htmlspecialchars($product->name[$system->language->selected['code']]) .'" /></a>' . PHP_EOL
         . '  '. $sticker . PHP_EOL
         . '</div>' . PHP_EOL;
      $first_image = false;
    } else {
      echo '<div style="display: inline;"><a href="'. WS_DIR_IMAGES . $image .'" class="fancybox" rel="product"><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image, FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 100, 133, 'CROP') .'" border="0" style="margin: 5px 5px 0px 0px;" class="productImage-extraImage zoomable shadow" title="'. htmlspecialchars($product->name[$system->language->selected['code']]) .'" /></a></div>';
    }
  }
?>
          </div>
        </td>
        
        <td style="padding-left: 10px;">
          <div class="tabs">
            <div class="index">
              <li><a href="#tab-information"><?php echo $system->language->translate('title_information', 'Information'); ?></a></li>
              <?php if (!empty($product->attributes[$system->language->selected['code']])) { ?><li><a href="#tab-details"><?php echo $system->language->translate('title_details', 'Details'); ?></a></li><?php } ?>
            </div>
            
            <div class="content">
              <div class="tab" id="tab-information">
                <p><?php echo $product->description[$system->language->selected['code']] ? $product->description[$system->language->selected['code']] : '<em style="color: #999;">'. $system->language->translate('text_no_product_description', 'There is no description for this product yet.') .'</em>'; ?></p>
              </div>
              
              <?php if (!empty($product->attributes[$system->language->selected['code']])) { ?>
              <div class="tab" id="tab-details">
                <table cellspacing="0" cellpadding="5" border="0">
<?php
  $attributes = explode(PHP_EOL, $product->attributes[$system->language->selected['code']]);
  for ($i=0; $i<count($attributes); $i++) {
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
    if (strpos($attributes[$i], ':') !== false) {
      list($key, $value) = explode(':', $attributes[$i]);
      echo '<tr class="'. $rowclass .'">' . PHP_EOL
         . '  <td nowrap="nowrap">'. trim($key) .':</td>' . PHP_EOL
         . '  <td width="100%">'. trim($value) .'</td>' . PHP_EOL
         . '</tr>' . PHP_EOL;
    } else {
      echo '<tr class="'. $rowclass .' header">' . PHP_EOL
         . '  <th colspan="2" class="header"><strong>'. $attributes[$i] .'</strong></th>' . PHP_EOL
         . '</tr>' . PHP_EOL;
    }
  }
?>
                </table>
              </div>
              <?php } ?>
            </div>
          </div>
      
<?php
    if ($product->manufacturer_id) {
?>
          <div style="margin-bottom: 10px; color: #999;" class="manufacturer">
<?php
      if ($product->manufacturer['image']) {
        echo '<a href="'. $system->document->link('manufacturer.php', array('manufacturer_id' => $product->manufacturer_id)) .'"><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 0, 60) .'" height="60" border="0" alt="'. $product->manufacturer['name'] .'" title="'. $product->manufacturer['name'] .'" /></a>';
      } else {
        echo '<a href="'. $system->document->link('manufacturer.php', array('manufacturer_id' => $product->manufacturer_id)) .'">'. $product->manufacturer['name'] .'</a>';
      }
?>
          </div>
<?php
    }
?>
      
          <div style="margin-bottom: 10px; font-size: 22px;" class="price"><?php echo $product->campaign['price'] ? '<s class="old-price">'. $system->currency->format($system->tax->calculate($product->price, $product->tax_class_id)) .'</s> <strong style="color: #cc0000; color: #c00;" class="special-price">'. $system->currency->format($system->tax->calculate($product->campaign['price'], $product->tax_class_id)) .'</strong>' : '<span style="color: #cc0000; color: #c00;" class="price">'. $system->currency->format($system->tax->calculate($product->price, $product->tax_class_id)); ?></div>
          
          <div style="margin-bottom: 10px; color: #999;" class="tax">
<?php
    if ($system->settings->get('display_prices_including_tax') == 'true') {
      echo $system->language->translate('title_including_tax', 'Including Tax') .':<br/>' . PHP_EOL;
    } else {
      echo $system->language->translate('title_excluding_tax', 'Excluding Tax') .':<br/>' . PHP_EOL;
    }
    
    if ($tax_rates = $system->tax->get_tax_by_rate($product->campaign['price'] ? $product->campaign['price'] : $product->price, $product->tax_class_id)) {
      $use_br = false;
      foreach ($tax_rates as $tax_rate) {
        echo $system->currency->format($tax_rate['tax']) .' ('. $tax_rate['name'] .')<br/>' . PHP_EOL;
      }
      
    } else {
      echo $system->language->translate('text_duty_free_or_no_country_zone_set', 'Duty free or no country/zone set.');
    }
    
?>
          </div>
          
          <div style="margin-bottom: 10px;" class="quantity">
<?php
  if ($product->quantity > 0) {
    echo $system->language->translate('title_stock_status', 'Stock Status') .': <span style="color: #080;">'. (($system->settings->get('display_stock_count') == 'true') ? sprintf($system->language->translate('text_d_pieces', '%d pieces'), $product->quantity) : $system->language->translate('title_in_stock', 'In Stock')) .'</span>';
    if (!empty($product->delivery_status['name'][$system->language->selected['code']])) echo '<br />' . $system->language->translate('title_delivery_status', 'Delivery Status') .': '. $product->delivery_status['name'][$system->language->selected['code']];
  } else {
    if (!empty($product->sold_out_status['name'][$system->language->selected['code']])) {
      echo $system->language->translate('title_stock_status', 'Stock Status') .': <span style="color: '. ($product->sold_out_status['orderable'] ? '#f90' : '#c00') .';">'. $product->sold_out_status['name'][$system->language->selected['code']] .'</span>';
    } else {
      echo $system->language->translate('title_stock_status', 'Stock Status') .': <span style="color: #c00;">'. $system->language->translate('title_sold_out', 'Sold Out') .'</span>';
    }
  }
?>
          </div>
      
<?php
    if ($system->settings->get('display_cheapest_shipping') == 'true') {
?>
          <div style="margin-bottom: 10px;" class="cheapest-shipping">
<?php
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'shipping.inc.php');
    $shipping = new shipping('local');
    $shipping->items[$product->id] = array(
      'quantity' => 1,
      'price' => $product->campaign['price'] ? $product->campaign['price'] : $product->price,
      'tax_class_id' => $product->tax_class_id,
      'weight' => $product->weight,
      'weight_class' => $product->weight_class,
      'dim_x' => $product->dim_x,
      'dim_x' => $product->dim_x,
      'dim_y' => $product->dim_y,
      'dim_z' => $product->dim_z,
      'dim_class' => $product->dim_class,
    );
    $shipping->destination = $system->customer->data;
    $cheapest_shipping = explode(':', $shipping->cheapest());
    if (!empty($cheapest_shipping)) {
      $shipping_cost = $shipping->data['options'][$cheapest_shipping[0]]['options'][$cheapest_shipping[1]]['cost'];
      $shipping_tax_class_id = $shipping->data['options'][$cheapest_shipping[0]]['options'][$cheapest_shipping[1]]['tax_class_id'];
      echo str_replace(
             '%price',
             $system->currency->format($system->tax->calculate($shipping_cost, $shipping_tax_class_id)),
             $system->language->translate('text_cheapest_shipping_from_price', 'Cheapest shipping from %price')
           );
    }
?>
          </div>
<?php
  }
?>
      
          <div style="margin-bottom: 10px;" class="buy_now">
            <?php echo $system->functions->form_draw_form_begin('buy_now_form'); ?>
            <?php echo $system->functions->form_draw_hidden_field('product_id', $product->id); ?>
            
            <div class="options">
<?php
  if (count($product->options) > 0) {
  
    foreach ($product->options as $group) {
    
      echo '  <p><strong>'. $group['name'][$system->language->selected['code']] .'</strong>'. (empty($group['required']) == false ? ' ('. $system->language->translate('title_required', 'Required') .')' : '') .'<br />'
         . (!empty($group['description'][$system->language->selected['code']]) ? $group['description'][$system->language->selected['code']] . '<br />' . PHP_EOL : '');
      
      switch ($group['function']) {
      
        case 'checkbox':
          $use_br = false;
          
          foreach (array_keys($group['values']) as $value_id) {
            if ($use_br) echo '<br />';
            
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = $system->currency->format($system->tax->calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }
            
            echo $system->functions->form_draw_checkbox('options['.$group['name'][$system->language->selected['code']].'][]', $group['values'][$value_id]['name'][$system->language->selected['code']], isset($_POST['options'][$group['name'][$system->language->selected['code']]]) ? $_POST['options'][$group['name'][$system->language->selected['code']]] : '') .' '. $group['values'][$value_id]['name'][$system->language->selected['code']] . $price_adjust_text . PHP_EOL;
            $use_br = true;
          }
          break;
          
        case 'input':
        
          $value_id = array_shift(array_keys($group['values']));
        
          $price_adjust_text = '';
          if ($group['values'][$value_id]['price_adjust']) {
            $price_adjust_text = $system->currency->format($system->tax->calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
            if ($group['values'][$value_id]['price_adjust'] > 0) {
              $price_adjust_text = ' +'.$price_adjust_text;
            }
          }
          
          echo $system->functions->form_draw_input_field('options['.$group['name'][$system->language->selected['code']].']', isset($_POST['options'][$group['name'][$system->language->selected['code']]]) ? $_POST['options'][$group['name'][$system->language->selected['code']]] : '', 'input') . $price_adjust_text . PHP_EOL;
          break;
          
        case 'radio':
        
          $use_br = false;
          foreach (array_keys($group['values']) as $value_id) {
            if ($use_br) echo '<br />';
            
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = $system->currency->format($system->tax->calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }
            
            echo $system->functions->form_draw_radio_button('options['.$group['name'][$system->language->selected['code']].']', $group['values'][$value_id]['name'][$system->language->selected['code']], isset($_POST['options'][$group['name'][$system->language->selected['code']]]) ? $_POST['options'][$group['name'][$system->language->selected['code']]] : '') .' '. $group['values'][$value_id]['name'][$system->language->selected['code']] . $price_adjust_text . PHP_EOL;
            $use_br = true;
          }
          break;
          
        case 'select':
          
          $options = array(array('-- '. $system->language->translate('title_select', 'Select') .' --', ''));
          foreach (array_keys($group['values']) as $value_id) {
          
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = $system->currency->format($system->tax->calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }

            $options[] = array($group['values'][$value_id]['name'][$system->language->selected['code']] . $price_adjust_text, $group['values'][$value_id]['name'][$system->language->selected['code']]);
          }
          echo $system->functions->form_draw_select_field('options['.$group['name'][$system->language->selected['code']].']', $options, isset($_POST['options'][$group['name'][$system->language->selected['code']]]) ? $_POST['options'][$group['name'][$system->language->selected['code']]] : '');
          break;
          
        case 'textarea':
          
          $price_adjust_text = '';
          if ($group['values'][$value_id]['price_adjust']) {
            $price_adjust_text = $system->currency->format($system->tax->calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
            if ($group['values'][$value_id]['price_adjust'] > 0) {
              $price_adjust_text = ' +'.$price_adjust_text;
            }
          }

          echo $system->functions->form_draw_textarea('options['.$group['name'][$system->language->selected['code']].']', isset($_POST['options'][$group['name'][$system->language->selected['code']]]) ? $_POST['options'][$group['name'][$system->language->selected['code']]] : '') . $price_adjust_text. PHP_EOL;
          break;
      }
    }
    
    echo '</p>' . PHP_EOL;
  }
?>
            </div>
            <div class="quantity">
              <p><strong><?php echo $system->language->translate('title_quantity', 'Antal'); ?></strong><br />
                <?php echo $system->functions->form_draw_input_field('quantity', isset($_POST['quantity']) ? $_POST['quantity'] : 1, 'text', 'style="width: 20px;"'); ?>
              </p>
            </div>
            <?php echo $system->functions->form_draw_button('add_cart_product', $system->language->translate('title_add_to_cart', 'Add To Cart'), 'submit'); ?>
            <?php echo $system->functions->form_draw_form_end(); ?>
          </div>
          
          <div style="margin-bottom: 10px;" class="social-bookmarks">
            <!-- AddThis Button BEGIN -->
            <div class="addthis_toolbox addthis_default_style ">
            <a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
            <a class="addthis_button_tweet"></a>
            <a class="addthis_button_pinterest_pinit"></a>
            <a class="addthis_counter addthis_pill_style"></a>
            </div>
            <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script>
            <!-- AddThis Button END -->
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'also_purchased_products.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'similar_products.inc.php'); ?>

<?php
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>