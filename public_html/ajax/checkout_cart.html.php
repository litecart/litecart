<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once('../includes/app_header.inc.php');
    header('Content-type: text/html; charset='. $system->language->selected['charset']);
    $system->document->layout = 'ajax';
  }
  
  if ($system->cart->data['total']['items'] == 0) {
    echo '<p><em>'. $system->language->translate('description_no_items_in_cart', 'There are no items in your cart.') .'</em></p>';
    return;
  }
?>
<div style="margin-bottom: 10px;" id="box-checkout-cart">
  <div class="viewport" style="width: 560px; overflow: hidden; max-height: 200px; padding: 5px;">
    <div class="slides">
    <?php foreach ($system->cart->data['items'] as $key => $item) { ?>
      <?php echo $system->functions->form_draw_form_begin('cart_form') . $system->functions->form_draw_hidden_field('key', $key); ?>
        <div style="float: left; text-align: left; width: 580px;" class="slide">
          <a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $item['product_id'])); ?>" class="image-wrapper shadow"><img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $item['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 0, 150, 'FIT'); ?>" height="150" /></a>
          <div style="float: left; margin-left: 20px;">
            <p style="margin-top: 0;"><a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $item['product_id'])); ?>" style="color: inherit;"><strong><?php echo $item['name'][$system->language->selected['code']]; ?></strong></a>
            <?php echo $item['sku'] ? '<br /><span style="color: #999; font-size: 10px;">[' .$system->language->translate('title_sku', 'SKU') .': '. $item['sku'] .']</span>' : ''; ?></p>
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
            <p><?php echo $system->currency->format($system->tax->calculate($item['price'], $item['tax_class_id'])); ?></p>
            <p><?php echo $system->language->translate('title_quantity', 'Quantity'); ?>: <?php echo $system->functions->form_draw_number_field('quantity', $item['quantity'], ''); ?> &nbsp; <?php echo $system->functions->form_draw_button('update_cart_item', $system->language->translate('text_update', 'Update'), 'submit'); ?></p>
            <p><?php echo $system->functions->form_draw_button('remove_cart_item', $system->language->translate('text_remove', 'Remove'), 'submit'); ?></p>
          </div>
        </div>
      <?php echo $system->functions->form_draw_form_end(); ?>
    <?php } ?>
    </div>
  </div>
  <?php if (count($system->cart->data['items']) > 1) { ?>
  <div class="items">
    <?php foreach ($system->cart->data['items'] as $item) { ?>
    <a href="#" style="list-style:none; display:inline-block; text-align: center;"><img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $item['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 42, 32, 'FIT'); ?>" /></a>
    <?php } ?>
  </div>
  <?php } ?>
</div>

<?php if (count($system->cart->data['items']) > 1) { ?>
<script type="text/javascript">
    var totWidth=0;
    var positions = new Array();
    
    $('#box-checkout-cart .viewport .slides .slide').each(function(i){
      /* Traverse through all the slides and store their accumulative widths in totWidth */
      positions[i]= totWidth;
      totWidth += $(this).width();
    });
    
    $('#box-checkout-cart .viewport .slides').width(totWidth);
    
    /* Change the container div's width to the exact width of all the slides combined */
    $('#box-checkout-cart .items a').click(function(e,keepScroll){
        e.preventDefault();
        
        /* On a thumbnail click */
        $('#box-checkout-cart .items a').removeClass('act').addClass('inact');
        $(this).addClass('act');
        
        var pos = $(this).prevAll('#box-checkout-cart .items a').length;        
        $('#box-checkout-cart .viewport .slides').stop().animate({marginLeft:-positions[pos]+'px'},450);
        
        if (!keepScroll) clearInterval(itvl);
    });
    
    $('#box-checkout-cart .items a:first').addClass('act').siblings().addClass('inact');
    /* On page load, mark the first thumbnail as active */
    
    $('#box-checkout-cart').click(function(e,keepScroll) {
      if (!keepScroll) clearInterval(itvl);
    });
    
    var current=1;
    function autoAdvance() {
      if(current==-1) return false;
      $('#box-checkout-cart .items a').eq(current%$('#box-checkout-cart .items a').length).trigger('click',[true]);
      current++;
    }

    // The number of seconds that the slider will auto-advance in:
    var cartStepInterval = 3;
    if (itvl) clearInterval(itvl);
    var itvl = setInterval(function(){autoAdvance()}, cartStepInterval * 1000);
</script>
<?php } ?>

<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>