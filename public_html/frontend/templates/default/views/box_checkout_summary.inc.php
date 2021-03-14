<section id="box-checkout-summary" class="box">
  <h2 class="title"><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>

  <table class="table table-striped table-bordered data-table">
    <tbody>
      <?php foreach ($order->data['items'] as $item) { ?>
      <tr>
        <td class="text-left"><?php echo $item['quantity']; ?></td>
        <td class="text-left"><?php echo $item['name']; ?></td>
        <td class="text-right"><?php echo currency::format(!empty($order->data['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
        <td class="text-right"><?php echo currency::format((!empty($order->data['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price']) * $item['quantity'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <?php foreach ($order->data['order_total'] as $row) { ?>
      <tr>
        <td colspan="3" class="text-right"><strong><?php echo $row['title']; ?>:</strong></td>
        <td class="text-right"><?php echo currency::format(!empty($order->data['display_prices_including_tax']) ? $row['value'] + $row['tax'] : $row['value'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <?php if ($order->data['tax_total']) { ?>
      <tr>
        <td colspan="3" class="text-right" style="color: #999999;"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
        <td class="text-right" style="color: #999999;"><?php echo currency::format($order->data['tax_total'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
      </tr>
      <?php } ?>
    </tbody>

    <tfoot>
      <tr>
        <td colspan="3" class="text-right"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td class="text-right" style="width: 25%;"><strong><?php echo currency::format_html($order->data['payment_due'], false, $order->data['currency_code'], $order->data['currency_value']); ?></strong></td>
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