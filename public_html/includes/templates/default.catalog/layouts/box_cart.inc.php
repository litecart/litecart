<div id="cart">
  <a href="<?php echo $href; ?>" class="image"><img src="<?php echo WS_DIR_IMAGES; ?>icons/32x32/cart.png" alt="" /></a>
  <a href="<?php echo $href; ?>" class="content">
    <strong><?php echo language::translate('title_cart', 'Cart'); ?>:</strong><br />
    <span class="quantity"><?php echo $num_items; ?></span> <?php echo language::translate('text_items', 'item(s)'); ?> - <span class="formatted_value"><?php echo $cart_total; ?></span>
  </a>
</div>
<script>
  function updateCart() {
    $.ajax({
      url: '<?php echo document::ilink('ajax/cart.json'); ?>',
      type: 'get',
      cache: false,
      async: true,
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo language::$selected['charset']; ?>");
      },
      error: function(jqXHR, textStatus, errorThrown) {
        //alert('Error');
      },
      success: function(data) {
        $('#cart .quantity').html(data['quantity']);
        $('#cart .formatted_value').html(data['formatted_value']);
      },
      complete: function() {
        $('*').css('cursor', '');
      }
    });
  }
  var timerCart = setInterval("updateCart()", 60000); // Keeps session alive
</script>