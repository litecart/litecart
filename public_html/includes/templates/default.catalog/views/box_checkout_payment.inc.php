<div id="box-checkout-payment" class="box">
  <h2 class="title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>

  <?php echo functions::form_draw_form_begin('shipping_form', 'post'); ?>
    <div class="options btn-group-vertical btn-block">
      <?php foreach ($options as $module) foreach ($module['options'] as $option) { ?>
      <label class="option btn btn-default<?php echo ($module['id'].':'.$option['id'] == $selected['id']) ? ' active' : ''; ?><?php echo !empty($option['error']) ? ' disabled' : ''; ?>">
        <?php echo functions::form_draw_radio_button('payment[option_id]', $module['id'].':'.$option['id'], $selected['id'], 'style="display: none;"' . (!empty($option['error']) ? ' disabled="disabled"' : '')); ?>
        <div class="header row" style="margin: 0;">
          <div class="col-sm-fourths thumbnail" style="margin: 0;">
            <img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], 100, 40, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" />
          </div>
          <div class="col-sm-5 text-left">
            <h4 class="title" style="margin: 0.5em 0 0 0;"><?php echo $module['title']; ?></h4>
            <div class="name"><?php echo $option['name']; ?></div>
          </div>
          <div class="col-sm-thirds text-right">
            <div class="price"><?php echo (empty($option['error']) && $option['cost'] != 0) ? '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])) : language::translate('text_no_fee', 'No fee'); ?></div>
          </div>
        </div>

        <?php if (!empty($option['description']) || !empty($option['fields'])) { ?>
        <div class="content">
          <hr />
          <p class="description text-left"><?php echo $option['fields'] . $option['description']; ?></p>
        </div>
        <?php } ?>
      </label>
      <?php } ?>
    </div>
  <?php functions::form_draw_form_end(); ?>
</div>