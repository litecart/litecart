<section id="box-checkout-shipping" class="box white">
  <h2 class="title"><?php echo language::translate('title_shipping', 'Shipping'); ?></h2>

  <div class="options btn-group-vertical">

    <?php foreach ($options as $module) foreach ($module['options'] as $option) { ?>
    <label class="option btn btn-default btn-block text-left<?php echo (!empty($selected['id']) && $selected['id'] == $module['id'].':'.$option['id']) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">
      <?php echo functions::form_draw_radio_button('shipping[option_id]', $module['id'].':'.$option['id'], !empty($selected['id']) ? $selected['id'] : '', 'style="display: none;"' . (!empty($option['error']) ? ' disabled' : '')); ?>
      <div class="header row" style="margin: 0;">
        <div class="col-sm-4 thumbnail" style="margin: 0;">
          <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . $option['icon'], 160, 80, 'FIT_ONLY_BIGGER_USE_WHITESPACING')); ?>" />
        </div>

        <div class="col-sm-8" style="padding-bottom: 0;">
          <h3 class="title"><?php echo $module['title']; ?></h3>

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
        <div class="fields text-left"><?php echo $option['fields']; ?></div>
      </div>
      <?php } ?>
    </label>
    <?php } ?>

  </div>
</section>

<script>
  $('#box-checkout-shipping .option.active :input').prop('disabled', false);
  $('#box-checkout-shipping .option:not(.active) :input').prop('disabled', true);
</script>