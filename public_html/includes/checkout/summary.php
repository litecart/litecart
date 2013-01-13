<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once('../app_header.inc.php');
    header('Content-type: text/html; charset='. $system->language->selected['charset']);
    $system->document->layout = 'default';
    $system->document->viewport = 'ajax';
  }
  
  $errors = array();
  
  if (empty($system->cart->data['items'])) return;
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'shipping.inc.php');
  if (!isset($shipping)) $shipping = new shipping();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'payment.inc.php');
  if (!isset($payment)) $payment = new payment();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_total.inc.php');
  $order_total = new order_total();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'order.inc.php');
  $order = new ctrl_order('resume');
  
// Overwrite incompleted order in session
  if (!empty($order->data) && $order->data['customer']['id'] == $system->customer->data['id'] && empty($order->data['order_status_id'])) {
    $resume_id = $order->data['id'];
    $order = new ctrl_order('import_session');
    $order->data['id'] = $resume_id;
// New order based on session
  } else {
    $order = new ctrl_order('import_session');
  }
  
  $order->data['order_total'] = array();
  $order_total->process();
?>
<div class="box" id="box-checkout-summary">
  <div class="heading"><h2><?php echo $system->language->translate('title_order_summary', 'Order Summary'); ?></h2></div>
  <div class="content" id="order_confirmation-wrapper">
    <table class="dataTable rounded-corners" width="100%">
      <tr class="header">
        <th style="vertical-align: text-top" align="left" nowrap="nowrap"><?php echo $system->language->translate('title_quantity', 'Quantity'); ?></th>
        <th style="vertical-align: text-top" align="left" nowrap="nowrap"><?php echo $system->language->translate('title_product', 'Product'); ?></th>
        <th style="vertical-align: text-top" align="left" nowrap="nowrap" width="100%"><?php echo $system->language->translate('title_sku', 'SKU'); ?></th>
        <th style="vertical-align: text-top" align="right" nowrap="nowrap" width="100"><?php echo $system->language->translate('title_unit_cost', 'Unit Cost'); ?></th>
        <th style="vertical-align: text-top" align="right" nowrap="nowrap" width="100"><?php echo ($system->settings->get('display_prices_including_tax') == 'true') ? $system->language->translate('title_incl_tax', 'Incl. Tax') : $system->language->translate('title_excl_tax', 'Excl. Tax'); ?></th>
        <th style="vertical-align: text-top" align="right" nowrap="nowrap" width="100"><?php echo $system->language->translate('title_total', 'Total'); ?></th>
      </tr>
<?php
  $class = '';
  foreach ($order->data['items'] as $item) {
    if ($class == 'even') {
      $class = 'odd';
    } else {
      $class = 'even';
    }
?>
      <tr class="<?php echo $rowclass; ?>">
        <td align="left" nowrap="nowrap"><?php echo $item['quantity']; ?></td>
        <td align="left" nowrap="nowrap"><?php echo $item['name']; ?></td>
        <td align="left" nowrap="nowrap"><?php echo $item['sku']; ?></td>
<?php
    if ($system->settings->get('display_prices_including_tax') == 'true') {
?>
        <td align="right" nowrap="nowrap"><?php echo $system->currency->format($item['price'] + $item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo $system->currency->format($item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo $system->currency->format(($item['price'] + $item['tax']) * $item['quantity'], false); ?></td>
<?php
    } else {
?>
        <td align="right" nowrap="nowrap"><?php echo $system->currency->format($item['price'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo $system->currency->format($item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo $system->currency->format($item['price'] * $item['quantity'], false); ?></td>
<?php
    }
?>
      </tr>
<?php
  }
?>
      <tr>
        <td align="right" colspan="6">&nbsp;</td>
      </tr>
<?php 
  foreach ($order->data['order_total'] as $row) {
?>
      <tr>
        <td colspan="5" align="right"><strong><?php echo $row['title']; ?>:</strong></td>
        <td align="right" width="100" nowrap="nowrap">
<?php
    if ($system->settings->get('display_prices_including_tax') == 'true') {
      echo $system->currency->format($row['value'] + $row['tax'], false);
    } else {
      echo $system->currency->format($row['value'], false);
    }
?>
        </td>
      </tr>
<?php
  }
?>
      <tr>
        <td align="right" colspan="6">&nbsp;</td>
      </tr>
<?php
  if ($order->data['tax']['total']) {
    foreach ($order->data['tax']['rates'] as $rate) {
      if ($system->settings->get('display_prices_including_tax') == 'true') {
?>
      <tr>
        <td colspan="5" align="right" style="color: #999999;"><?php echo ($system->settings->get('display_prices_including_tax') == 'true') ? $system->language->translate('title_including_tax', 'Including Tax') : $system->language->translate('title_excluding_tax', 'Excluding Tax'); ?> (<?php echo $rate['name']; ?>):</td>
        <td align="right" width="100" nowrap="nowrap" style="color: #999999;"><?php echo $system->currency->format($rate['tax'], false); ?></td>
      </tr>
<?php
      }
    }
  }
?>
      <tr class="footer">
        <td colspan="5" align="right"><strong><?php echo $system->language->translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td align="right" width="100" nowrap="nowrap"><strong><?php echo $system->currency->format($order->data['payment_due'], false); ?></strong></td>
      </tr>
    </table>
    
    <?php echo $system->functions->form_draw_form_begin('order_form', 'post', $system->document->link(WS_DIR_HTTP_HOME . 'order_process.php'));  ?>
      <table width="100%">
        <tr>
          <td align="left" style="vertical-align: top;">
            <p><strong><?php echo $system->language->translate('title_comments', 'Comments'); ?></strong><br />
              <?php echo $system->functions->form_draw_textarea('comments', !empty($system->session->data['order_comments']) ? $system->session->data['order_comments'] : '', 'style="width: 400px; height: 50px;"'); ?></p>
          </td>
          <td align="right" style="vertical-align: bottom;">
            <p align="right"><?php echo (is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon'])) ? '<img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 160, 60, 'FIT_USE_WHITESPACING') .'" width="160" height="60" alt="'. htmlspecialchars($payment->data['selected']['title']) .'" />' : '<strong>'. $payment->data['selected']['title'] .'</strong>'; ?></p>
<?php
  if ($checkout_error = $order->checkout_forbidden()) $errors[] = $checkout_error;
  
  if (!empty($errors)) {
    echo '            <div class="warning">'. $errors[0] .'</div>' . PHP_EOL;
  } else {
    if ($system->settings->get('checkout_captcha_enabled') == 'true') {    
      echo '            <p align="right">'. $system->functions->captcha_generate(100, 40, 4, 'checkout', 'numbers', 'align="absbottom"') .' '. $system->functions->form_draw_input_field('captcha', '', 'input', 'style="width: 90px; height: 30px; font-size: 24px; text-align: center;"') .'<p>' . PHP_EOL;
    }
  }
?>

<?php
  if (!empty($errors)) {
    echo '      <p style="margin-bottom: 0; overflow: hidden;">'. $system->functions->form_draw_button('confirm_order', $system->language->translate('title_confirm_order', 'Confirm Order'), 'submit', 'style="float: right; text-align: right;" disabled="disabled"') .'</p>' . PHP_EOL;
  } else {
    echo '      <p align="right">'. $system->functions->form_draw_button('confirm_order', !empty($payment->data['selected']['confirm']) ? $payment->data['selected']['confirm'] : $system->language->translate('title_confirm_order', 'Confirm Order'), 'submit') .'</p>' . PHP_EOL;
  }
?>
          </td>
        </tr>
      </table>

    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>
<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>