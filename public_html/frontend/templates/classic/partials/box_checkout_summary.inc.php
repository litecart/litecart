<style>
.input-wrapper {
  position: relative;
}
.input-wrapper .remaining {
  position: absolute;
  bottom: .5em;
  right: .5em;
  color: #999;
}
</style>

<section id="box-checkout-summary" class="card">
  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>
  </div>

  <table class="table table-striped table-bordered data-table">
    <tbody>
      <?php foreach ($order_total as $row) { ?>
      <tr>
        <td class="text-end" style="white-space: normal;"><strong><?php echo $row['title']; ?>:</strong></td>
        <td class="text-end"><?php echo !empty(customer::$data['display_prices_including_tax']) ? currency::format($row['value'] + $row['tax'], false) : currency::format($row['value'], false); ?></td>
      </tr>
      <?php } ?>

      <?php if ($tax_total) { ?>
      <tr>
        <td class="text-end" style="color: #999;"><?php echo $incl_excl_tax; ?>:</td>
        <td class="text-end" style="color: #999;"><?php echo $tax_total; ?></td>
      </tr>
      <?php } ?>
    </tbody>

    <tfoot>
      <tr>
        <td class="text-end"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td class="text-end" style="width: 25%;"><strong><?php echo currency::format_html($payment_due, false); ?></strong></td>
      </tr>
    </tfoot>
  </table>

  <div class="card-body">
    <div class="comments form-group">
      <label><?php echo language::translate('title_comments', 'Comments'); ?></label>
      <div class="input-wrapper">
        <?php echo functions::form_input_textarea('comments', true, 'maxlength="512" rows="4"'); ?>
        <small class="remaining"></small>
      </div>
    </div>

    <div class="confirm row" style="margin-bottom: 0;">
      <div class="col-md-9" style="align-self: center;">
        <?php if ($error) { ?>
        <div class="error"><?php echo functions::escape_html($error); ?></div>
        <?php } ?>

        <?php if (!$error && $consent) { ?>
        <div class="consent text-center" style="font-size: 1.25em; margin-top: 0.5em; margin-bottom: 0;">
          <?php echo '<label>'. functions::form_checkbox('terms_agreed', '1', true, 'required') .' '. $consent .'</label>'; ?>
        </div>
        <?php } ?>
      </div>

      <div class="col-md-3">
        <button class="btn btn-block btn-lg btn-success" type="submit" name="confirm_order" value="true"<?php if (!empty($error)) echo ' disabled'; ?>><?php echo $confirm; ?></button>
      </div>
    </div>
  </div>
</section>

<script>
  $('textarea[maxlength]').on('input', function() {
    var remaining = $(this).attr('maxlength') - $(this).val().length;
    $(this).closest('.input-wrapper').find('.remaining').text(remaining);
  });
</script>