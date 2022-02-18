<section id="box-checkout-payment" class="box">
  <h2 class="title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>

  <div class="options btn-group-vertical">

    <?php foreach ($options as $option) { ?>
    <label class="option btn btn-default btn-block<?php echo (!empty($selected) && $selected['module_id'] == $option['module_id'] && $selected['option_id'] == $option['option_id']) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">
      <?php echo functions::form_draw_radio_button('payment_option_id', $option['id'], !empty($selected['id']) ? $selected['id'] .':'. $selected['option_id'] : '', 'style="display: none;"' . (!empty($option['error']) ? ' disabled' : '')); ?>
      <div class="header row" style="margin: 0;">
        <div class="col-3 thumbnail" style="margin: 0;">
          <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_APP . $option['icon'], 140, 80, 'FIT_ONLY_BIGGER_USE_WHITESPACING')); ?>" />
        </div>

        <div class="col-9 text-start" style="padding-bottom: 0;">
          <div class="name"><?php echo $option['name']; ?></div>
          <div class="price"><?php echo (empty($option['error']) && (float)$option['fee'] != 0) ? '+ ' . currency::format(tax::get_price($option['fee'], $option['tax_class_id'])) : ''; ?></div>
          <?php if (!empty($option['error'])) { ?><div class="error"><?php echo $option['error']; ?></div><?php } ?>
        </div>
      </div>

      <?php if (empty($option['error']) && (!empty($option['description']) || !empty($option['fields']))) { ?>
      <div class="content">
        <hr />
        <?php if (!empty($option['description'])) { ?><p class="description text-start"><?php echo $option['description']; ?></p><?php } ?>
        <?php if (!empty($option['fields'])) { ?><div class="fields text-start"><?php echo $option['fields']; ?></div><?php } ?>
      </div>
      <?php } ?>
    </label>
    <?php } ?>

  </div>
</section>
