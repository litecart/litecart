<div style="margin: 0 -20px 2em -20px;">
  <!--snippet:breadcrumbs-->
</div>

<div id="box-product" class="box" itemscope itemtype="http://www.schema.org/Product">
  <div style="margin-bottom: 20px;">
    <h1 class="title" style="margin-bottom: 0px;" itemprop="name"><?php echo $name; ?></h1>
    <?php if ($sku || $gtin) { ?>
    <div class="codes">
      <?php if ($sku) echo '<span class="sku" itemprop="sku">'. $sku .'</span>'; ?>
      <?php if ($gtin) echo '<span class="gtin" itemprop="gtin14">'. $gtin .'</span>'; ?>
    </div>
    <?php } ?>
  </div>

  <div class="content">
    <div class="images-wrapper">

      <a class="main-image fancybox zoomable shadow" href="<?php echo $image['original']; ?>" data-fancybox-group="product">
        <img class="image" src="<?php echo $image['original']; ?>" srcset="<?php echo $image['thumbnail']; ?> 1x, <?php echo $image['thumbnail_2x']; ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" title="<?php echo htmlspecialchars($name); ?>" itemprop="image" />
        <?php echo $sticker; ?>
      </a>
<?php
  if ($extra_images) {
    foreach ($extra_images as $image) {
      echo '<a class="extra-image fancybox zoomable shadow" href="'. $image['original'] .'" data-fancybox-group="product"><img class="image" src="'. $image['thumbnail'] .'" srcset="'. $image['thumbnail'] .' 1x, '. $image['thumbnail_2x'] .' 2x" alt="'. htmlspecialchars($name) .'" title="'. htmlspecialchars($name) .'" /></a>';
    }
  }
?>
    </div>

    <div class="information">
      <?php if ($manufacturer) { ?>
      <div class="manufacturer" style="font-size: 1.5em; margin-bottom: 10px;" itemscope itemtype="http://www.schema.org/Organization">
      <?php if ($manufacturer['image']) { ?>
        <a href="<?php echo htmlspecialchars($manufacturer['link']); ?>"><img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], 0, 48); ?>" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" title="<?php echo htmlspecialchars($manufacturer['name']); ?>" itemprop="image" /></a>
      <?php } else { ?>
        <a href="<?php echo htmlspecialchars($manufacturer['link']); ?>" itemprop="name"><?php echo $manufacturer['name']; ?></a>
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
        <?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <?php echo implode('<br />', $tax_rates); ?>
      <?php } else { ?>
        <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
      <?php } ?>
      </div>

      <div class="stock-status" style="margin-bottom: 10px;">
      <?php if ($quantity > 0) { ?>
        <div class="stock-available"><?php echo language::translate('title_stock_status', 'Stock Status'); ?>: <span class="value"><?php echo $stock_status_value; ?></span></div>
        <?php if ($delivery_status_value) { ?>
        <div class="stock-delivery"><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>: <span class="value"><?php echo $delivery_status_value;?></span></div>
        <?php } ?>
      <?php } else { ?>
        <?php if ($sold_out_status_value) { ?>
          <div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>"><?php echo language::translate('title_stock_status', 'Stock Status'); ?>: <span class="value"><?php echo $sold_out_status_value; ?></span></div>
        <?php } else { ?>
          <div class="stock-unavailable"><?php echo language::translate('title_stock_status', 'Stock Status'); ?>: <span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span></div>
        <?php } ?>
      <?php } ?>
      </div>

      <?php if ($cheapest_shipping) { ?>
      <div class="cheapest-shipping" style="margin-bottom: 10px;">
        <?php echo functions::draw_fonticon('fa-truck'); ?> <?php echo $cheapest_shipping; ?>
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
            <td class="quantity"><strong><?php echo language::translate('title_quantity', 'Quantity'); ?></strong><br />
<?php
  if (!empty($quantity_unit_decimals)) {
    echo functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit_decimals, 1, null, 'data-size="small"') .' '. $quantity_unit_name .' &nbsp; ';
   } else {
    echo functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, 1, null, 'style="width: 60px;"') .' '. $quantity_unit_name .' &nbsp; ';
  }

  if ($quantity > 0 || $orderable) {
    echo functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit');
  } else {
    echo functions::form_draw_button('add_cart_product', language::translate('title_add_to_cart', 'Add To Cart'), 'submit', 'disabled="disabled"');
  }
?>
            </td>
          </tr>
          <?php } ?>
        </table>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div class="social-bookmarks">
        <a class="facebook" href="<?php echo document::href_link('http://www.facebook.com/sharer.php', array('u' => document::link())); ?>" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square', 'style="color: #3b5998;"'); ?></a>
        <a class="twitter" href="<?php echo document::href_link('http://twitter.com/home/', array('status' => $name .' - '. document::link())); ?>" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square', 'style="color: #55acee;"'); ?></a>
        <a class="googleplus" href="<?php echo document::href_link('https://plus.google.com/share', array('url' => document::link())); ?>" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Google+'); ?>"><?php echo functions::draw_fonticon('fa-google-plus-square', 'style="color: #dd4b39;"'); ?></a>
        <a class="pinterest" href="<?php echo document::href_link('http://pinterest.com/pin/create/button/', array('url' => document::link())); ?>" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square', 'style="color: #bd081c;"'); ?></a>
        <a class="linkedin" href="<?php echo document::href_link('https://www.linkedin.com/cws/share', array('url' => document::link())); ?>" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'LinkedIn'); ?>"><?php echo functions::draw_fonticon('fa-linkedin-square', 'style="color: #0077b5;"'); ?></a>
      </div>

    </div>

    <?php if ($description || $attributes) { ?>
    <div class="tabs" style="margin-top: 20px;">
      <ul class="index">
        <li><a href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
        <?php if ($attributes) { ?><li><a href="#tab-details"><?php echo language::translate('title_details', 'Details'); ?></a></li><?php } ?>
      </ul>

      <div class="content">
        <div id="tab-information" class="tab" itemprop="description">
          <?php echo $description; ?>
        </div>

        <?php if ($attributes) { ?>
        <div id="tab-details" class="tab">
          <table>
<?php
  for ($i=0; $i<count($attributes); $i++) {
    if (strpos($attributes[$i], ':') !== false) {
      @list($key, $value) = explode(':', $attributes[$i]);
      echo '<tr class="row">' . PHP_EOL
         . '  <td>'. trim($key) .':</td>' . PHP_EOL
         . '  <td>'. trim($value) .'</td>' . PHP_EOL
         . '</tr>' . PHP_EOL;
    } else if (trim($attributes[$i]) != '') {
      echo '<tr class="row header">' . PHP_EOL
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
  $('body').on('submit', 'form[name=buy_now_form]', function(e) {
    var form = $(this);
    e.preventDefault();
    $(this).find("button[name='add_cart_product']").animate_from_to("#cart", {
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