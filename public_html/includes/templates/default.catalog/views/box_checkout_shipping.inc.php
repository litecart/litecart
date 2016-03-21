<div id="box-checkout-shipping" class="box">
  <h2 class="title"><?php echo language::translate('title_shipping', 'Shipping'); ?></h2>
  <div class="content listing-wrapper">
    <ul id="shipping-options" class="list-horizontal">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
      <li class="option<?php echo (!empty($selected['id']) && $module['id'].':'.$option['id'] == $selected['id']) ? ' selected' : false; ?><?php echo !empty($option['error']) ? ' semi-transparent' : ''; ?>">
      <?php echo functions::form_draw_form_begin('shipping_form') . functions::form_draw_hidden_field('selected_shipping', $module['id'].':'.$option['id'], !empty($selected['id']) ? $selected['id'] : ''); ?>

        <div class="icon-wrapper"><img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], 200, 70, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" /></div>

        <div class="title"><?php echo $module['title']; ?></div>

        <div class="name"><?php echo $option['name']; ?></div>

        <?php if (!empty($option['error'])) { ?>
        <div class="error"><?php echo $option['error']; ?></div>
        <?php } else { ?>
        <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
        <?php } ?>

        <div class="footer">
          <p class="price"><?php if ($option['cost'] != 0) echo '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])); ?></p>

          <div class="select">
<?php
  if (!empty($selected['id']) && $module['id'].':'.$option['id'] == $selected['id']) {
    if (!empty($option['fields'])) {
      echo functions::form_draw_button('set_shipping', language::translate('title_update', 'Update'), 'submit', !empty($option['error']) ? 'disabled="disabled"' : '');
    } else {
      echo functions::form_draw_button('set_shipping', language::translate('title_selected', 'Selected'), 'submit', 'class="active"' . (!empty($option['error']) ?  'disabled="disabled"' : ''));
    }
  } else {
    echo functions::form_draw_button('set_shipping', language::translate('title_select', 'Select'), 'submit', !empty($option['error']) ? 'disabled="disabled"' : '');
  }
?>
          </div>
        </div>
      <?php echo functions::form_draw_form_end(); ?>
      </li>
<?php
    }
  }
?>
    </ul>
  </div>
</div>