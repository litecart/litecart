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

  <div class="card">
    <div class="card-body">
      <div class="row layout" style="margin-bottom: 0;">
        <div class="col-sm-4 col-md-6">
          <div class="images row">

            <div class="col-12">
              <a class="main-image" href="<?php echo document::href_rlink($image); ?>" data-toggle="lightbox" data-gallery="product">
                <?php echo functions::draw_thumbnail($image, 320, 0, 'product', 'alt="'. functions::escape_html($name) .'"'); ?>
                {{sticker}}
              </a>
            </div>

            <?php foreach ($extra_images as $extra_image) { ?>
            <div class="col-4">
              <a class="extra-image" href="<?php echo document::href_rlink($extra_image); ?>" data-toggle="lightbox" data-gallery="product">
                <?php echo functions::draw_thumbnail($image, 160, $height, 'product', 'alt="'. functions::escape_html($name) .'"'); ?>
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
              <img src="<?php echo document::href_rlink($brand['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink($brand['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink($brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($brand['name']); ?>" />
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

          <?php if (isset($quantity_available)) { ?>
          <div class="stock-status" style="margin: 1em 0;">
            <?php if ($quantity_available > 0) { ?>
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
          <?php } ?>

          <fieldset class="buy_now" style="margin: 1em 0;">
            <?php echo functions::form_begin('buy_now_form', 'post'); ?>
            <?php echo functions::form_input_hidden('product_id', $product_id); ?>

            <?php if (count($stock_options) > 1) { ?>
            <div class="form-group">
              <label><?php echo language::translate('text_select_desired_option', 'Select desired option'); ?></label>
              <?php echo form_select_product_stock_option('stock_option_id', $product_id, true); ?>
            </div>
            <?php } else if (count($stock_options) == 1) { ?>
            <?php echo functions::form_input_hidden('stock_option_id', $stock_options[0]['stock_option_id']); ?>
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
                  <?php echo !empty($quantity_unit['decimals']) ? functions::form_input_decimal('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 'min="'. ($quantity_min ? $quantity_min : '1') .'" max="'. ($quantity_max ? $quantity_max : '') .'" step="'. ($quantity_step ? $quantity_step : '') .'"') : functions::form_input_number('quantity', isset($_POST['quantity']) ? true : 1, 'min="'. ($quantity_min ? $quantity_min : '1') .'" max="'. ($quantity_max ? $quantity_max : '') .'" step="'. ($quantity_step ? $quantity_step : '') .'"'); ?>
                  <?php if (!empty($quantity_unit['name'])) echo '<div class="input-group-text">'. $quantity_unit['name'] .'</div>'; ?>
                </div>

                <div style="flex: 1 0 auto; padding-inline-start: 1em;">
                  <?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity_available <= 0 && !$orderable) ? ' disabled' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
                </div>
              </div>
            </div>
            <?php } ?>

            <?php echo functions::form_end(); ?>
          </fieldset>

          <div class="social-bookmarks">
            <a class="link" href="#"><?php echo functions::draw_fonticon('fa-link', 'style="color: #333;"'); ?></a>
            <a class="facebook" href="<?php echo document::href_link('https://www.facebook.com/sharer.php', ['u' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
            <a class="twitter" href="<?php echo document::href_link('https://twitter.com/intent/tweet/', ['text' => $name .' - '. $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
            <a class="pinterest" href="<?php echo document::href_link('https://pinterest.com/pin/create/button/', ['url' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
          </div>

        </div>
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

          <div class="technical-data" <?php if (!$description) echo 'style="columns: 2 auto;"'; ?>>
            <table class="table table-striped table-hover">
<?php
  foreach ($technical_data as $line) {

    if (preg_match('#[:\t]#', $line)) {

      @list($key, $value) = preg_split('#([:\t]+)#', $line, -1, PREG_SPLIT_NO_EMPTY);

      echo implode(PHP_EOL, [
        '  <tr>',
        '    <td>'. trim($key) .'</td>',
        '    <td>'. trim($value) .'</td>',
        '  </tr>',
      ]);

    } else if (trim($line) != '') {
      echo implode(PHP_EOL, [
        '  <thead>',
        '    <tr>',
        '      <th colspan="2">'. $line .'</th>',
        '    </tr>',
        '  </thead>',
        '  <tbody>',
      ]);

    } else {
      echo implode(PHP_EOL, [
        ' </tbody>',
        '</table>',
        '<table class="table table-striped table-hover">',
      ]);
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
  $('#box-product[data-id="<?php echo $product_id; ?>"] form[name=buy_now_form] input[name="stock_option_id"]').on('change', function(e) {

    let $selected_option = $(this).closest('.dropdown').find(':input:checked');
    let regular_price = <?php echo currency::format_raw($regular_price); ?>;
    let sales_price = <?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>;
    let tax = <?php echo currency::format_raw($total_tax); ?>;

    $(this).closest('.form-group').find('[data-toggle="dropdown"]').text( $selected_option.data('name') );

    if ($selected_option.data('price-adjustment')) regular_price += $selected_option.data('price-adjustment') || 0;
    if ($selected_option.data('price-adjustment')) sales_price += $selected_option.data('price-adjustment') || 0;
    if ($selected_option.data('tax-adjustment')) tax += $selected_option.data('tax-adjustment') || 0;

    $(this).find('.regular-price').text(regular_price.toMoney());
    $(this).find('.campaign-price').text(sales_price.toMoney());
    $(this).find('.price').text(sales_price.toMoney());
    $(this).find('.total-tax').text(tax.toMoney());
  }).trigger('change');

  $('#box-product[data-id="{{product_id}}"] .social-bookmarks .link').off().click(function(e){
    e.preventDefault();
    prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '{{link}}');
  });
</script>
