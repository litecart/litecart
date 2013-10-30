<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once('../includes/app_header.inc.php');
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
  }
  
  if (empty(cart::$data['total']['physical'])) return;
  
  $shipping = new mod_shipping();
  
  if (empty(customer::$data['country_code'])) return;
  
  if (!empty($_POST['set_shipping'])) {
    list($module_id, $option_id) = explode(':', $_POST['selected_shipping']);
    if ($error = $shipping->run('before_select', $module_id)) {
      notices::add('errors', $error);
    } else {
      $shipping->select($module_id, $option_id);
    }
    header('Location: '. ((FS_DIR_HTTP_ROOT . $_SERVER['SCRIPT_NAME'] == __FILE__) ? $_SERVER['REQUEST_URI'] : document::link(WS_DIR_HTTP_HOME . 'checkout.php')));
    exit;
  }
  
  $options = $shipping->options();
  
  if (!empty($shipping->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $shipping->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id])) {
      $shipping->data['selected'] = array();
    } else {
      $shipping->select($module_id, $option_id); // Refresh
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
  <div class="heading"><h2><?php echo language::translate('title_shipping', 'Shipping'); ?></h2></div>
  <div class="content listing-wrapper">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
    <div class="option-wrapper<?php echo ($module['id'].':'.$option['id'] == $shipping->data['selected']['id']) ? ' selected' : false; ?>">
      <?php echo functions::form_draw_form_begin('shipping_form') . functions::form_draw_hidden_field('selected_shipping', $module['id'].':'.$option['id'], $shipping->data['selected']['id']); ?>
        <div class="icon"><img src="<?php echo functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 160, 60, 'FIT_USE_WHITESPACING'); ?>" width="160" height="60" /></div>
        <div class="title"><?php echo $module['title']; ?></div>
        <div class="name"><?php echo $option['name']; ?></div>
        <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
        <div class="footer">
          <div class="price"><?php echo currency::format(tax::calculate($option['cost'], $option['tax_class_id'])); ?></div>
          <div class="select">
<?php
  if ($module['id'].':'.$option['id'] == $shipping->data['selected']['id']) {
    if (!empty($option['fields'])) {
      echo functions::form_draw_button('set_shipping', language::translate('title_update', 'Update'), 'submit');
    } else {
      echo functions::form_draw_button('set_shipping', language::translate('title_selected', 'Selected'), 'submit', 'class="active"');
    }
  } else {
    echo functions::form_draw_button('set_shipping', language::translate('title_select', 'Select'), 'submit');
  }
?>
          </div>
        </div>
      <?php echo functions::form_draw_form_end(); ?>
    </div>
<?php
    }
  }
?>
  </div>
</div>
<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>