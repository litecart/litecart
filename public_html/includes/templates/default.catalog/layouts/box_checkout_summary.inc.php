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
      
      <?php foreach ($items as $item) { ?>
      <tr>
        <td align="center" nowrap="nowrap"><?php echo $item['quantity']; ?></td>
        <td align="left" nowrap="nowrap"><?php echo $item['name']; ?></td>
        <td align="left" nowrap="nowrap"><?php echo $item['sku']; ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['price'] + $item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format($item['tax'], false); ?></td>
        <td align="right" nowrap="nowrap"><?php echo currency::format(($item['price'] + $item['tax']) * $item['quantity'], false); ?></td>
      </tr>
      <?php } ?>
      
      <tr>
        <td align="right" colspan="6">&nbsp;</td>
      </tr>
      
      <?php foreach ($order_total as $row) { ?>
      <tr>
        <td colspan="5" align="right"><strong><?php echo $row['title']; ?>:</strong></td>
        <td align="right" width="100" nowrap="nowrap"><?php echo $row['value']; ?></td>
      </tr>
      <?php } ?>
      
      <tr>
        <td align="right" colspan="6">&nbsp;</td>
      </tr>
      
      <?php if ($tax_total) { ?>
      <tr>
        <td colspan="5" align="right" style="color: #999999;"><?php echo $incl_excl_tax; ?>:</td>
        <td align="right" width="100" nowrap="nowrap" style="color: #999999;"><?php echo $tax_total; ?></td>
      </tr>
      <?php } ?>
      
      <tr class="footer">
        <td colspan="5" align="right"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td align="right" width="100" nowrap="nowrap"><strong><?php echo $payment_due; ?></strong></td>
      </tr>
    </table>
    
    <?php echo functions::form_draw_form_begin('order_form', 'post', document::ilink('order_process'));  ?>
      <table width="100%">
        <tr>
          <td align="left" style="vertical-align: top; width: 40%;">
            <strong><?php echo language::translate('title_comments', 'Comments'); ?></strong><br />
              <?php echo functions::form_draw_textarea('comments', true, 'style="width: 100%; height: 50px;"'); ?>
          </td>
          <td align="right" style="vertical-align: bottom; width: 40%;">
          
            <?php if ($selected_payment) { ?>
            <p align="right"><?php echo $selected_payment['icon'] ? '<img src="'. $selected_payment['icon'] .'" alt="'. htmlspecialchars($selected_payment['title']) .'" />' : '<strong>'. $payment->data['selected']['title'] .'</strong>'; ?></p>
            <?php } ?>
            
            <?php if ($errors) echo '<div class="warning">'. $errors[0] .'</div>' . PHP_EOL; ?>

<?php
  if (!empty($errors)) {
    echo '      <p style="margin-bottom: 0; overflow: hidden;">'. functions::form_draw_button('confirm_order', $selected_payment['confirm'], 'submit', 'style="float: right; text-align: right;" disabled="disabled"') .'</p>' . PHP_EOL;
  } else {
    echo '      <p align="right">'. functions::form_draw_button('confirm_order', $selected_payment['confirm'], 'submit') .'</p>' . PHP_EOL;
  }
?>
          </td>
        </tr>
      </table>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>