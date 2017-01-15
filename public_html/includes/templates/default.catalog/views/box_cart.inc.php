<div id="cart">
  <a href="<?php echo htmlspecialchars($link); ?>" class="image">
    <img src="{snippet:template_path}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="" />
    <div class="content">
      <div class="title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></div>
      <div><span class="quantity"><?php echo $num_items; ?></span> <?php echo language::translate('text_items', 'item(s)'); ?> - <span class="formatted_value"><?php echo $cart_total; ?></span></div>
    </div>
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
        if (data['quantity'] > 0) {
          $('#cart img').attr('src', '{snippet:template_path}images/cart_filled.svg');
        } else {
          $('#cart img').attr('src', '{snippet:template_path}images/cart.svg');
        }
      },
      complete: function() {
        $('*').css('cursor', '');
      }
    });
  }
  var timerCart = setInterval("updateCart()", 60000); // Keeps session alive
</script>