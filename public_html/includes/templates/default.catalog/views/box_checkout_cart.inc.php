<div id="box-checkout-cart" class="box">

  <h2 class="title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></h2>

  <div class="table-responsive">
    <table class="items table table-striped data-table" style="width: 100%;">
      <thead>
        <tr class="item">
          <th><?php echo language::translate('title_item', 'Item'); ?></th>
          <th><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_price', 'Price'); ?></th>
          <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
          <th><?php echo language::translate('title_sum', 'Sum'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $key => $item) { ?>
        <tr class="item">
          <td><a href="<?php echo htmlspecialchars($item['link']); ?>" class="image-wrapper shadow"><img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" height="48" /></a></td>
          <td>
            <div><strong><a href="<?php echo htmlspecialchars($item['link']); ?>" style="color: inherit;"><?php echo $item['name']; ?></a></strong></div>
            <?php if (!empty($item['options'])) echo '<div class="options">'. implode('<br />', $item['options']) .'</div>'; ?>
            <?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
          </td>
          <td><?php echo currency::format($item['display_price']); ?></td>
          <td>
            <div style="display: flex;">
              <?php if (!empty($item['quantity_unit']['name'])) { ?>
              <div class="input-group" style="width: 125px;">
                <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 0, null) : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 0, null); ?>
                <span class="input-group-addon"><?php echo $item['quantity_unit']['name']; ?></span>
              </div>
              <?php } else { ?>
                <?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 0, null) : functions::form_draw_number_field('item['.$key.'][quantity]', $item['quantity'], 0, null, 'style="width: 125px;"'); ?>
              <?php } ?>
              <?php echo functions::form_draw_button('update_cart_item', array($key, functions::draw_fonticon('fa-refresh')), 'submit', 'title="'. htmlspecialchars(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-left: 0.5em;"'); ?>
            </div>
          </td>
          <td><?php echo currency::format($item['display_price'] * $item['quantity']); ?></td>
          <td><?php echo functions::form_draw_button('remove_cart_item', array($key, functions::draw_fonticon('fa-trash')), 'submit', 'class="btn btn-danger" title="'. htmlspecialchars(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3"></th>
          <td colspan="3" class="subtotal">
            <?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <strong class="formatted-value"><?php echo !empty(customer::$data['display_prices_including_tax']) ?  currency::format($subtotal + $subtotal_tax) : currency::format($subtotal); ?></strong>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>