<div id="cart">
<?php
  echo '<strong>'. $system->language->translate('title_cart', 'Cart') .':</strong> ';
  
  echo sprintf($system->language->translate('text_x_items', '%d item(s)'), $system->cart->data['total']['items']);
  
  if ($system->settings->get('display_prices_including_tax')) {
    echo ' - '. $system->currency->format($system->cart->data['total']['value'] + $system->cart->data['total']['tax']);
  } else {
    echo ' - '. $system->currency->format($system->cart->data['total']['value']);
  }
  
  //echo ' '. $system->functions->form_draw_button('clear_cart_items', $system->language->translate('title_reset', 'Reset'), 'button')
  echo ' '. $system->functions->form_draw_button('checkout', $system->language->translate('title_checkout', 'Checkout'), 'button', 'onclick="location=\''. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php') .'\'"') . $system->functions->form_draw_form_end();
?>
<!--
<script type="text/javascript">
  $("button[name=clear_cart_items]").live('click', function() {
    var form = $('<?php echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_form_begin('clear_cart_form', 'post') . $system->functions->form_draw_hidden_field('clear_cart_items', 'true') . $system->functions->form_draw_form_end()); ?>');
    $(document.body).append(form);
    form.submit();
  });
</script>
-->
</div>