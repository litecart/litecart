<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once('../app_header.inc.php');
    header('Content-type: text/html; charset='. $system->language->selected['charset']);
    $system->document->layout = 'default';
    $system->document->viewport = 'ajax';
  }
  
  if (empty($system->cart->data['total']['physical'])) return;
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'shipping.inc.php');
  $shipping = new shipping();
  
  if (empty($system->customer->data['country_code'])) return;
  
  if (!empty($_POST['set_shipping'])) {
    list($module_id, $option_id) = explode(':', $_POST['selected_shipping']);
    if ($error = $shipping->run('before_select', $module_id)) {
      $system->notices->add('errors', $error);
    } else {
      $shipping->select($module_id, $option_id);
    }
    header('Location: '. (($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) ? $_SERVER['REQUEST_URI'] : $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php')));
    exit;
  }
  
  $options = $shipping->options();
  
  if (!empty($shipping->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $shipping->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id])) {
      $shipping->data['selected'] = array();
    }
  }
  
  if (empty($options)) return;

  if (empty($shipping->data['selected'])) {
    $cheapest_shipping = explode(':', $shipping->cheapest());
    $shipping->select($cheapest_shipping[0], $cheapest_shipping[1]);
  }
  
  if (count($options) == 1
  && count($options[key($options)]['options']) == 1
  && empty($options[key($options)][key($options[key($options)]['options'])]['fields'])) return;
  
?>
<div class="box" id="box-checkout-shipping">
  <div class="heading"><h2><?php echo $system->language->translate('title_shipping', 'Shipping'); ?></h2></div>
  <div class="content listing-wrapper">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
    <div class="option-wrapper<?php echo ($module['id'].':'.$option['id'] == $shipping->data['selected']['id']) ? ' selected' : false; ?>">
      <?php echo $system->functions->form_draw_form_begin('shipping_form') . $system->functions->form_draw_hidden_field('selected_shipping', $module['id'].':'.$option['id'], $shipping->data['selected']['id']); ?>
        <div class="icon"><?php echo (is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'])) ? '<img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 160, 60, 'FIT') .'" width="160" height="60" />' : '&nbsp;'; ?></div>
        <div class="title"><?php echo $module['title']; ?></div>
        <div class="name"><?php echo $option['name']; ?></div>
        <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
        <div class="footer" style="position: relative;">
          <div class="price" style="position: absolute; left: 0; bottom: 0;"><?php echo $system->currency->format($system->tax->calculate($option['cost'], $option['tax_class_id'])); ?></div>
          <div class="select" style="position: absolute; right: 0; bottom: 0;">
<?php
  if ($module['id'].':'.$option['id'] == $shipping->data['selected']['id']) {
    if (!empty($option['fields'])) {
      echo $system->functions->form_draw_button('set_shipping', $system->language->translate('title_update', 'Update'), 'submit');
    } else {
      echo '<span class="button active">'. $system->language->translate('title_select', 'Select') .'</span>';
    }
  } else {
    echo $system->functions->form_draw_button('set_shipping', $system->language->translate('title_select', 'Select'), 'submit');
  }
?>
          </div>
        </div>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </div>
<?php
    }
  }
?>
  </div>
</div>
<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>