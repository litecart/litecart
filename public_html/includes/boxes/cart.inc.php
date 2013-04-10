<div id="cart">
  <strong><?php echo $system->language->translate('title_cart', 'Cart'); ?>:</strong>
  <span class="quantity"><?php echo $system->cart->data['total']['items']; ?></span> <?php echo $system->language->translate('text_items', 'item(s)'); ?>
  - <span class="formatted_value">
<?php
  if ($system->settings->get('display_prices_including_tax')) {
    echo $system->currency->format($system->cart->data['total']['value'] + $system->cart->data['total']['tax']);
  } else {
    echo $system->currency->format($system->cart->data['total']['value']);
  }
?>
  </span>
  <a href="<?php echo $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'); ?>" class="button"><?php echo $system->language->translate('title_checkout', 'Checkout'); ?></a>
</div>
<script>
  function updateCart() {
    $.ajax({
      url: '<?php echo $system->document->link(WS_DIR_AJAX .'cart.json.php'); ?>',
      type: 'get',
      cache: false,
      async: true,
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType("text/html;charset=<?php echo $system->language->selected['charset']; ?>");
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