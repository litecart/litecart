<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once('../includes/app_header.inc.php');
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
  }
  
  $errors = array();
  
  if (empty(cart::$data['items'])) return;
  
  if (!isset($shipping)) $shipping = new mod_shipping();
  
  if (!isset($payment)) $payment = new mod_payment();
  
  $order_total = new mod_order_total();
  
  $order = new ctrl_order('resume');
  
// Overwrite incompleted order in session
  if (!empty($order->data) && $order->data['customer']['id'] == customer::$data['id'] && empty($order->data['order_status_id'])) {
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
  <div class="heading"><h2><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2></div>
  <div class="content" id="order_confirmation-wrapper">
    <table class="dataTable rounded-corners" style="width: 100%;">
      <tr class="header">
        <th style="vertical-align: text-top" align="left" nowrap="nowrap" width="50"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
        <th style="vertical-align: text-top" align="left" nowrap="nowrap"><?php echo language::translate('title_product', 'Product'); ?></th>
        <th style="vertical-align: text-top" align="left" nowrap="nowrap"><?php echo language::translate('title_sku', 'SKU'); ?></th>
        <th style="vertical-align: text-top" align="right" nowrap="nowrap" width="100"><?php echo language::translate('title_unit_cost', 'Unit Cost'); ?></th>
        <th style="vertical-align: text-top" align="right" nowrap="nowrap" width="100"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_incl_tax', 'Incl. Tax') : language::translate('title_excl_tax', 'Excl. Tax'); ?></th>
        <th style="vertical-align: text-top" align="right" nowrap="nowrap" width="100"><?php echo language::translate('title_total', 'Total'); ?></th>
      </tr>
<?php
  foreach ($order->data['items'] as $item) {
?>
      <tr>
        <td align="center" nowrap="nowrap"><?php echo $item['quantity']; ?></td>
        <td align="left" nowrap="nowrap"><?php echo $item['name']; ?></td>
        <td align="left" nowrap="nowrap"><?php echo $item['sku']; ?></td>
<?php
    if (!empty(customer::$data['display_prices_including_tax'])) {
?>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['price'] + $item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format(($item['price'] + $item['tax']) * $item['quantity'], false); ?></td>
<?php
    } else {
?>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['price'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['price'] * $item['quantity'], false); ?></td>
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
    if (!empty(customer::$data['display_prices_including_tax'])) {
      echo currency::format($row['value'] + $row['tax'], false);
    } else {
      echo currency::format($row['value'], false);
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
  if ($order->data['tax_total']) {
?>
      <tr>
        <td colspan="5" align="right" style="color: #999999;"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
        <td align="right" width="100" nowrap="nowrap" style="color: #999999;"><?php echo currency::format($order->data['tax_total'], false); ?></td>
      </tr>
<?php
  }
?>
      <tr class="footer">
        <td colspan="5" align="right"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td align="right" width="100" nowrap="nowrap"><strong><?php echo currency::format($order->data['payment_due'], false); ?></strong></td>
      </tr>
    </table>
    
    <?php echo functions::form_draw_form_begin('order_form', 'post', document::link(WS_DIR_HTTP_HOME . 'order_process.php'));  ?>
      <table width="100%">
        <tr>
          <td align="left" style="vertical-align: top; width: 40%;">
            <strong><?php echo language::translate('title_comments', 'Comments'); ?></strong><br />
              <?php echo functions::form_draw_textarea('comments', true, 'style="width: 100%; height: 50px;"'); ?>
          </td>
          <td align="right" style="vertical-align: bottom; width: 40%;">
            <p align="right"><?php if (!empty($payment->data['selected'])) echo is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon']) ? '<img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 160, 60, 'FIT_USE_WHITESPACING') .'" width="160" height="60" alt="'. htmlspecialchars($payment->data['selected']['title']) .'" />' : '<strong>'. $payment->data['selected']['title'] .'</strong>'; ?></p>
<?php
  if ($checkout_error = $order->checkout_forbidden()) $errors[] = $checkout_error;
  
  if (!empty($errors)) {
    echo '            <div class="warning">'. $errors[0] .'</div>' . PHP_EOL;
  }
?>

<?php
  if (!empty($errors)) {
    echo '      <p style="margin-bottom: 0; overflow: hidden;">'. functions::form_draw_button('confirm_order', language::translate('title_confirm_order', 'Confirm Order'), 'submit', 'style="float: right; text-align: right;" disabled="disabled"') .'</p>' . PHP_EOL;
  } else {
    echo '      <p align="right">'. functions::form_draw_button('confirm_order', !empty($payment->data['selected']['confirm']) ? $payment->data['selected']['confirm'] : language::translate('title_confirm_order', 'Confirm Order'), 'submit') .'</p>' . PHP_EOL;
  }
?>
          </td>
        </tr>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>