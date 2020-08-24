<main id="main" class="container">
  <aside id="sidebar">
    <section id="box-checkout-region">

      <div class="row">
        <div class="form-group col-md-6">
          <small><?php echo language::translate('title_language', 'Language'); ?></small>
          <div style="line-height: 200%"><?php echo language::$selected['name']; ?></div>
        </div>

        <div class="form-group col-md-6">
          <small><?php echo language::translate('title_currency', 'Currency'); ?></small>
          <div style="line-height: 200%"><?php echo currency::$selected['code']; ?></div>
        </div>
      </div>

      <div class="form-group">
        <small><?php echo language::translate('title_country', 'Country'); ?></small>
        <div style="line-height: 200%"><?php echo reference::country(customer::$data['country_code'])->name; ?></div>
      </div>

      <div>
        <a class="btn btn-default change" href="<?php echo document::href_ilink('regional_settings', array('redirect_url' => document::link())); ?>" data-toggle="lightbox"><?php echo language::translate('title_change', 'Change'); ?></a>
      </div>
    </section>
  </aside>

  <div id="content">
    {snippet:notices}

    <?php echo functions::form_draw_form_begin('shopping_cart_form', 'post'); ?>

      <section id="box-checkout-cart" class="box">

        <h2 class="title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></h2>

        <ul class="items list-unstyled">
          <?php foreach ($items as $key => $item) { ?>
          <li class="item" data-id="<?php echo $item['product_id']; ?>" data-sku="<?php echo $item['sku']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo currency::format_raw($item['price']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">
            <div class="row">
              <div class="col-xs-8">

                <div class="row">
                  <div class="col-xs-4 col-md-2">
                    <a href="<?php echo htmlspecialchars($item['link']); ?>" class="thumbnail pull-left" style="max-width: 64px; margin-right: 1em;">
                      <img src="<?php echo document::href_link(WS_DIR_STORAGE . $item['thumbnail']); ?>" alt="" />
                    </a>
                  </div>

                  <div class="col-xs-8 col-md-10">
                    <div class="row">
                      <div class="col-md-6">
                        <div><strong><a href="<?php echo htmlspecialchars($item['link']); ?>" style="color: inherit;"><?php echo $item['name']; ?></a></strong></div>
                        <?php if (!empty($item['options'])) echo '<div class="options">'. implode('<br />', $item['options']) .'</div>'; ?>
                        <?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
                      </div>

                      <div class="col-md-6 text-center">
                        <div style="display: inline-flex;">
                          <?php if (!empty($item['quantity_unit']['name'])) { ?>
                          <div class="input-group" style="max-width: 150px;">
                            <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 0, null) : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 0, null); ?>
                            <span class="input-group-addon"><?php echo $item['quantity_unit']['name']; ?></span>
                          </div>
                          <?php } else { ?>
                            <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 0, null) : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 0, null, 'style="width: 125px;"'); ?>
                          <?php } ?>
                          <?php echo functions::form_draw_button('update_cart_item', array($key, functions::draw_fonticon('fa-refresh')), 'submit', 'title="'. htmlspecialchars(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-left: 0.5em;"'); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

              <div class="col-xs-2 text-right">
                <?php echo currency::format($item['display_price'] * $item['quantity']); ?>
              </div>

              <div class="col-xs-2 text-right">
                <td><?php echo functions::form_draw_button('remove_cart_item', array($key, functions::draw_fonticon('fa-trash')), 'submit', 'class="btn btn-danger" title="'. htmlspecialchars(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></td>
              </div>
            </div>
          </li>
          <?php } ?>
        </ul>

        <div class="subtotal">
          <?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <strong class="formatted-value"><?php echo !empty(customer::$data['display_prices_including_tax']) ?  currency::format($subtotal['value'] + $subtotal['tax']) : currency::format($subtotal['value']); ?></strong>
        </div>

      </section>

      <ul class="list-inline text-right">
        <li><a class="btn btn-success btn-lg" href="<?php echo document::href_ilink('checkout'); ?>"><?php echo language::translate('title_checkout', 'Checkout'); ?></a></li>
      </ul>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</main>
