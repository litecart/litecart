<?php
  $currency_options = ['' => '-- '. language::translate('title_select', 'Select') .' --'];
  foreach ($currencies as $currency) {
    $currency_options[$currency['code']] = $currency['name'];
  }

  $language_options = ['' => '-- '. language::translate('title_select', 'Select') .' --',];
  foreach ($languages as $language) {
    $language_options[$language['code']] = $language['name'];
  }
?>
<main id="main">
  <aside id="sidebar">
    <section id="box-checkout-region">

      <h2 class="title"><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></h2>
      <?php echo functions::form_draw_form_begin('region_form', 'post', document::ilink('regional_settings', ['redirect_url' => document::link()]), false, 'style="max-width: 480px;"'); ?>

        <?php if (count($languages) > 1) { ?>
        <div class="form-group">
          <label><?php echo language::translate('title_language', 'Language'); ?></label>
          <?php echo functions::form_draw_select_field('language_code', $language_options, language::$selected['code']); ?>
        </div>
        <?php } ?>

        <?php if (count($currencies) > 1) { ?>
        <div class="form-group">
          <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
          <?php echo functions::form_draw_select_field('currency_code', $currency_options, currency::$selected['code']); ?>
        </div>
        <?php } ?>

        <div class="form-group">
          <label><?php echo language::translate('title_country', 'Country'); ?></label>
          <?php echo functions::form_draw_countries_list('country_code', customer::$data['country_code']); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
          <?php echo functions::form_draw_zones_list('zone_code', customer::$data['country_code'], customer::$data['zone_code']); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_postcode', 'Postcode'); ?></label>
          <?php echo functions::form_draw_text_field('postcode', customer::$data['postcode']); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_display_prices_including_tax', 'Display Prices Including Tax'); ?></label>
          <?php echo functions::form_draw_toggle('display_prices_including_tax', 'y/n', customer::$data['display_prices_including_tax'] ? '1' : '0'); ?>
        </div>

        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-default btn-block"'); ?>

      <?php echo functions::form_draw_form_end(); ?>
    </section>
  </aside>

  <div id="content">
    {{notices}}

    <?php echo functions::form_draw_form_begin('shopping_cart_form', 'post'); ?>

      <section id="box-checkout-cart">

        <h2 class="title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></h2>

        <ul class="items list-unstyled">
          <?php foreach ($items as $key => $item) { ?>
          <li class="item" data-id="<?php echo $item['product_id']; ?>" data-sku="<?php echo $item['sku']; ?>" data-name="<?php echo functions::escape_html($item['name']); ?>" data-price="<?php echo currency::format_raw($item['price']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">
            <div class="row">
              <div class="col-8">

                <div class="row">
                  <div class="col-4 col-md-2">
                    <a href="<?php echo functions::escape_html($item['link']); ?>" class="thumbnail float-start" style="max-width: 64px; margin-inline-end: 1em;">
                      <img src="<?php echo document::href_link(WS_DIR_STORAGE . $item['image']['thumbnail']); ?>" alt="" />
                    </a>
                  </div>

                  <div class="col-8 col-md-10">
                    <div class="row">
                      <div class="col-md-6">
                        <div><strong><a href="<?php echo functions::escape_html($item['link']); ?>" style="color: inherit;"><?php echo $item['name']; ?></a></strong></div>
                        <?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
                      </div>

                      <div class="col-md-6 text-center">
                        <div style="display: inline-flex;">
                          <?php if (!empty($item['quantity_unit']['name'])) { ?>
                          <div class="input-group" style="max-width: 150px;">
                            <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 'min="0"') : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 'min="0"'); ?>
                            <?php echo $item['quantity_unit']['name']; ?>
                          </div>
                          <?php } else { ?>
                            <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 'min="0"') : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 'min="0" style="width: 125px;"'); ?>
                          <?php } ?>
                          <?php echo functions::form_draw_button('update_cart_item', [$key, functions::draw_fonticon('fa-refresh')], 'submit', 'title="'. functions::escape_html(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-inline-start: 0.5em;"'); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

              <div class="col-2 text-end">
                <?php echo currency::format($item['display_price'] * $item['quantity']); ?>
              </div>

              <div class="col-2 text-end">
                <td><?php echo functions::form_draw_button('remove_cart_item', [$key, functions::draw_fonticon('fa-trash')], 'submit', 'class="btn btn-danger" title="'. functions::escape_html(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></td>
              </div>
            </div>
          </li>
          <?php } ?>
        </ul>

        <div class="subtotal text-end">
          <?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <strong class="formatted-value"><?php echo !empty($display_prices_including_tax) ?  currency::format($subtotal + $subtotal_tax) : currency::format($subtotal); ?></strong>
        </div>

      </section>

      <ul class="list-inline text-end">
        <li><a class="btn btn-success btn-lg" href="<?php echo document::href_ilink('checkout/index'); ?>"><?php echo language::translate('title_checkout', 'Checkout'); ?></a></li>
      </ul>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</main>
