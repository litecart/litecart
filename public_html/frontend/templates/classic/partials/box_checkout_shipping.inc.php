<section id="box-checkout-shipping" class="box">
  <h2 class="title"><?php echo language::translate('title_shipping', 'Shipping'); ?></h2>

  <div class="options btn-group-vertical">

    <?php foreach ($options as $key => $option) { ?>
    <label class="option btn btn-default btn-block<?php echo (!empty($selected) && $selected['module_id'] == $option['module_id'] && $selected['option_id'] == $option['option_id']) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">
      <?php echo functions::form_draw_radio_button('shipping_option_id', $key, !empty($selected['id']) ? $selected['id'] .':'. $selected['option_id'] : '', 'style="display: none;"' . (!empty($option['error']) ? ' disabled' : '')); ?>
      <div class="header row" style="margin: 0;">
        <div class="col-sm-4 thumbnail" style="margin: 0;">
          <img src="<?php echo document::href_link(WS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_APP . $option['icon'], 140, 80, 'FIT_ONLY_BIGGER_USE_WHITESPACING')); ?>" />
        </div>

        <div class="col-9 text-start" style="padding-bottom: 0;">
          <div class="title"><?php echo $option['title']; ?></div>
          <div class="price"><?php echo (empty($option['error']) && (float)$option['cost'] != 0) ? '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])) : language::translate('text_no_fee', 'No fee'); ?></div>
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
