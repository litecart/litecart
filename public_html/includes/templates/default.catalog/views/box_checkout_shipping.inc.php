<div id="box-checkout-shipping" class="box">
  <h2 class="title"><?php echo language::translate('title_shipping', 'Shipping'); ?></h2>

  <div class="options btn-group-vertical">

    <?php foreach ($options as $module) foreach ($module['options'] as $option) { ?>
    <label class="option btn btn-default btn-block<?php echo ($module['id'].':'.$option['id'] == $selected['id']) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">
      <?php echo functions::form_draw_radio_button('shipping[option_id]', $module['id'].':'.$option['id'], $selected['id'], 'style="display: none;"' . (!empty($option['error']) ? ' disabled="disabled"' : '')); ?>
      <div class="header row" style="margin: 0;">
        <div class="col-sm-fourths thumbnail" style="margin: 0;">
          <img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], 140, 60, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" />
        </div>
        <div class="col-sm-5 text-left">
          <h4 class="title" style="margin: 0.5em 0 0 0;"><?php echo $module['title']; ?></h4>
          <div class="name"><?php echo $option['name']; ?></div>
        </div>
        <div class="col-sm-thirds text-right">
          <div class="price"><?php echo (empty($option['error']) && $option['cost'] != 0) ? '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])) : language::translate('text_no_fee', 'No fee'); ?></div>
        </div>
      </div>

      <?php if (empty($option['error']) && (!empty($option['description']) || !empty($option['fields']))) { ?>
      <div class="content">
        <hr />
        <?php if (!empty($option['description'])) { ?><p class="description text-left"><?php echo $option['description']; ?></p><?php } ?>
        <?php if (!empty($option['fields'])) { ?><div class="fields text-left"><?php echo $option['fields']; ?></div><?php } ?>
      </div>
      <?php } ?>
    </label>
    <?php } ?>

  </div>
</div>

<script>
  $('#box-checkout-shipping .option.active :input').prop('disabled', false);
  $('#box-checkout-shipping .option:not(.active) :input').prop('disabled', true);
</script>