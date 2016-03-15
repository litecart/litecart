<div id="box-checkout-payment" class="box">
  <h2 class="title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>
  <div class="content listing-wrapper">
    <ul id="payment-options" class="list-horizontal">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
      <li class="option<?php echo ($module['id'].':'.$option['id'] == $selected['id']) ? ' selected' : false; ?>">
      <?php echo functions::form_draw_form_begin('payment_form', 'post') . functions::form_draw_hidden_field('selected_payment', $module['id'].':'.$option['id'], $selected['id']); ?>
        <div class="icon-wrapper"><img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], 200, 70, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" /></div>
        <div class="title"><?php echo $module['title']; ?></div>
        <div class="name"><?php echo $option['name']; ?></div>
        <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
        <div class="footer">
          <p class="price"><?php if ($option['cost'] != 0) echo '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])); ?></p>
          <div class="select">
<?php
  if ($module['id'].':'.$option['id'] == $selected['id']) {
    if (!empty($option['fields'])) {
      echo functions::form_draw_button('set_payment', language::translate('title_update', 'Update'), 'submit');
    } else {
    echo functions::form_draw_button('set_payment', language::translate('title_selected', 'Selected'), 'submit', 'class="active"');
    }
  } else {
    echo functions::form_draw_button('set_payment', language::translate('title_select', 'Select'), 'submit');
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