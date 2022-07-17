<style>
input[name="payment_option[id]"]:checked + .option::after {
  content: '<?php echo language::translate('title_selected', 'Selected'); ?>';
}
</style>

<section id="box-checkout-payment" class="">
  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>
  </div>

  <div class="card-body">
    <div class="options">

      <?php foreach ($options as $option) { ?>
      <label class="option-wrapper">
        <input name="payment_option[id]" value="<?php echo functions::escape_html($option['id']); ?>" type="radio" hidden<?php if (!empty($option['error'])) echo ' disabled'; ?><?php if (!empty($selected['id'])) echo ' checked'; ?> />
        <div class="option">
          <div class="header row" style="margin: 0;">
            <div class="col-sm-2" style="margin: 0;">
              <img class="thumbnail fit" src="<?php echo document::href_rlink(functions::image_thumbnail(FS_DIR_STORAGE . $option['icon'], 160, 80)); ?>"  style="aspect-ratio: 2/1;" />
            </div>

            <div class="col-sm-10" style="align-self: center;">
              <div class="name"><?php echo $option['name']; ?></div>

              <?php if (!empty($option['description'])) { ?>
              <div class="description"><?php echo $option['description']; ?></div>
              <?php } ?>

              <div class="price"><?php echo (empty($option['error']) && $option['fee'] != 0) ? '+ ' . currency::format(tax::get_price($option['fee'], $option['tax_class_id'])) : ''; ?></div>
              <?php if (!empty($option['error'])) { ?><div class="error"><?php echo $option['error']; ?></div><?php } ?>
            </div>
          </div>

          <?php if (empty($option['error']) && !empty($option['fields'])) { ?>
          <div class="content">
            <hr />
            <div class="fields text-start"><?php echo $option['fields']; ?></div>
          </div>
          <?php } ?>
        </div>
      </label>
      <?php } ?>
    </div>

  </div>
</section>

<script>
  $('#box-checkout-payment .option:not(.active) .content :input').prop('disabled', true);

// Payment Form: Process Data

  $('#box-checkout-payment').on('change', '.option:not(.active) input[name="payment_option[id]"]', function(e){

    $('#box-checkout-payment .option').removeClass('active');
    $(this).closest('.option').addClass('active');

    $('#box-checkout-payment .option:not(.active) .content :input').prop('disabled', true);
    $(this).closest('.option').find('.content :input').prop('disabled', false);

    var formdata = $('#box-checkout-payment .option.active :input').serialize();

    $('#box-checkout').trigger('update', [{component: 'payment', data: formdata + '&select_payment=true', refresh: false}])
                      .trigger('update', [{component: 'summary', refresh: true}]);
  });
</script>