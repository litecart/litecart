<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <section id="box-regional-settings" class="box box-default">

          <div class="row">
            <div class="form-group col-6">
              <small><?php echo language::translate('title_language', 'Language'); ?></small>
              <div style="line-height: 2;"><?php echo language::$selected['name']; ?></div>
            </div>

            <div class="form-group col-6">
              <small><?php echo language::translate('title_currency', 'Currency'); ?></small>
              <div style="line-height: 2;"><?php echo currency::$selected['code']; ?></div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-6">
              <small><?php echo language::translate('title_country', 'Country'); ?></small>
              <div style="line-height: 2;"><?php echo customer::$data['different_shipping_address'] ? reference::country(customer::$data['shipping_address']['country_code'])->name : reference::country(customer::$data['country_code'])->name; ?></div>
            </div>

            <div class="form-group col-6">
              <small><?php echo language::translate('title_postcode', 'Postcode'); ?></small>
              <div style="line-height: 2;"><?php echo customer::$data['different_shipping_address'] ? customer::$data['shipping_address'] : customer::$data['postcode']; ?></div>
            </div>
          </div>

          <div>
            <a class="btn btn-default change" href="<?php echo document::href_ilink('regional_settings', array('redirect_url' => document::link())); ?>" data-toggle="lightbox"><?php echo language::translate('title_change', 'Change'); ?></a>
          </div>
        </section>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}

        <?php echo functions::form_draw_form_begin('shopping_cart_form', 'post'); ?>

          <section id="box-shopping-cart" class="box box-default">

            <h2 class="title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></h2>

            <ul class="items list-unstyled">
              <?php foreach ($items as $key => $item) { ?>
              <li class="item" data-id="<?php echo $item['product_id']; ?>" data-sku="<?php echo $item['sku']; ?>" data-name="<?php echo functions::escape_html($item['name']); ?>" data-price="<?php echo currency::format_raw($item['price']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">
                <div class="row">
                  <div class="col-8">

                    <div class="row">
                      <div class="col-4 col-md-2">
                        <a href="<?php echo functions::escape_html($item['link']); ?>" class="thumbnail float-start" style="max-width: 64px; margin-inline-end: 1em;">
                          <img src="<?php echo document::href_link(WS_DIR_APP . $item['image']['thumbnail']); ?>" alt="" />
                        </a>
                      </div>

                      <div class="col-8 col-md-10">
                        <div class="row">
                          <div class="col-md-6">
                            <div><strong><a href="<?php echo functions::escape_html($item['link']); ?>" style="color: inherit;"><?php echo $item['name']; ?></a></strong></div>
                            <?php if (!empty($item['sku'])) echo '<div class="sku">'. $item['sku'] .'</div>'; ?>
                            <?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
                          </div>

                          <div class="col-md-6 text-center">
                            <div style="display: inline-flex;">
                              <?php if (!empty($item['quantity_unit']->name)) { ?>
                              <div class="input-group" style="max-width: 150px;">
                                <?php echo !empty($item['quantity_unit']->decimals) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']->decimals, 'min="0" max="'. ($item['quantity_max'] ? $item['quantity_max'] : '') .'" step="'. ($item['quantity_step'] ? $item['quantity_step'] : '') .'"') : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 'min="0" max="'. ($item['quantity_max'] ? $item['quantity_max'] : '') .'" step="'. ($item['quantity_step'] ? $item['quantity_step'] : '') .'"'); ?>
                                <?php echo $item['quantity_unit_name']; ?>
                              </div>
                              <?php } else { ?>
                                <?php echo !empty($item['quantity_unit']->decimals) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']->decimals, 'min="0"') : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 'min="0" style="width: 125px;"'); ?>
                              <?php } ?>
                              <?php echo functions::form_draw_button('update_cart_item', [$key, functions::draw_fonticon('fa-refresh')], 'submit', 'title="'. functions::escape_html(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-inline-start: 0.5em;"'); ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                  <div class="col-2 text-end">
                    <?php if ($item['price'] != $item['final_price']) { ?>
                    <del class="regular-price"><?php echo currency::format($item['price'] * $item['quantity']); ?></del> <strong class="final-price"><?php echo currency::format($item['final_price'] * $item['quantity']); ?></strong>
                    <?php } else { ?>
                    <span class="price"><?php echo currency::format($item['price'] * $item['quantity']); ?></span>
                    <?php } ?>
                  </div>

                  <div class="col-2 text-end">
                    <td><?php echo functions::form_draw_button('remove_cart_item', [$key, functions::draw_fonticon('fa-trash')], 'submit', 'class="btn btn-danger" title="'. functions::escape_html(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></td>
                  </div>
                </div>
              </li>
              <?php } ?>
            </ul>

            <div class="subtotal text-end">
              <?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <strong class="formatted-value"><?php echo !empty(customer::$data['display_prices_including_tax']) ?  currency::format($subtotal['value'] + $subtotal['tax']) : currency::format($subtotal['value']); ?></strong>
            </div>

            <div class="text-end">
              <a class="btn btn-success btn-lg" href="<?php echo document::href_ilink('checkout/index'); ?>"><?php echo language::translate('title_go_to_checkout', 'Go To Checkout'); ?> <?php echo functions::draw_fonticon('fa-chevron-right'); ?></a>
            </div>

          </section>

        <?php echo functions::form_draw_form_end(); ?>
    </div>
  </div>
</main>
