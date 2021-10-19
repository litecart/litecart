<section id="box-checkout-shipping" class="box box-default">
  <h2 class="title"><?php echo language::translate('title_shipping', 'Shipping'); ?></h2>

  <div class="options">

    <?php foreach ($options as $option) { ?>
    <label class="option text-start<?php echo (!empty($selected['id']) && $selected['id'] == $option['id']) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">

      <input name="shipping_option[id]" value="<?php echo htmlspecialchars($option['id']); ?>" type="radio" hidden<?php if (!empty($option['error'])) echo ' disabled'; ?><?php if (!empty($selected['id'])) echo ' checked'; ?> />

      <div class="header row" style="margin: 0;">
        <div class="col-sm-4 thumbnail">
          <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_STORAGE . $option['icon'], 160, 80, 'FIT_ONLY_BIGGER_USE_WHITESPACING')); ?>" />
        </div>

        <div class="col-sm-8">
          <h3 class="title"><?php echo $option['title']; ?></h3>

          <?php if (!empty($option['description'])) { ?>
          <p class="description"><?php echo $option['description']; ?></p>
          <?php } ?>

          <div class="price"><?php echo (empty($option['error']) && $option['cost'] != 0) ? '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])) : language::translate('text_no_fee', 'No fee'); ?></div>
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
  $('#box-checkout-shipping .option:not(.active) .content :input').prop('disabled', true);

// Shipping Form: Process Data

  $('#box-checkout-shipping').on('change', '.option:not(.active) input[name="payment_option[id]"]', function(e){

    $('#box-checkout-shipping .option').removeClass('active');
    $(this).closest('.option').addClass('active');

    $('#box-checkout-shipping .option:not(.active) .content :input').prop('disabled', true);
    $(this).closest('.option').find('.content :input').prop('disabled', false);

    var formdata = $('#box-checkout-shipping .option.active :input').serialize();

    $('#box-checkout').trigger('update', [{component: 'shipping', data: formdata + '&select_shipping=true', refresh: false}])
                      .trigger('update', [{component: 'payment', refresh: true}])
                      .trigger('update', [{component: 'summary', refresh: true}]);
  });
</script>