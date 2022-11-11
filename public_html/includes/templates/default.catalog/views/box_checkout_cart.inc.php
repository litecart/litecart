<section id="box-checkout-cart" class="card">

  <div class="card-header">
    <h2 class="card-title">
      <?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?>
    </h2>
  </div>

  <div class="headings hidden-xs">
    <div class="row">
      <div class="col-sm-8">
        <?php echo language::translate('title_item', 'Item'); ?>
      </div>

      <div class="hidden-xs col-sm-2 text-end">
        <?php echo language::translate('title_price', 'Price'); ?>
      </div>

      <div class="col-sm-2 text-end">
        <?php echo language::translate('title_sum', 'Sum'); ?>
      </div>
    </div>
  </div>

  <ul class="items list-unstyled">
    <?php foreach ($items as $key => $item) { ?>
    <li class="item" data-id="<?php echo $item['product_id']; ?>" data-sku="<?php echo $item['sku']; ?>" data-name="<?php echo functions::escape_html($item['name']); ?>" data-price="<?php echo currency::format_raw($item['price']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">

      <div class="row">
        <div class="col-xs-3 col-sm-2 col-md-1">
          <a href="<?php echo functions::escape_html($item['link']); ?>" class="thumbnail float-start" style="margin-inline-end: 1em;">
            <img class="img-responsive" src="<?php echo document::href_link(WS_DIR_APP . $item['image']['thumbnail']); ?>" alt="" />
          </a>
        </div>

        <div class="col-xs-9 col-sm-10 col-md-11">

          <div class="row">
            <div class="col-sm-4">

              <div class="name"><a href="<?php echo functions::escape_html($item['link']); ?>" style="color: inherit;"><?php echo $item['name']; ?></a></div>

              <?php if (!empty($item['options'])) echo '<small class="options">'. implode('<br />', $item['options']) .'</small>'; ?>
              <?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
            </div>

            <div class="col-sm-4">
              <div style="display: inline-flex;">
                <div class="input-group" style="max-width: 175px;">
                <?php if (!empty($item['quantity_unit']['name'])) { ?>
                  <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], $item['quantity_min'], $item['quantity_max'], $item['quantity_step'] ? 'step="'. (float)$item['quantity_step'] .'"' : '') : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_min'], $item['quantity_max'], $item['quantity_step'] ? 'step="'. (float)$item['quantity_step'] .'"' : ''); ?>
                  <span class="input-group-text"><?php echo $item['quantity_unit']['name']; ?></span>
                <?php } else { ?>
                  <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 'min="0"') : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 'min="0" style="width: 125px;"'); ?>
                <?php } ?>
                </div>
                <?php echo functions::form_draw_button('update_cart_item', [$key, functions::draw_fonticon('fa-refresh')], 'submit', 'title="'. functions::escape_html(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-inline-start: 0.5em;"'); ?>

                <div style="margin-inline-start: 1em;"><?php echo functions::form_draw_button('remove_cart_item', [$key, functions::draw_fonticon('fa-trash')], 'submit', 'class="btn btn-danger" title="'. functions::escape_html(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></div>
              </div>
            </div>

            <div class="hidden-xs col-sm-2">
              <div class="unit-price text-end">
                <?php echo currency::format($item['display_price']); ?>
              </div>
            </div>

            <div class="col-sm-2">
              <div class="total-price text-xs-left text-sm-end">
                <?php echo currency::format($item['display_price'] * $item['quantity']); ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </li>
    <?php } ?>
  </ul>

  <div class="card-footer subtotal text-end">
    <?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <strong class="formatted-value"><?php echo !empty(customer::$data['display_prices_including_tax']) ?  currency::format(cart::$total['value'] + cart::$total['tax']) : currency::format_html(cart::$total['value']); ?></strong>
  </div>
</section>