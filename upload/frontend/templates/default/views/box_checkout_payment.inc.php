<section id="box-checkout-payment" class="box box-default">
  <h2 class="title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>

  <div class="options">

    <?php foreach ($options as $id => $option) { ?>
    <label class="option text-start<?php echo (!empty($selected['id']) && $selected['id'] == $id) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">
      <?php echo functions::form_draw_radio_button('payment[option_id]', $id, !empty($selected['id']) ? $selected['id'] : '', 'style="display: none;"' . (!empty($option['error']) ? ' disabled' : '')); ?>
      <div class="header row" style="margin: 0;">
        <div class="col-sm-4 thumbnail" style="margin: 0;">
          <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_STORAGE . $option['icon'], 160, 80, 'FIT_ONLY_BIGGER_USE_WHITESPACING')); ?>" />
        </div>

        <div class="col-sm-8" style="padding-bottom: 0;">
          <h3 class="title"><?php echo $option['title']; ?></h3>

          <?php if (!empty($option['description'])) { ?>
          <p class="description"><?php echo $option['description']; ?></p>
          <?php } ?>

          <div class="price"><?php echo (empty($option['error']) && $option['cost'] != 0) ? '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])) : ''; ?></div>
          <?php if (!empty($option['error'])) { ?><div class="error"><?php echo $option['error']; ?></div><?php } ?>
        </div>
      </div>

      <?php if (empty($option['error']) && !empty($option['fields'])) { ?>
      <div class="content">
        <hr />
        <div class="fields text-start"><?php echo $option['fields']; ?></div>
      </div>
      <?php } ?>
    </label>
    <?php } ?>

  </div>
</section>

<script>
  $('#box-checkout-payment .option.active :input').prop('disabled', false);
  $('#box-checkout-payment .option:not(.active) :input').prop('disabled', true);

// Payment Form: Process Data

  $('#box-checkout-payment .option:not(.active):not(.disabled)').click(function(e){

    $('#box-checkout-payment .option').removeClass('active');
    $(this).find('input[name="payment[option_id]"]').prop('checked', true);
    $(this).addClass('active');

    $('#box-checkout-payment .option.active .fields :input').prop('disabled', false);
    $('#box-checkout-payment .option:not(.active) .fields :input').prop('disabled', true);

    var formdata = $('#box-checkout-payment .option.active :input').serialize();

    $('#box-checkout').trigger('update', [{component: 'payment', data: formdata, refresh: false}])
                      .trigger('update', [{component: 'summary', refresh: true}]);
  });
</script>