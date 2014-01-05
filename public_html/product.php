<?php
  require_once('includes/app_header.inc.php');
  
  $product = new ref_product($_GET['product_id']);
  
  if ($product->id == 0 || $product->status == 0) {
    notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 File Not Found');
    header('Location: '. document::link(WS_DIR_HTTP_HOME));
    exit;
  }
  
  if (substr($product->date_valid_from, 0, 10) != '0000-00-00 00:00:00' && $product->date_valid_from > date('Y-m-d H:i:s')) {
    notices::add('errors', sprintf(language::translate('text_product_cannot_be_purchased_until_s', 'The product cannot be purchased until %s'), strftime(language::$selected['format_date'], strtotime($product->date_valid_from))));
  }
  
  if (substr($product->date_valid_to, 0, 10) != '0000-00-00' && $product->date_valid_to < date('Y-m-d H:i:s')) {
    notices::add('errors', language::translate('text_product_can_no_longer_be_purchased', 'The product can no longer be purchased'));
  }
  
  database::query(
    "update ". DB_TABLE_PRODUCTS ."
    set views = views + 1
    where id = '". (int)$_GET['product_id'] ."'
    limit 1;"
  );
  
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars(document::link('product.php', array('product_id' => $_GET['product_id']))) .'" />';
  
  document::$snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';
  
  functions::draw_fancybox("a.fancybox[data-fancybox-group='product']");
  
  document::$snippets['head_tags']['animate_from_to'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.animate_from_to-1.0.min.js"></script>';
  
  if (empty($_GET['category_id']) && empty($product->manufacturer)) {
    if (count($product->category_ids)) {
      $category_ids = array_values($product->category_ids);
      $_GET['category_id'] = array_shift($category_ids);
    }
  }
  
  if (!empty($_GET['category_id'])) {
    breadcrumbs::add(language::translate('title_categories', 'Categories'), document::link('categories.php'));
    foreach (functions::catalog_category_trail($_GET['category_id']) as $category_id => $category_name) {
      document::$snippets['title'][] = $category_name;
      breadcrumbs::add($category_name, document::link('category.php', array('category_id' => $category_id)));
    }
  } else if (!empty($product->manufacturer)) {
    document::$snippets['title'][] = $product->manufacturer['name'];
    breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'), document::link('manufacturers.php'));
    breadcrumbs::add(functions::reference_get_manufacturer_name($product->manufacturer['id']), document::link('manufacturer.php', array('manufacturer_id' => $product->manufacturer['id'])));
  }
  
  document::$snippets['title'][] = $product->head_title[language::$selected['code']] ? $product->head_title[language::$selected['code']] : $product->name[language::$selected['code']];
  document::$snippets['keywords'] = $product->meta_keywords[language::$selected['code']] ? $product->meta_keywords[language::$selected['code']] : $product->keywords;
  document::$snippets['description'] = $product->meta_description[language::$selected['code']] ? $product->meta_description[language::$selected['code']] : $product->short_description[language::$selected['code']];
  
  breadcrumbs::add($product->name[language::$selected['code']], document::link('', array('product_id' => $product->id), array('category_id')));
  
  include(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
?>

<div class="box" id="box-product" itemscope itemtype="http://www.schema.org/Product">
  <div class="heading" style="overflow: hidden;">
    <h1 itemprop="name"><?php echo $product->name[language::$selected['code']]; ?></h1>
    <?php echo (!empty($product->sku)) ? '<div class="sku">'. $product->sku .'</div>' : false; ?>
  </div>
  
  <div class="content">
    <table>
      <tr>
        <td style="width: 320px; vertical-align: top;">
          <div class="product-images-wrapper">
<?php
  if (count($product->images) > 0) {
    $first_image = true;
    foreach ($product->images as $image) {
      if ($first_image) {
      
        $sticker = '';
        if (!empty($product->campaign['price'])) {
          $sticker = '<img src="'. WS_DIR_IMAGES .'stickers/sale.png" width="48" height="48" alt="" title="'. language::translate('title_on_sale', 'On Sale') .'" class="sticker" />';
        } else if ($product->date_created > date('Y-m-d', strtotime('-'.settings::get('new_products_max_age')))) {
          $sticker = '<img src="'. WS_DIR_IMAGES .'stickers/new.png" width="48" height="48" alt="" title="'. language::translate('title_new', 'New') .'" class="sticker" />';
        }
        
        echo '<div style="position: relative;">' . PHP_EOL
           . '  <a href="'. WS_DIR_IMAGES . $image .'" class="fancybox" data-fancybox-group="product"><img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image, FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 310, 310, 'FIT_USE_WHITESPACING') .'" class="main-image zoomable shadow rounded-corners" alt="" title="'. htmlspecialchars($product->name[language::$selected['code']]) .'" itemprop="image" /></a>' . PHP_EOL
           . '  '. $sticker . PHP_EOL
           . '</div>' . PHP_EOL;
        $first_image = false;
      } else {
        echo '<div style="display: inline;"><a href="'. WS_DIR_IMAGES . $image .'" class="fancybox" data-fancybox-group="product"><img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image, FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 100, 133, 'CROP') .'" style="margin: 5px 5px 0px 0px;" class="extra-image zoomable shadow" title="'. htmlspecialchars($product->name[language::$selected['code']]) .'" /></a></div>';
      }
    }
  } else {
    echo '<img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 310, 310, 'FIT_USE_WHITESPACING') .'" class="extra-image" alt="" />' . PHP_EOL;
  }
?>
          </div>
        </td>
        
        <td style="padding-left: 20px; vertical-align: top; width: 100%;">
        
          <?php if ($product->manufacturer_id) { ?>
          <div style="font-size: 1.5em; margin-bottom: 10px;" class="manufacturer" itemscope itemtype="http://www.schema.org/Organisation">
<?php
      if ($product->manufacturer['image']) {
        echo '<a href="'. document::href_link('manufacturer.php', array('manufacturer_id' => $product->manufacturer_id)) .'"><img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 200, 60) .'" alt="'. $product->manufacturer['name'] .'" title="'. $product->manufacturer['name'] .'" itemprop="image" /></a>';
      } else {
        echo '<a href="'. document::href_link('manufacturer.php', array('manufacturer_id' => $product->manufacturer_id)) .'" itemprop="name">'. $product->manufacturer['name'] .'</a>';
      }
?>
          </div>
          <?php } ?>

          <div style="margin-bottom: 10px;" class="price-wrapper" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><?php echo $product->campaign['price'] ? '<s class="regular-price">'. currency::format(tax::calculate($product->price, $product->tax_class_id)) .'</s> <strong class="campaign-price" itemprop="price">'. currency::format(tax::calculate($product->campaign['price'], $product->tax_class_id)) .'</strong>' : '<span class="price" itemprop="price">'. currency::format(tax::calculate($product->price, $product->tax_class_id)); ?></div>
          
          <div style="margin-bottom: 10px;" class="tax">
<?php
    if ($tax_rates = tax::get_tax_by_rate($product->campaign['price'] ? $product->campaign['price'] : $product->price, $product->tax_class_id)) {
      
      if (!empty(customer::$data['display_prices_including_tax'])) {
        echo language::translate('title_including_tax', 'Including Tax') .':<br/>' . PHP_EOL;
      } else {
        echo language::translate('title_excluding_tax', 'Excluding Tax') .':<br/>' . PHP_EOL;
      }
      $use_br = false;
      foreach ($tax_rates as $tax_rate) {
        echo currency::format($tax_rate['tax']) .' ('. $tax_rate['name'] .')<br/>' . PHP_EOL;
      }
    } else {
      echo language::translate('title_excluding_tax', 'Excluding Tax');
    }
?>
          </div>
          
          <div style="margin-bottom: 10px;">
<?php
  if ($product->quantity > 0) {
    echo '<div class="stock-available">'. language::translate('title_stock_status', 'Stock Status') .': <span class="value">'. ((settings::get('display_stock_count')) ? sprintf(language::translate('text_d_pieces', '%d pieces'), $product->quantity) : language::translate('title_in_stock', 'In Stock')) .'</span></div>';
    if (!empty($product->delivery_status['name'][language::$selected['code']])) echo '<div class="stock-delivery">'. language::translate('title_delivery_status', 'Delivery Status') .': <span class="value">'. $product->delivery_status['name'][language::$selected['code']] .'</span></div>';
  } else {
    if (!empty($product->sold_out_status['name'][language::$selected['code']])) {
      echo '<div class="'. ($product->sold_out_status['orderable'] ? 'stock-partly-available' : 'stock-unavailable') .'">'. language::translate('title_stock_status', 'Stock Status') .': <span class="value">'. $product->sold_out_status['name'][language::$selected['code']] .'</span></div>';
    } else {
      echo '<div class="stock-unavailable">'. language::translate('title_stock_status', 'Stock Status') .': <span class="value">'. language::translate('title_sold_out', 'Sold Out') .'</span></div>';
    }
  }
?>
          </div>
      
<?php
    if (settings::get('display_cheapest_shipping')) {
?>
          <div style="margin-bottom: 10px;" class="cheapest-shipping">
<?php
    $shipping = new mod_shipping('local');
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
    $shipping->destination = customer::$data;
    $cheapest_shipping = $shipping->cheapest();
    if (!empty($cheapest_shipping)) {
      list($module_id, $option_id) = explode(':', $cheapest_shipping);
      $shipping_cost = $shipping->data['options'][$module_id]['options'][$option_id]['cost'];
      $shipping_tax_class_id = $shipping->data['options'][$module_id]['options'][$option_id]['tax_class_id'];
      echo str_replace(
             '%price',
             currency::format(tax::calculate($shipping_cost, $shipping_tax_class_id)),
             language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from %price')
           );
    }
?>
          </div>
<?php
  }
?>
      
          <div style="margin-bottom: 20px;" class="buy_now">
            <?php echo functions::form_draw_form_begin('buy_now_form'); ?>
            <?php echo functions::form_draw_hidden_field('product_id', $product->id); ?>
            
            <table>
<?php
  if (count($product->options) > 0) {
    
    foreach ($product->options as $group) {
    
      echo '  <tr>' . PHP_EOL
         . '    <td class="options"><strong>'. $group['name'][language::$selected['code']] .'</strong>'. (empty($group['required']) == false ? ' <span class="required">*</span>' : '') .'<br />'
         . (!empty($group['description'][language::$selected['code']]) ? $group['description'][language::$selected['code']] . '<br />' . PHP_EOL : '');
      
      switch ($group['function']) {
      
        case 'checkbox':
          $use_br = false;
          
          foreach (array_keys($group['values']) as $value_id) {
            if ($use_br) echo '<br />';
            
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format(tax::calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }
            
            echo '<label>' . functions::form_draw_checkbox('options['.$group['name'][language::$selected['code']].'][]', $group['values'][$value_id]['name'][language::$selected['code']], true, !empty($group['required']) ? 'required="required"' : '') .' '. $group['values'][$value_id]['name'][language::$selected['code']] . $price_adjust_text . '</label>' . PHP_EOL;
            $use_br = true;
          }
          break;
          
        case 'input':
        
          $value_ids = array_keys($group['values']);
          $value_id = array_shift($value_ids);
        
          $price_adjust_text = '';
          if ($group['values'][$value_id]['price_adjust']) {
            $price_adjust_text = currency::format(tax::calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
            if ($group['values'][$value_id]['price_adjust'] > 0) {
              $price_adjust_text = ' +'.$price_adjust_text;
            }
          }
          
          echo functions::form_draw_text_field('options['.$group['name'][language::$selected['code']].']', isset($_POST['options'][$group['name'][language::$selected['code']]]) ? true : $group['values'][$value_id]['value'], !empty($group['required']) ? 'required="required"' : '') . $price_adjust_text . PHP_EOL;
          break;
          
        case 'radio':
        
          $use_br = false;
          foreach (array_keys($group['values']) as $value_id) {
            if ($use_br) echo '<br />';
            
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format(tax::calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }
            
            echo '<label>' . functions::form_draw_radio_button('options['.$group['name'][language::$selected['code']].']', $group['values'][$value_id]['name'][language::$selected['code']], true, !empty($group['required']) ? 'required="required"' : '') .' '. $group['values'][$value_id]['name'][language::$selected['code']] . $price_adjust_text . '</label>' . PHP_EOL;
            $use_br = true;
          }
          break;
          
        case 'select':
          
          $options = array(array('-- '. language::translate('title_select', 'Select') .' --', ''));
          foreach (array_keys($group['values']) as $value_id) {
          
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust']) {
              $price_adjust_text = currency::format(tax::calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
              if ($group['values'][$value_id]['price_adjust'] > 0) {
                $price_adjust_text = ' +'.$price_adjust_text;
              }
            }

            $options[] = array($group['values'][$value_id]['name'][language::$selected['code']] . $price_adjust_text, $group['values'][$value_id]['name'][language::$selected['code']]);
          }
          echo functions::form_draw_select_field('options['.$group['name'][language::$selected['code']].']', $options, true, false, !empty($group['required']) ? 'required="required"' : '');
          break;
          
        case 'textarea':
          
          $value_ids = array_keys($group['values']);
          $value_id = array_shift($value_ids);
          
          $price_adjust_text = '';
          if (!empty($group['values'][$value_id]['price_adjust'])) {
            $price_adjust_text = '';
            if ($group['values'][$value_id]['price_adjust'] > 0) {
              $price_adjust_text = ' <br />+'. currency::format(tax::calculate($group['values'][$value_id]['price_adjust'], $product->tax_class_id));
            }
          }

          echo functions::form_draw_textarea('options['.$group['name'][language::$selected['code']].']', isset($_POST['options'][$group['name'][language::$selected['code']]]) ? true : $group['values'][$value_id]['value'], !empty($group['required']) ? 'required="required"' : '') . $price_adjust_text. PHP_EOL;
          break;
      }
      
      echo '  </td>' . PHP_EOL
         . '</tr>' . PHP_EOL;
    }
    
  }
?>
              <tr>
                <td class="quantity"><strong><?php echo language::translate('title_quantity', 'Quantity'); ?></strong><br />
                <?php echo functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? $_POST['quantity'] : 1, 1, 99, 'data-size="tiny"'); ?> &nbsp; 
<?php
  if ($product->quantity > 0) {
    echo functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit'); 
  } else {
    if ($product->sold_out_status['orderable']) {
      echo functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit'); 
    } else {
      echo functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit', 'disabled="disabled"'); 
    }
  }
?>
              </td>
            </table>

            <?php echo functions::form_draw_form_end(); ?>
          </div>
          
          <div style="margin-bottom: 10px;" class="social-bookmarks">
            <!-- AddThis Button BEGIN -->
            <div class="addthis_toolbox addthis_default_style addthis_16x16_style">
            <a class="addthis_button_facebook"></a>
            <a class="addthis_button_google_plusone_share"></a>
            <a class="addthis_button_twitter"></a>
            <a class="addthis_button_email"></a>
            <a class="addthis_button_compact"></a><a class="addthis_counter addthis_bubble_style"></a>
            </div>
            <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-5187e5911f6d7f8a"></script>
            <!-- AddThis Button END -->
          </div>
        </td>
      </tr>
    </table>
    
    <?php if (!empty($product->description[language::$selected['code']]) || !empty($product->attributes[language::$selected['code']])) { ?>
    <div class="tabs" style="margin-top: 20px;">
      <ul class="index">
        <li><a href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
        <?php if (!empty($product->attributes[language::$selected['code']])) { ?><li><a href="#tab-details"><?php echo language::translate('title_details', 'Details'); ?></a></li><?php } ?>
      </ul>
      
      <div class="content">
        <div class="tab" id="tab-information" itemprop="description">
          <?php echo $product->description[language::$selected['code']] ? $product->description[language::$selected['code']] : '<p><em style="opacity: 0.65;">'. language::translate('text_no_product_description', 'There is no description for this product yet.') .'</em></p>'; ?>
        </div>
        
        <?php if (!empty($product->attributes[language::$selected['code']])) { ?>
        <div class="tab" id="tab-details">
          <table>
<?php
  $attributes = preg_split('/\R+/', $product->attributes[language::$selected['code']]);
  for ($i=0; $i<count($attributes); $i++) {
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
    if (strpos($attributes[$i], ':') !== false) {
      list($key, $value) = explode(':', $attributes[$i]);
      echo '<tr class="'. $rowclass .'">' . PHP_EOL
         . '  <td>'. trim($key) .':</td>' . PHP_EOL
         . '  <td>'. trim($value) .'</td>' . PHP_EOL
         . '</tr>' . PHP_EOL;
    } else if (trim($attributes[$i] != '')) {
      echo '<tr class="'. $rowclass .' header">' . PHP_EOL
         . '  <th colspan="2" class="header">'. $attributes[$i] .'</th>' . PHP_EOL
         . '</tr>' . PHP_EOL;
    }
  }
?>
          </table>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
    
  </div>
</div>

<script>
  $('form[name=buy_now_form]').submit(function(e) {
    var form = $(this);
    e.preventDefault();
    $("button[name='add_cart_product']").animate_from_to("#cart", {
      initial_css: {
        "border": "1px rgba(0,136,204,1) solid",
        "background-color": "rgba(0,136,204,0.5)",
        "z-index": "999999",
      },
      callback: function() {
        $('*').css('cursor', 'wait');
        $.ajax({
          url: '<?php echo document::link(WS_DIR_AJAX .'cart.json.php'); ?>',
          data: $(form).serialize() + '&add_cart_product=true',
          type: 'post',
          cache: false,
          async: true,
          dataType: 'json',
          beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
          },
          error: function(jqXHR, textStatus, errorThrown) {
            alert('Error');
          },
          success: function(data) {
            if (data['alert']) alert(data['alert']);
            $('#cart .quantity').html(data['quantity']);
            $('#cart .formatted_value').html(data['formatted_value']);
          },
          complete: function() {
            $('*').css('cursor', '');
          }
        });
      }
    });
  });
</script>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'similar_products.inc.php'); ?>

<?php include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'also_purchased_products.inc.php'); ?>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>