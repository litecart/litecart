<style>
form[name="buy_now_form"] .dropdown-menu {
  left: 0;
  right: 0;
}
form[name="buy_now_form"] .dropdown-menu .image {
  border-radius: var(--border-radius);
  border: 1px solid var(--default-border-color);
  vertical-align: middle;
}

</style>

<article id="box-product" data-id="{{product_id}}" data-name="{{name|escape}}" data-price="<?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>">

  <div class="row layout" style="margin-bottom: 0;">
    <div class="col-sm-4 col-md-6">
      <div class="images row" style="margin-bottom: 0;">

        <div class="col-12">
          <a class="main-image thumbnail" href="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['original']); ?>" data-toggle="lightbox" data-gallery="product">
            <img class="responsive <?php echo $image['viewport']['clipping']; ?>" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail']); ?> 1x, <?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail_2x']); ?> 2x" alt="" title="{{name|escape}}" style="aspect-ratio: <?php echo $image['viewport']['ratio']; ?>;" />
            {{sticker}}
          </a>
        </div>

        <?php foreach ($extra_images as $extra_image) { ?>
        <div class="col-4">
          <a class="extra-image thumbnail" href="<?php echo document::href_rlink(FS_DIR_STORAGE . $extra_image['original']); ?>" data-toggle="lightbox" data-gallery="product">
            <img class="responsive <?php echo $extra_image['viewport']['clipping']; ?>" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $extra_image['thumbnail']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_STORAGE . $extra_image['thumbnail']); ?> 1x, <?php echo document::href_rlink(FS_DIR_STORAGE . $extra_image['thumbnail_2x']); ?> 2x" alt="" title="{{name|escape}}" style="aspect-ratio: <?php echo $image['viewport']['ratio']; ?>;" />
          </a>
        </div>
        <?php } ?>

      </div>
    </div>

    <div class="col-sm-8 col-md-6">
      <h1 class="title">{{name}}</h1>

      <?php if ($short_description) { ?>
      <p class="short-description">
        {{short_description}}
      </p>
      <?php } ?>

      <?php if (!empty($brand)) { ?>
      <div class="brand">
        <a href="<?php echo functions::escape_html($brand['link']); ?>">
          <?php if ($brand['image']) { ?>
          <img src="<?php echo document::href_rlink(FS_DIR_STORAGE . $brand['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_STORAGE . $brand['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink(FS_DIR_STORAGE . $brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($brand['name']); ?>" />
          <?php } else { ?>
          <h3><?php echo $brand['name']; ?></h3>
          <?php } ?>
        </a>
      </div>
      <?php } ?>

      <?php if ($recommended_price) { ?>
      <div class="recommended-price" style="margin: 1em 0;">
        <?php echo language::translate('title_recommended_price', 'Recommended Price'); ?>:
        <span class="value">{{recommended_price|money}}</span>
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
          <span class="value">{{sku}}</span>
        </div>
        <?php } ?>

        <?php if ($mpn) { ?>
        <div class="mpn">
          <?php echo language::translate('title_mpn', 'MPN'); ?>:
          <span class="value">{{mpn}}</span>
        </div>
        <?php } ?>

        <?php if ($gtin) { ?>
        <div class="gtin">
          <?php echo language::translate('title_gtin', 'GTIN'); ?>:
          <span class="value">{{gtin}}</span>
        </div>
        <?php } ?>
      </div>
      <?php } ?>

      <div class="stock-status" style="margin: 1em 0;">
       <?php if ($quantity > 0) { ?>
        <div class="stock-available">
          <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
          <span class="value">{{stock_status}}</span>
        </div>

        <?php if ($delivery_status) { ?>
        <div class="stock-delivery">
          <?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>:
          <span class="value"><?php echo $delivery_status['name']; ?></span>
        </div>
        <?php } ?>

       <?php } else { ?>
        <?php if ($sold_out_status) { ?>
          <div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>">
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

      <hr />

      <div class="buy_now" style="margin: 1em 0;">
        <?php echo functions::form_draw_form_begin('buy_now_form', 'post'); ?>
        <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

        <?php if ($stock_options) { ?>
        <div class="form-group">
          <label><?php echo language::translate('text_select_desired_option', 'Select desired option'); ?></label>
          <?php echo form_draw_product_stock_options_list('stock_item_id', $product_id, true); ?>
        </div>
        <?php } ?>

        <div class="price-wrapper">
          <?php if ($campaign_price) { ?>
          <del class="regular-price">{{regular_price|money}}</del> <strong class="campaign-price">{{campaign_price|money}}</strong>
          <?php } else { ?>
          <span class="price">{{regular_price|money}}</span>
          <?php } ?>
        </div>

        <div class="tax" style="margin: 0 0 1em 0;">
         <?php if ($tax_rates) { ?>
          <?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <span class="total-tax">{{total_tax|money}}</span>
         <?php } else { ?>
          <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
         <?php } ?>
        </div>

        <?php if (!settings::get('catalog_only_mode')) { ?>
        <div class="form-group">
          <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
          <div style="display: flex">
            <div class="input-group" style="flex: 0 1 150px;">
              <?php echo (!empty($quantity_unit['decimals'])) ? functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 'min="'. ($quantity_min ? $quantity_min : '1') .'" max="'. ($quantity_max ? $quantity_max : '') .'" step="'. ($quantity_step ? $quantity_step : '') .'"') : functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, 'min="'. ($quantity_min ? $quantity_min : '1') .'" max="'. ($quantity_max ? $quantity_max : '') .'" step="'. ($quantity_step ? $quantity_step : '') .'"'); ?>
              <?php echo !empty($quantity_unit['name']) ? '<div class="input-group-text">'. $quantity_unit['name'] .'</div>' : ''; ?>
            </div>

            <div style="flex: 1 0 auto; padding-inline-start: 1em;">
              <?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity <= 0 && !$orderable) ? ' disabled' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
            </div>
          </div>
        </div>
        <?php } ?>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <hr />

      <div class="social-bookmarks">
        <a class="link" href="#"><?php echo functions::draw_fonticon('fa-link', 'style="color: #333;"'); ?></a>
        <a class="facebook" href="<?php echo document::href_link('https://www.facebook.com/sharer.php', ['u' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
        <a class="twitter" href="<?php echo document::href_link('https://twitter.com/intent/tweet/', ['text' => $name .' - '. $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
        <a class="pinterest" href="<?php echo document::href_link('https://pinterest.com/pin/create/button/', ['url' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
      </div>

    </div>
  </div>

  <?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') { ?>
  <?php if ($description || $technical_data) { ?>
  <div class="card" style="margin: var(--gutter-size) 0;">
    <div class="card-body">
      <div class="row layout" style="margin-bottom: 0;">

        <?php if ($description) { ?>
        <div class="col-md-<?php echo ($technical_data) ? 6 : 12; ?>">
          <h2 style="margin-top: 0;"><?php echo language::translate('title_description', 'Description'); ?></h2>

          <div class="description">
            {{description}}
          </div>
        </div>
        <?php } ?>

        <?php if ($technical_data) { ?>
        <div class="col-md-<?php echo ($description) ? 6 : 12; ?>">
          <h2 style="margin-top: 0;"><?php echo language::translate('title_technical_data', 'Technical Data'); ?></h2>

          <div class="technical-data" <?php echo (!$description) ? 'style="columns: 2 auto;"' : ''; ?>>
            <table class="table table-striped table-hover">
<?php
  foreach ($technical_data as $line) {
    if (preg_match('#[:\t]#', $line)) {
      @list($key, $value) = preg_split('#([:\t]+)#', $line, -1, PREG_SPLIT_NO_EMPTY);
      echo '  <tr>' . PHP_EOL
         . '    <td>'. trim($key) .'</td>' . PHP_EOL
         . '    <td>'. trim($value) .'</td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    } else if (trim($line) != '') {
      echo '  <thead>' . PHP_EOL
         . '    <tr>' . PHP_EOL
         . '      <th colspan="2">'. $line .'</th>' . PHP_EOL
         . '    </tr>' . PHP_EOL
         . '  </thead>' . PHP_EOL
         . '  <tbody>' . PHP_EOL;
    } else {
      echo ' </tbody>' . PHP_EOL
         . '</table>' . PHP_EOL
         . '<table class="table table-striped table-hover">' . PHP_EOL;
    }
  }
?>
              </table>
            </div>
          </div>
        <?php } ?>
      </div>

    </div>
  </div>
  <?php } ?>
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

  $('#box-product[data-id="<?php echo $product_id; ?>"] form[name=buy_now_form] input[name="stock_item_id"]').on('change', function(e) {

    var regular_price = <?php echo currency::format_raw($regular_price); ?>;
    var sales_price = <?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>;
    var tax = <?php echo currency::format_raw($total_tax); ?>;

    $(this).closest('.form-group').find('[data-toggle="dropdown"]').text( $(this).data('name') );

    if ($(this).data('price-adjustment')) regular_price += $(this).data('price-adjustment') || 0;
    if ($(this).data('price-adjustment')) sales_price += $(this).data('price-adjustment') || 0;
    if ($(this).data('tax-adjustment')) tax += $(this).data('tax-adjustment') || 0;

    $(this).find('.regular-price').text(regular_price.toMoney());
    $(this).find('.campaign-price').text(sales_price.toMoney());
    $(this).find('.price').text(sales_price.toMoney());
    $(this).find('.total-tax').text(tax.toMoney());
  });

  $('#box-product[data-id="{{product_id}}"] .social-bookmarks .link').off().click(function(e){
    e.preventDefault();
    prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '{{link}}');
  });
</script>
