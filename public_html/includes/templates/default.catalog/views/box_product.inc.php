<div id="box-product" class="box" style="max-width: 980px;" data-id="<?php echo $product_id; ?>">
  <div class="row">
    <div class="col-xs-whole col-sm-halfs col-md-thirds">
      <div class="image-wrapper">
        <a href="<?php echo htmlspecialchars($image['original']); ?>" data-toggle="lightbox" data-gallery="product">
          <img class="img-responsive" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($name); ?>" />
          <?php echo $sticker; ?>
        </a>
      </div>

      <?php if ($extra_images) { ?>
      <div class="extra-images row half-gutter">
        <?php foreach ($extra_images as $image) { ?>
        <div class="col-xs-thirds">
          <div class="extra-image">
            <a href="<?php echo htmlspecialchars($image['original']); ?>" data-toggle="lightbox" data-gallery="product">
              <img class="img-responsive" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($name); ?>" />
            </a>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>

    <div class="col-sm-halfs col-md-thirds">
      <h1 class="title"><?php echo $name; ?></h1>

      <?php if ($sku || $gtin) { ?>
      <ul class="codes list-inline">
        <?php if ($sku) { ?><li class="sku"><?php echo $sku; ?></li><?php } ?>
        <?php if ($gtin) { ?><li class="gtin"><?php echo $gtin; ?></li><?php } ?>
      </ul>
      <?php } ?>

      <?php if ($description) { ?>
      <div class="description">
        <?php echo $description; ?>
      </div>
      <?php } ?>
    </div>

    <div class="col-sm-halfs col-md-thirds">

      <?php if (!empty($manufacturer['image'])) { ?>
      <div class="manufacturer" style="font-size: 1.5em;">
        <?php if ($manufacturer['image']) { ?>
        <p><a href="<?php echo htmlspecialchars($manufacturer['link']); ?>"><img src="<?php echo functions::image_thumbnail($manufacturer['image']['thumbnail'], 0, 48); ?>" srcset="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?> 1x, <?php echo htmlspecialchars($manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" title="<?php echo htmlspecialchars($manufacturer['name']); ?>" /></a></p>
        <?php } else { ?>
        <p><a href="<?php echo htmlspecialchars($manufacturer['link']); ?>"><?php echo $manufacturer['name']; ?></a></p>
        <?php } ?>
      </div>
      <?php } ?>

      <div class="price-wrapper">
        <?php if ($campaign_price) { ?>
        <del class="regular-price"><?php echo currency::format($regular_price); ?></del> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
        <?php } else { ?>
        <span class="price"><?php echo currency::format($regular_price); ?></span>
        <?php } ?>
      </div>

      <div class="tax" style="margin: 0 0 1em 0;">
       <?php if ($tax_rates) { ?>
        <?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <span class="total-tax"><?php echo currency::format($total_tax); ?></span>
       <?php } else { ?>
        <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
       <?php } ?>
      </div>

      <?php if ($cheapest_shipping_fee !== null) { ?>
      <div class="cheapest-shipping" style="margin: 1em 0;">
        <?php echo functions::draw_fonticon('fa-truck'); ?> <?php echo strtr(language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">%price</strong>'), array('%price' => currency::format($cheapest_shipping_fee))); ?>
      </div>
      <?php } ?>

      <div class="stock-status" style="margin: 1em 0;">
       <?php if ($quantity > 0) { ?>
        <div class="stock-available">
          <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
          <span class="value"><?php echo $stock_status; ?></span>
        </div>
        <?php if ($delivery_status) { ?>
        <div class="stock-delivery">
          <?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>:
          <span class="value"><?php echo $delivery_status;?></span>
        </div>
        <?php } ?>
       <?php } else { ?>
        <?php if ($sold_out_status) { ?>
          <div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>">
            <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
            <span class="value"><?php echo $sold_out_status; ?></span>
          </div>
        <?php } else { ?>
          <div class="stock-unavailable">
            <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
            <span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span>
          </div>
        <?php } ?>
       <?php } ?>
      </div>

      <hr />

      <div class="buy_now" style="margin: 1em 0;">
        <?php echo functions::form_draw_form_begin('buy_now_form', 'post'); ?>
        <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

        <?php if ($options) { ?>
          <?php foreach ($options as $option) { ?>
          <div class="form-group">
            <label><?php echo $option['name']; ?></label>
            <?php echo $option['description'] ? '<div>' . $option['description'] . '</div>' : ''; ?>
            <?php echo $option['values']; ?>
          </div>
          <?php } ?>
        <?php } ?>

        <?php if (!$catalog_only_mode) { ?>
        <div class="form-group">
          <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
          <div style="display: flex">
            <div class="input-group">
              <?php echo (!empty($quantity_unit['decimals'])) ? functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 1, null) : (functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, 1)); ?>
              <?php echo !empty($quantity_unit['name']) ? '<div class="input-group-addon">'. $quantity_unit['name'] .'</div>' : ''; ?>
            </div>
            <div style="padding-left: 0.5em; white-space: nowrap;">
              <?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity <= 0 && !$orderable) ? ' disabled="disabled"' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
            </div>
          </div>
        </div>
        <?php } ?>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <hr />

      <div class="social-bookmarks text-center">
        <a class="link" href="#"><?php echo functions::draw_fonticon('fa-link', 'style="color: #333;"'); ?></a>
        <a class="twitter" href="<?php echo document::href_link('http://twitter.com/home/', array('status' => $name .' - '. $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
        <a class="facebook" href="<?php echo document::href_link('http://www.facebook.com/sharer.php', array('u' => $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
        <a class="googleplus" href="<?php echo document::href_link('https://plus.google.com/share', array('url' => $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Google+'); ?>"><?php echo functions::draw_fonticon('fa-google-plus-square fa-lg', 'style="color: #dd4b39;"'); ?></a>
        <a class="pinterest" href="<?php echo document::href_link('http://pinterest.com/pin/create/button/', array('url' => $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
      </div>

    </div>
  </div>

  <?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') { ?>
  <?php if ($attributes) { ?>
  <div class="attributes">
    <table class="table table-striped">
<?php
  for ($i=0; $i<count($attributes); $i++) {
    if (strpos($attributes[$i], ':') !== false) {
      @list($key, $value) = explode(':', $attributes[$i]);
      echo '  <tr>' . PHP_EOL
         . '    <td>'. trim($key) .':</td>' . PHP_EOL
         . '    <td>'. trim($value) .'</td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    } else if (trim($attributes[$i]) != '') {
      echo '  <thead>' . PHP_EOL
         . '    <tr>' . PHP_EOL
         . '      <th colspan="2">'. $attributes[$i] .'</th>' . PHP_EOL
         . '    </tr>' . PHP_EOL
         . '  </thead>' . PHP_EOL
         . '  <tbody>' . PHP_EOL;
    } else {
      echo ' </tbody>' . PHP_EOL
         . '</table>' . PHP_EOL
         . '<table class="table table-striped">' . PHP_EOL;
    }
  }
?>
    </table>
  </div>
  <?php } ?>
  <?php } ?>
</div>

<script>
  Number.prototype.toMoney = function() {
    var number = this;
    var decimals = <?php echo currency::$selected['decimals']; ?>;
    var decimal_point = '<?php echo language::$selected['decimal_point']; ?>';
    var thousands_sep = '<?php echo language::$selected['thousands_sep']; ?>';
    var prefix = '<?php echo currency::$selected['prefix']; ?>';
    var suffix = '<?php echo currency::$selected['suffix']; ?>';
    var sign = (number < 0) ? '-' : '';

    var i = parseInt(number = Math.abs(number).toFixed(decimals)) + '';
    if (number - i == 0) decimals = 0;

    var l = ((l = i.length) > 3) ? l % 3 : 0;
    var f = sign + prefix + (l ? i.substr(0, l) + thousands_sep : '') + i.substr(l).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep) + (decimals ? decimal_point + Math.abs(number - i).toFixed(decimals).slice(decimals) : '') + suffix;

    return f;
  }

  $('#box-product form[name=buy_now_form]').bind('input propertyChange', function(e) {

    var regular_price = <?php echo currency::format_raw($regular_price); ?>;
    var sales_price = <?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>;
    var tax = <?php echo currency::format_raw($total_tax); ?>;

    $(this).find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function(){
      if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
      if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
    });

    $(this).find('select option:checked').each(function(){
      if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
      if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
    });

    $(this).find('input[type!="radio"][type!="checkbox"]').each(function(){
      if ($(this).val() != '') {
      if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
      if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
      }
    });

    $('#box-product .regular-price').text(regular_price.toMoney());
    $('#box-product .campaign-price').text(sales_price.toMoney());
    $('#box-product .price').text(sales_price.toMoney());
    $('#box-product .total-tax').text(tax.toMoney());
  });

  $('#box-product[data-id="<?php echo $product_id; ?>"] .social-bookmarks .link').off().click(function(e){
    e.preventDefault();
    prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '<?php echo $link; ?>');
  });
</script>
