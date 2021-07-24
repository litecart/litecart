<section id="box-checkout-summary" class="box">
  <h2 class="title"><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>

  <table class="table table-striped table-bordered data-table">
    <tbody>
      <?php foreach ($order['items'] as $item) { ?>
      <tr>
        <td class="text-start"><?php echo $item['quantity']; ?></td>
        <td class="text-start"><?php echo $item['name']; ?></td>
        <td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price'], false, $order['currency_code'], $order['currency_value']); ?></td>
        <td class="text-end"><?php echo currency::format((!empty($order['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price']) * $item['quantity'], false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <?php foreach ($order['order_total'] as $row) { ?>
      <tr>
        <td colspan="3" class="text-end"><strong><?php echo $row['title']; ?>:</strong></td>
        <td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $row['value'] + $row['tax'] : $row['value'], false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <?php if ($order['tax_total']) { ?>
      <tr>
        <td colspan="3" class="text-end text-muted"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
        <td class="text-end text-muted"><?php echo currency::format($order['tax_total'], false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>
    </tbody>

    <tfoot>
      <tr>
        <td colspan="3" class="text-end"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td class="text-end" style="width: 25%;"><strong><?php echo currency::format_html($order['payment_due'], false, $order['currency_code'], $order['currency_value']); ?></strong></td>
      </tr>
    </tfoot>
  </table>

  <div class="comments form-group">
    <label><?php echo language::translate('title_comments', 'Comments'); ?></label>
    <?php echo functions::form_draw_textarea('comments', true); ?>
  </div>

  <div class="confirm row">
    <div class="col-md-9">
      <?php if ($error) { ?>
      <div class="alert alert-danger">{{error|escape}}</div>
      <?php } ?>

      <?php if (!$error && $consent) { ?>
      <div class="consent text-center" style="font-size: 1.25em; margin-top: 0.5em;">
        <?php echo '<label>'. functions::form_draw_checkbox('terms_agreed', '1', true, 'required') .' '. $consent .'</label>'; ?>
      </div>
      <?php } ?>
    </div>

    <div class="col-md-3">
      <button class="btn btn-block btn-lg btn-success" type="submit" name="confirm_order" value="true"<?php echo !empty($error) ? ' disabled' : ''; ?>>{{confirm}}</button>
    </div>
  </div>
</section>

<script>
// Summary Form: Process Data

  $('#box-checkout-summary button[name="confirm_order"]').click(function(e) {
    if ($('box-checkout-customer').prop('changed')) {
      e.preventDefault();
      alert("<?php echo language::translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>");
    }
  });

  $('form[name="checkout_form"]').submit(function(e) {
    $('#box-checkout-summary button[name="confirm_order"]').css('display', 'none').before('<div class="btn btn-block btn-default btn-lg disabled"><?php echo functions::draw_fonticon('fa-spinner'); ?> <?php echo functions::general_escape_js(language::translate('text_please_wait', 'Please wait')); ?>&hellip;</div>');
  });
</script>