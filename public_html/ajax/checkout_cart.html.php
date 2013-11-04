<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once('../includes/app_header.inc.php');
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
  }
  
  if (cart::$data['total']['items'] == 0) {
    echo '<p><em>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</em></p>';
    return;
  }
?>
<div id="box-checkout-cart">
  <div class="viewport">
    <ul class="items">
    <?php foreach (cart::$data['items'] as $key => $item) { ?>
      <li class="item">
        <?php echo functions::form_draw_form_begin('cart_form') . functions::form_draw_hidden_field('key', $key); ?>
          <a href="<?php echo document::href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $item['product_id'])); ?>" class="image-wrapper shadow"><img src="<?php echo functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $item['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 0, 150, 'FIT'); ?>" height="150" /></a>
          <div>
            <p style="margin-top: 0px;"><a href="<?php echo document::href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $item['product_id'])); ?>" style="color: inherit;"><strong><?php echo $item['name'][language::$selected['code']]; ?></strong></a>
            <?php echo $item['sku'] ? '<br /><span style="color: #999; font-size: 10px;">[' .language::translate('title_sku', 'SKU') .': '. $item['sku'] .']</span>' : ''; ?></p>
<?php
  if (!empty($item['options'])) {
    echo '<p>';
    $use_br = false;
    foreach ($item['options'] as $k => $v) {
      if ($use_br) echo '<br />';
      echo $k .': '. $v;
      $use_br = true;
    }
    echo '</p>' . PHP_EOL;
  }
?>
            <p><?php echo currency::format(tax::calculate($item['price'], $item['tax_class_id'])); ?></p>
            <p><?php echo language::translate('title_quantity', 'Quantity'); ?>: <?php echo functions::form_draw_number_field('quantity', $item['quantity'], ''); ?> &nbsp; <?php echo functions::form_draw_button('update_cart_item', language::translate('text_update', 'Update'), 'submit'); ?></p>
            <p><?php echo functions::form_draw_button('remove_cart_item', language::translate('text_remove', 'Remove'), 'submit'); ?></p>
          </div>
        <?php echo functions::form_draw_form_end(); ?>
      </li>
    <?php } ?>
    </ul>
  </div>
  
  <?php if (count(cart::$data['items']) > 1) { ?>
  <ul class="shortcuts">
    <?php foreach (cart::$data['items'] as $item) { ?>
    <li class="shortcut">
      <a href="#" style="list-style:none; display:inline-block; text-align: center;"><img src="<?php echo functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $item['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 42, 32, 'FIT'); ?>" /></a>
    </li>
    <?php } ?>
  </ul>
  <?php } ?>
</div>

<?php if (count(cart::data['items']) > 1) { ?>
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
    
    if (!keepScroll) clearInterval(itvl);
  });
  
// On page load, mark the first thumbnail as active
  $('#box-checkout-cart .shortcut a:first').addClass('act').siblings().addClass('inact');
  
// Initiate the auto-advance timer
  var itvl = setInterval(function(){
    if (current == -1) return false;
    $('#box-checkout-cart .shortcut a').eq(current%$('#box-checkout-cart .shortcut a').length).trigger('click',[true]);
    current++;
  }, 3000);
  
// Clear timer
  $('#box-checkout-cart').click(function(e,keepScroll) {
    if (!keepScroll) clearInterval(itvl);
  });
</script>
<?php } ?>

<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>