<div id="cart">
  <a href="<?php echo htmlspecialchars($link); ?>" class="image"><img src="{snippet:template_path}images/<?php echo !empty($num_items) ? 'cart_filled.png' : 'cart.png'; ?>" alt="" /></a>
  <a href="<?php echo htmlspecialchars($link); ?>" class="content">
    <strong><?php echo language::translate('title_cart', 'Cart'); ?>:</strong><br />
    <span class="quantity"><?php echo $num_items; ?></span> <?php echo language::translate('text_items', 'item(s)'); ?> - <span class="formatted_value"><?php echo $cart_total; ?></span>
  </a><br />
  <a href="<?php echo htmlspecialchars($link); ?>" class="link"><?php echo language::translate('title_checkout', 'Checkout'); ?> &raquo;</a>
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
        if (data['quantity'] > 0) {
          $('#cart img').attr('src', '{snippet:template_path}images/cart_filled.png');
        } else {
          $('#cart img').attr('src', '{snippet:template_path}images/cart.png');
        }
      },
      complete: function() {
        $('*').css('cursor', '');
      }
    });
  }
  var timerCart = setInterval("updateCart()", 60000); // Keeps session alive
</script>