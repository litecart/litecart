<?php
  if (realpath(__FILE__) == realpath($_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'])) {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
  }
  
  if (cart::$data['total']['items'] == 0) return;
  
  $payment = new mod_payment();
  
  if (empty(customer::$data['country_code'])) return;
  
  if (!empty($_POST['set_payment'])) {
    list($module_id, $option_id) = explode(':', $_POST['selected_payment']);
    $payment->select($module_id, $option_id, $_POST);
    header('Location: '. ((FS_DIR_HTTP_ROOT . $_SERVER['SCRIPT_NAME'] == __FILE__) ? $_SERVER['REQUEST_URI'] : document::ilink('checkout')));
    exit;
  }
  
  $options = $payment->options();
  
  if (!empty($payment->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $payment->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id])) {
      $payment->data['selected'] = array();
    } else {
      $payment->select($module_id, $option_id); // Refresh
    }
  }
  
  if (empty($options)) return;
  
  if (empty($payment->data['selected'])) {
    $payment->set_cheapest();
  }
  
// Hide
  //if (count($options) == 1
  //&& count($options[key($options)]['options']) == 1
  //&& empty($options[key($options)]['options'][key($options[key($options)]['options'])]['fields'])
  //&& $options[key($options)]['options'][key($options[key($options)]['options'])]['cost'] == 0) return;
  
?>
<div class="box" id="box-checkout-payment">
  <div class="heading"><h2><?php echo language::translate('title_payment', 'Payment'); ?></h2></div>
  <div class="content listing-wrapper">
    <ul id="payment-options" class="list-horizontal">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
      <li class="option<?php echo ($module['id'].':'.$option['id'] == $payment->data['selected']['id']) ? ' selected' : false; ?>">
      <?php echo functions::form_draw_form_begin('payment_form', 'post') . functions::form_draw_hidden_field('selected_payment', $module['id'].':'.$option['id'], $payment->data['selected']['id']); ?>
        <div class="icon-wrapper"><img src="<?php echo functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 200, 70, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" /></div>
        <div class="title"><?php echo $module['title']; ?></div>
        <div class="name"><?php echo $option['name']; ?></div>
        <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
        <div class="footer">
          <div class="price"><?php echo currency::format(tax::calculate($option['cost'], $option['tax_class_id'])); ?></div>
          <div class="select">
<?php
  if ($module['id'].':'.$option['id'] == $payment->data['selected']['id']) {
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
