<div id="box-checkout-cart">
  <div class="viewport">
    <ul class="items">
    <?php foreach ($items as $key => $item) { ?>
      <li class="item">
        <?php echo functions::form_draw_form_begin('cart_form') . functions::form_draw_hidden_field('key', $key); ?>
          <a href="<?php echo htmlspecialchars($item['link']); ?>" class="image-wrapper shadow"><img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="" /></a>
          <div style="display: inline-block;">
            <p style="margin-top: 0px;"><a href="<?php echo htmlspecialchars($item['link']); ?>" style="color: inherit;"><strong><?php echo $item['name']; ?></strong></a>
            <?php echo $item['sku'] ? '<br /><span style="color: #999; font-size: 10px;">['. language::translate('title_sku', 'SKU') .': '. $item['sku'] .']</span>' : ''; ?></p>
            <?php if (!empty($item['options'])) echo '<p>'. implode('<br />', $item['options']) .'</p>' . PHP_EOL; ?>
            <p><?php echo currency::format(tax::get_price($item['price'], $item['tax_class_id'])); ?></p>
            <p><?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_draw_decimal_field('quantity', $item['quantity'], $item['quantity_unit']['decimals'], 0, null, 'style="width: 90px;"') : functions::form_draw_number_field('quantity', $item['quantity'], 0, null, 'style="width: 70px;"'); ?> <?php echo $item['quantity_unit']['name']; ?> &nbsp; <?php echo functions::form_draw_button('update_cart_item', language::translate('text_update', 'Update'), 'submit'); ?></p>
            <p><?php echo functions::form_draw_button('remove_cart_item', language::translate('text_remove', 'Remove'), 'submit'); ?></p>
          </div>
        <?php echo functions::form_draw_form_end(); ?>
      </li>
    <?php } ?>
    </ul>
  </div>

  <?php if (count($items) > 1) { ?>
  <ul class="shortcuts">
    <?php foreach ($items as $item) { ?>
    <li class="shortcut">
      <a href="#" style="list-style:none; display:inline-block; text-align: center;"><img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="" style="max-width: 42px; max-height: 32px" /></a>
    </li>
    <?php } ?>
  </ul>
  <script>
    var current = 1;
    var totalWidth = 0;
    var positions = new Array();

  // Traverse through all the slides and store their accumulative widths in totalWidth
    $('#box-checkout-cart .viewport .items .item').each(function(i) {
      positions[i] = totalWidth;
      totalWidth += $(this).width();
    });

    $('#box-checkout-cart .viewport .items').width(totalWidth);

  // On a thumbnail click - Change the container's width to the exact width of all the slides combined
    $('#box-checkout-cart .shortcut a').click(function(e,keepScroll) {
      e.preventDefault();

      $('#box-checkout-cart .shortcut a').removeClass('act').addClass('inact');
      $(this).addClass('act');

      var index = $('#box-checkout-cart .shortcut a').index(this);
      $('#box-checkout-cart .viewport .items').stop().animate({marginLeft:-positions[index]+'px'}, 400); // slide
      //$('#box-checkout-cart .viewport .items').stop().fadeOut('fast').animate({marginLeft: -positions[index]+'px'}, 0).fadeIn('fast'); // fade

      if (!keepScroll) clearInterval(timer_cart_scroll);
    });

  // On page load, mark the first thumbnail as active
    $('#box-checkout-cart .shortcut a:first').addClass('act').siblings().addClass('inact');

  // Initiate the auto-advance timer
    if (!timer_cart_scroll) {
      var timer_cart_scroll = setInterval(function(){
        if (current == -1) return false;
        $('#box-checkout-cart .shortcut a').eq(current%$('#box-checkout-cart .shortcut a').length).trigger('click',[true]);
        current++;
      }, 3000);
    }

  // Clear timer
    $('#box-checkout-cart').click(function(e,keepScroll) {
      if (!keepScroll) clearInterval(timer_cart_scroll);
    });
  </script>
  <?php } ?>
</div>