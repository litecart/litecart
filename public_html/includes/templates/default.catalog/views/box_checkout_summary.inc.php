<div id="box-checkout-summary" class="box">
  <h2 class="title"><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>

  <div id="order_confirmation-wrapper">

    <table class="table table-striped table-bordered data-table">

      <tbody>
        <?php foreach ($order_total as $row) { ?>
        <tr>
          <td class="text-right" colspan="5"><strong><?php echo $row['title']; ?>:</strong></td>
          <td class="text-right"><?php echo !empty(customer::$data['display_prices_including_tax']) ? currency::format($row['value'] + $row['tax'], false) : currency::format($row['value'], false); ?></td>
        </tr>
        <?php } ?>

        <?php if ($tax_total) { ?>
        <tr>
          <td class="text-right" style="color: #999999;" colspan="5"><?php echo $incl_excl_tax; ?>:</td>
          <td class="text-right" style="color: #999999;"><?php echo $tax_total; ?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="footer">
          <td class="text-right" colspan="5"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
          <td class="text-right" style="width: 25%;"><strong><?php echo currency::format($payment_due, false); ?></strong></td>
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
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } else { ?>
          <p class="terms-of-purchase text-center" style="font-size: 1.25em; margin-top: 0.5em;">
            <?php echo language::translate('checkout_summary:terms_of_purchase', 'By proceeding you hereby confirm and accept the Terms and Conditions of Purchase.'); ?>
          </p>
        <?php } ?>
      </div>

      <div class="col-md-fourths">
        <button class="btn btn-block btn-lg btn-success" type="submit" name="confirm_order" value="true"<?php echo !empty($error) ? ' disabled="disabled"' : ''; ?>><?php echo $confirm; ?></button>
      </div>
    </div>
  </div>
</div>