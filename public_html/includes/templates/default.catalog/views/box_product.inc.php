<div id="box-product" class="box" itemscope itemtype="http://www.schema.org/Product">
  <div class="heading" style="overflow: hidden;">
    <h1 itemprop="name"><?php echo $name; ?></h1>
    <?php if ($sku) echo '<div class="sku">'. $sku .'</div>'; ?>
  </div>
  
  <div class="content">
    <div class="product-images-wrapper">

      <div style="position: relative;">
        <a href="<?php echo $image['original']; ?>" class="fancybox" data-fancybox-group="product"><img src="<?php echo $image['thumbnail']; ?>" class="main-image zoomable shadow rounded-corners" alt="" title="<?php echo htmlspecialchars($name); ?>" itemprop="image" /></a>
        <?php echo $sticker; ?>
      </div>
<?php
  if ($extra_images) {
    foreach ($extra_images as $image) {
      echo '<div style="display: inline;"><a href="'. $image['original'] .'" class="fancybox" data-fancybox-group="product"><img src="'. $image['thumbnail'] .'" class="extra-image zoomable shadow" title="'. htmlspecialchars($name) .'" /></a></div>';
    }
  }
?>
    </div>

    <div class="information">
      <?php if ($manufacturer_name) { ?>
      <div class="manufacturer" style="font-size: 1.5em; margin-bottom: 10px;" itemscope itemtype="http://www.schema.org/Organisation">
      <?php if ($manufacturer_image) { ?>
        <a href="<?php echo htmlspecialchars($manufacturer_url); ?>"><img src="<?php echo htmlspecialchars($manufacturer_image); ?>" alt="<?php echo htmlspecialchars($manufacturer_name); ?>" title="<?php echo htmlspecialchars($manufacturer_name); ?>" itemprop="image" /></a>
      <?php } else { ?>
        <a href="<?php echo htmlspecialchars($manufacturer_url); ?>" itemprop="name"><?php echo $manufacturer_name; ?></a>
      <?php } ?>
      </div>
      <?php } ?>

      <div class="price-wrapper" style="margin-bottom: 10px;" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        <?php if ($campaign_price) { ?>
        <s class="regular-price"><?php echo $regular_price; ?></s> <strong class="campaign-price" itemprop="price"><?php echo $campaign_price; ?></strong>
        <?php } else { ?>
        <span class="price" itemprop="price"><?php echo $regular_price; ?></span>
        <?php } ?>
      </div>
      
      <div class="tax" style="margin-bottom: 10px;">
      <?php if ($tax_rates) { ?>
        <?php echo $tax_status; ?>: <?php echo implode('<br />', $tax_rates); ?>
      <?php } else { ?>
        <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
      <?php } ?>
      </div>
      
      <div style="margin-bottom: 10px;">
      <?php if ($quantity > 0) { ?>
        <div class="stock-available"><?php echo $title_stock_status; ?>: <span class="value"><?php echo $stock_status_value; ?></span></div>
        <div class="stock-delivery"><?php echo $title_delivery_status; ?>: <span class="value"><?php echo $delivery_status_value;?></span></div>
        <?php } else { ?>
        <?php if ($sold_out_status_value) { ?>
          <div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>"><?php echo $title_stock_status; ?>: <span class="value"><?php echo $sold_out_status_value; ?></span></div>
        <?php } else { ?>
          <div class="stock-unavailable"><?php echo language::translate('title_stock_status', 'Stock Status'); ?>: <span class="value"><?php echo $title_sold_out; ?></span></div>
        <?php } ?>
      <?php } ?>
      </div>
  
      <?php if ($cheapest_shipping) { ?>
      <div class="cheapest-shipping" style="margin-bottom: 10px;">
        <?php echo $cheapest_shipping; ?>
      </div>
      <?php } ?>
  
      <div class="buy_now" style="margin-bottom: 20px;">
        <?php echo functions::form_draw_form_begin('buy_now_form'); ?>
        <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>
        
        <table>
<?php
  if ($options) {
    foreach ($options as $option) {
    
      echo '  <tr>' . PHP_EOL
         . '    <td class="options"><strong>'. $option['name'] .'</strong>'. (!empty($option['required']) ? ' <span class="required">*</span>' : '') .'<br />'
         .      ($option['description'] ? $option['description'] . '<br />' . PHP_EOL : '')
         .      $option['values'] . PHP_EOL
         . '    </td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    }
  }
?>
          <?php if (!$catalog_only_mode) { ?>
          <tr>
            <td class="quantity"><strong><?php echo $title_quantity; ?></strong><br />
<?php
  if (!empty($quantity_unit_decimals)) {
    echo functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit_decimals, 1, null, 'data-size="small"') .' '. $quantity_unit_name .' &nbsp; ';
   } else {
    echo functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, 1, null, 'data-size="tiny"') .' '. $quantity_unit_name .' &nbsp; ';
  }

  if ($quantity > 0 || $orderable) {
    echo functions::form_draw_button('add_cart_product', $title_add_to_cart, 'submit'); 
  } else {
    echo functions::form_draw_button('add_cart_product', $title_add_to_cart, 'submit', 'disabled="disabled"'); 
  }
?>
            </td>
          </tr>
          <?php } ?>
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
      
    </div>
    
    <?php if ($description || $attributes) { ?>
    <div class="tabs" style="margin-top: 20px;">
      <ul class="index">
        <li><a href="#tab-information"><?php echo $title_information; ?></a></li>
      <?php if ($attributes) { ?>
        <li><a href="#tab-details"><?php echo $title_details; ?></a></li>
      <?php } ?>
      </ul>
      
      <div class="content">
        <div class="tab" id="tab-information" itemprop="description">
          <?php echo $description; ?>
        </div>
        
        <?php if ($attributes) { ?>
        <div class="tab" id="tab-details">
          <table>
<?php
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
          url: '<?php echo document::ilink('ajax/cart.json'); ?>',
          data: $(form).serialize() + '&add_cart_product=true',
          type: 'post',
          cache: false,
          async: true,
          dataType: 'json',
          beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
          },
          error: function(jqXHR, textStatus, errorThrown) {
            //alert("Error\n"+ jqXHR.responseText);
            alert("Error");
          },
          success: function(data) {
            if (data['alert']) alert(data['alert']);
            $('#cart .quantity').html(data['quantity']);
            $('#cart .formatted_value').html(data['formatted_value']);
            if (data['quantity'] > 0) {
              $('#cart img').attr('src', '{snippet:template_path}images/cart_filled.png');
            } else {
              $('#cart img').attr('src', '{snippet:template_path}images/cart.png');
            }
          },
          complete: function() {
            $('*').css('cursor', '');
          }
        });
      }
    });
  });
</script>