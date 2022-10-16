<article id="box-product" class="box" data-id="<?php echo $product_id; ?>" data-sku="<?php echo functions::escape_html($sku); ?>" data-name="<?php echo functions::escape_html($name); ?>" data-price="<?php echo currency::format_raw($final_price); ?>">

  <div class="row">
    <div class="col-md-6">
      <div class="images row">

        <div class="col-xs-12">
          <a class="main-image thumbnail" href="<?php echo document::href_link(WS_DIR_APP . $image['original']); ?>" data-toggle="lightbox" data-gallery="product">
            <img class="img-responsive" src="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $image['thumbnail_2x']); ?> 2x" style="aspect-ratio: <?php echo $image['ratio']; ?>;" alt="" title="<?php echo functions::escape_html($name); ?>" />
            <?php echo $sticker; ?>
          </a>
        </div>

        <?php foreach ($extra_images as $extra_image) { ?>
        <div class="col-xs-4">
          <a class="extra-image thumbnail" href="<?php echo document::href_link(WS_DIR_APP . $extra_image['original']); ?>" data-toggle="lightbox" data-gallery="product">
            <img class="img-responsive" src="<?php echo document::href_link(WS_DIR_APP . $extra_image['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $extra_image['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $extra_image['thumbnail_2x']); ?> 2x" style="aspect-ratio: <?php echo $image['ratio']; ?>;" alt="" title="<?php echo functions::escape_html($name); ?>" />
          </a>
        </div>
        <?php } ?>

      </div>
    </div>

    <div class="col-md-6">
      <h1 class="title"><?php echo $name; ?></h1>

      <?php if ($short_description) { ?>
      <p class="short-description">
        <?php echo $short_description; ?>
      </p>
      <?php } ?>

      <?php if (!empty($manufacturer)) { ?>
      <div class="manufacturer">
        <a href="<?php echo functions::escape_html($manufacturer['link']); ?>">
          <?php if ($manufacturer['image']) { ?>
          <img src="<?php echo document::href_link(WS_DIR_APP . $manufacturer['image']['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $manufacturer['image']['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($manufacturer['name']); ?>" title="<?php echo functions::escape_html($manufacturer['name']); ?>" />
          <?php } else { ?>
          <h3><?php echo $manufacturer['name']; ?></h3>
          <?php } ?>
        </a>
      </div>
      <?php } ?>

      <?php if ($cheapest_shipping_fee !== null) { ?>
      <div class="cheapest-shipping" style="margin: 1em 0;">
        <?php echo functions::draw_fonticon('fa-truck'); ?> <?php echo strtr(language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">%price</strong>'), ['%price' => currency::format($cheapest_shipping_fee)]); ?>
      </div>
      <?php } ?>

      <?php if ($sku || $mpn || $gtin) { ?>
      <div class="codes" style="margin: 1em 0;">
        <?php if ($sku) { ?>
        <div class="sku">
          <?php echo language::translate('title_sku', 'SKU'); ?>:
          <span class="value"><?php echo $sku; ?></span>
        </div>
        <?php } ?>

        <?php if ($mpn) { ?>
        <div class="mpn">
          <?php echo language::translate('title_mpn', 'MPN'); ?>:
          <span class="value"><?php echo $mpn; ?></span>
        </div>
        <?php } ?>

        <?php if ($gtin) { ?>
        <div class="gtin">
          <?php echo language::translate('title_gtin', 'GTIN'); ?>:
          <span class="value"><?php echo $gtin; ?></span>
        </div>
        <?php } ?>
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
          <span class="value"><?php echo $delivery_status['name']; ?></span>
        </div>
        <?php } ?>
       <?php } else { ?>
        <?php if ($sold_out_status) { ?>
          <div class="<?php echo empty($sold_out_status['orderable']) ? 'stock-partly-available' : 'stock-unavailable'; ?>">
            <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
            <span class="value"><?php echo $sold_out_status['name']; ?></span>
          </div>
        <?php } else { ?>
          <div class="stock-unavailable">
            <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
            <span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span>
          </div>
        <?php } ?>
       <?php } ?>
      </div>

      <?php if ($recommended_price) { ?>
      <div class="recommended-price" style="margin: 1em 0;">
        <?php echo language::translate('title_recommended_price', 'Recommended Price'); ?>:
        <span class="value"><?php echo currency::format($recommended_price); ?></span>
      </div>
      <?php } ?>

      <div class="buy_now" style="margin: 1em 0;">
        <?php echo functions::form_draw_form_begin('buy_now_form', 'post'); ?>
        <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

        <?php if ($options) { ?>
        <div class="options">
          <?php foreach ($options as $option) { ?>
          <div class="form-group">
            <label><?php echo $option['name']; ?></label>
            <?php echo $option['values']; ?>
          </div>
          <?php } ?>
        </div>
        <?php } ?>

        <div class="price-wrapper">
          <?php if ($campaign_price) { ?>
          <del class="regular-price"><?php echo currency::format($regular_price); ?></del> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
          <?php } else if ($recommended_price) { ?>
          <del class="recommended-price"><?php echo currency::format($recommended_price); ?></del> <strong class="price"><?php echo currency::format($regular_price); ?></strong>
          <?php } else { ?>
          <span class="price"><?php echo currency::format($regular_price); ?></span>
          <?php } ?>
        </div>

        <div class="tax" style="margin-bottom: 1em;">
         <?php if ($tax_rates) { ?>
          <?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <span class="total-tax"><?php echo currency::format($total_tax); ?></span>
         <?php } else { ?>
          <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
         <?php } ?>
        </div>

        <?php if ($campaign_price_end_date) { ?>
        <div class="offer-expires" style="margin-bottom: 1em;">
          <?php echo strtr(language::translate('text_offer_expires_on_date', 'The offer expires on %datetime.'), ['%datetime' => language::strftime(language::$selected['format_datetime'], strtotime($campaign_price_end_date))]); ?>
        </div>
        <?php } ?>

        <?php if (!settings::get('catalog_only_mode') && ($quantity > 0 || empty($sold_out_status) || !empty($sold_out_status['orderable']))) { ?>
        <div class="form-group" style="margin-bottom: 0;">
          <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
          <div style="display: flex">
            <div class="input-group" style="flex: 0 1 150px;">
              <?php echo (!empty($quantity_unit['decimals'])) ? functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], $quantity_min, $quantity_max, 'step="'. ($quantity_step ? $quantity_step : '') .'"') : functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_min ? $quantity_min : '1', $quantity_max ? $quantity_max : null, 'step="'. ($quantity_step ? $quantity_step : '') .'"'); ?>
              <?php echo !empty($quantity_unit['name']) ? '<div class="input-group-text">'. $quantity_unit['name'] .'</div>' : ''; ?>
            </div>

            <div style="padding-inline-start: 1em;">
              <?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity <= 0 && !$orderable) ? ' disabled' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
            </div>
          </div>
        </div>
        <?php } ?>

        <div class="stock-notice"></div>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <?php if ($quantity <= 0 && !empty($sold_out_status) && empty($sold_out_status['orderable'])) { ?>
      <div class="out-of-stock-notice">
        <?php echo language::translate('description_item_is_out_of_stock', 'This item is currently out of stock and cannot be purchased.'); ?>
      </div>
      <?php } ?>

      <div class="social-bookmarks text-center">
        <a class="link" href="#"><?php echo functions::draw_fonticon('fa-link', 'style="color: #333;"'); ?></a>
        <a class="twitter" href="<?php echo document::href_link('https://twitter.com/intent/tweet/', ['text' => $name .' - '. $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
        <a class="facebook" href="<?php echo document::href_link('https://www.facebook.com/sharer.php', ['u' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
        <a class="pinterest" href="<?php echo document::href_link('https://pinterest.com/pin/create/button/', ['url' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
      </div>

    </div>
  </div>

  <?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') { ?>
  <ul class="nav nav-tabs">
    <?php if ($description) { ?><li><a data-toggle="tab" href="#tab-description"><?php echo language::translate('title_description', 'Description'); ?></a></li><?php } ?>
    <?php if ($technical_data) { ?><li><a data-toggle="tab" href="#tab-technical-data"><?php echo language::translate('title_technical_data', 'Technical Data'); ?></a></li><?php } ?>
  </ul>

  <div class="tab-content">
    <div id="tab-description" class="tab-pane description">
      <?php echo $description; ?>
    </div>

    <?php if ($technical_data) { ?>
    <div id="tab-technical-data" class="tab-pane technical-data">
      <table class="table table-striped table-hover">
<?php
  foreach ($technical_data as $line) {
    if (preg_match('#[:\t]#', $line)) {
      list($key, $value) = preg_split('# *[:\t]+ *#', $line, 2);
      echo '  <tr>' . PHP_EOL
         . '    <td>'. trim($key) .'</td>' . PHP_EOL
         . '    <td>'. trim($value) .'</td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    } else if (trim($line)) {
      echo '  <tr>' . PHP_EOL
         . '    <th colspan="2" class="text-start">'. $line .'</th>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    } else {
      echo '</table>' . PHP_EOL
         . '<table class="table table-striped table-hover">' . PHP_EOL;
    }
  }
?>
      </table>
    </div>
    <?php } ?>
  </div>
  <?php } ?>

</article>

<script>
  Number.prototype.toMoney = function() {
    var n = this,
      c = <?php echo (int)currency::$selected['decimals']; ?>,
      d = '<?php echo language::$selected['decimal_point']; ?>',
      t = '<?php echo addslashes(language::$selected['thousands_sep']); ?>',
      p = '<?php echo currency::$selected['prefix']; ?>',
      x = '<?php echo currency::$selected['suffix']; ?>',
      s = n < 0 ? '-' : '',
      i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
      f = n - i,
      j = (j = i.length) > 3 ? j % 3 : 0;

    return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (<?php echo (settings::get('auto_decimals')) ? "(c && f)" : "c"; ?> ? d + Math.abs(f).toFixed(c).slice(2) : '') + x;
  }

  $('#box-product[data-id="<?php echo $product_id; ?>"] form[name="buy_now_form"]').on('input', function(e) {

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

    $(this).find('.regular-price').text(regular_price.toMoney());
    $(this).find('.campaign-price').text(sales_price.toMoney());
    $(this).find('.price').text(sales_price.toMoney());
    $(this).find('.total-tax').text(tax.toMoney());
  });

  $('#box-product form[name="buy_now_form"] .options :input').change(function(){

    $.ajax({
      type: 'post',
      url: '<?php echo document::ilink('ajax/product_options_stock.json'); ?>',
      data: $(this).closest('form').serialize(),
      dataType: 'json',
      cache: false,

      error: function(jqXHR, textStatus, errorThrown) {
        console.log('error', errorThrown);
      },

      success: function(data){
        console.log(data);
        if (data.status == 'ok') {
          $('.stock-notice').text(data.notice).removeClass('warning');
        } else if (data.status == 'warning') {
          $('.stock-notice').text(data.notice).addClass('warning');
        } else {
          $('.stock-notice').html('');
        }
      }
    });
  });

  $('#box-product[data-id="<?php echo $product_id; ?>"] .social-bookmarks .link').off().click(function(e){
    e.preventDefault();
    prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '<?php echo $link; ?>');
  });
</script>
