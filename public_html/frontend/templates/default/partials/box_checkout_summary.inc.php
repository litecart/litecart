<style>
#box-checkout-summary tr td:first-child {
  width: 75px;
}
.comments {
  position: relative;
}
.comments .remaining {
  position: absolute;
  bottom: .5em;
  right: .5em;
  color: #999;
}
</style>

<section id="box-checkout-summary">
  <h2 class="title"><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>

  <table class="table table-striped table-bordered data-table">
    <thead>
      <tr>
        <th class="text-start"><?php echo language::translate('title_item', 'Item'); ?></td>
        <th class="text-end"><?php echo language::translate('title_price', 'Price'); ?></td>
        <th class="text-end"><?php echo language::translate('title_discount', 'Discount'); ?></td>
        <th class="text-end"><?php echo language::translate('title_sum', 'Sum'); ?></td>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($shopping_cart['items'] as $item) { ?>
      <tr>
        <td class="text-start"><?php echo (float)$item['quantity']; ?> x <?php echo $item['name']; ?></td>
        <td class="text-end"><?php echo currency::format(!empty($shopping_cart['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></td>
        <td class="text-end">-<?php echo currency::format(!empty($shopping_cart['display_prices_including_tax']) ? $item['discount'] + $item['discount_tax'] : $item['discount'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></td>
        <td class="text-end"><?php echo currency::format(!empty($shopping_cart['display_prices_including_tax']) ? $item['sum'] + $item['sum_tax'] : $item['sum'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <tr>
        <td colspan="3" class="text-end"><strong><?php echo language::translate('title_subtotal', 'Subtotal'); ?>:</strong></td>
        <td class="text-end"><?php echo currency::format(!empty($shopping_cart['display_prices_including_tax']) ? $shopping_cart['subtotal'] + $shopping_cart['subtotal_tax'] : $shopping_cart['subtotal'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></td>
      </tr>

      <?php foreach ($shopping_cart['order_total'] as $row) { ?>
      <tr>
        <td colspan="3" class="text-end"><strong><?php echo $row['title']; ?>:</strong></td>
        <td class="text-end"><?php echo currency::format(!empty($shopping_cart['display_prices_including_tax']) ? $row['amount'] + $row['tax'] : $row['amount'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <?php if ($shopping_cart['total_tax']) { ?>
      <tr>
        <td colspan="3" class="text-end text-muted"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
        <td class="text-end text-muted"><?php echo currency::format($shopping_cart['total_tax'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></td>
      </tr>
      <?php } ?>
    </tbody>

    <tfoot>
      <tr>
        <td colspan="3" class="text-end"><strong><?php echo language::translate('title_total', 'Payment Due'); ?>:</strong></td>
        <td class="text-end" style="width: 25%;"><strong><?php echo currency::format_html($shopping_cart['total'], false, $shopping_cart['currency_code'], $shopping_cart['currency_value']); ?></strong></td>
      </tr>
    </tfoot>
  </table>

  <div class="comments form-group">
    <label><?php echo language::translate('title_comments', 'Comments'); ?></label>
    <?php echo functions::form_draw_textarea('comments', true, 'maxlength="512" rows="4"'); ?>
    <small class="remaining"></small>
  </div>

  <div class="confirm">
    <?php if ($error) { ?>
    <div class="alert alert-danger">{{error|escape}}</div>
    <?php } ?>

    <?php if (!$error && $consent) { ?>
    <div class="consent text-center" style="font-size: 1.25em; margin-top: 0.5em;">
      <?php echo '<label>'. functions::form_draw_checkbox('terms_agreed', ['1', $consent], true, 'required') .'</label>'; ?>
    </div>
    <?php } ?>

    <?php if (!$error) { ?>
    <button class="btn btn-block btn-lg btn-success" type="submit" name="confirm" value="true"<?php echo !empty($error) ? ' disabled' : ''; ?>>{{confirm}}</button>
    <?php } ?>
  </div>

</section>

<script>
  $('textarea[name="comments"][maxlength]').on('input', function() {
    var remaining = $(this).attr('maxlength') - $(this).val().length;
    $(this).closest('.comments').find('.remaining').text(remaining);
  });

  $('#box-checkout-summary button[name="confirm"]').click(function(e) {
    if ($('box-checkout-customer').prop('changed')) {
      e.preventDefault();
      alert("<?php echo language::translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>");
    }
  });

  $('form[name="checkout_form"]').submit(function(e) {
    var new_button = '<div class="btn btn-block btn-default btn-lg disabled"><?php echo functions::draw_fonticon('fa-spinner'); ?> <?php echo functions::general_escape_js(language::translate('text_please_wait', 'Please wait')); ?>&hellip;</div>';
    $('#box-checkout-summary button[name="confirm"]').css('display', 'none').before(new_button);
  });
</script>