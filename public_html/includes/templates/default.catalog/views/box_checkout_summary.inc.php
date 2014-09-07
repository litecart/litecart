<div class="box" id="box-checkout-summary">
  <div class="heading"><h2><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2></div>
  <div class="content" id="order_confirmation-wrapper">
    <table class="dataTable rounded-corners" style="width: 100%;">
      <tr class="header">
        <th class="quantity" style="vertical-align: text-top" align="left" nowrap="nowrap" width="50"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
        <th class="item" style="vertical-align: text-top" align="left" nowrap="nowrap"><?php echo language::translate('title_product', 'Product'); ?></th>
        <th class="sku" style="vertical-align: text-top" align="left" nowrap="nowrap"><?php echo language::translate('title_sku', 'SKU'); ?></th>
        <th class="unit-cost" style="vertical-align: text-top" align="right" nowrap="nowrap"><?php echo language::translate('title_unit_cost', 'Unit Cost'); ?></th>
        <th class="tax" style="vertical-align: text-top" align="right" nowrap="nowrap"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_incl_tax', 'Incl. Tax') : language::translate('title_excl_tax', 'Excl. Tax'); ?></th>
        <th class="sum" style="vertical-align: text-top" align="right" nowrap="nowrap"><?php echo language::translate('title_total', 'Total'); ?></th>
      </tr>
      
      <?php foreach ($items as $item) { ?>
      <tr>
        <td class="quantity" align="center" nowrap="nowrap"><?php echo $item['quantity']; ?></td>
        <td class="item" class="unit-cost" align="left" nowrap="nowrap"><?php echo $item['name']; ?></td>
        <td class="sku" align="left" nowrap="nowrap"><?php echo $item['sku']; ?></td>
        <td class="unit-cost" align="right" nowrap="nowrap"><?php echo currency::format($item['price'] + $item['tax'], false); ?></td>
        <td class="tax" align="right" nowrap="nowrap"><?php echo currency::format($item['tax'], false); ?></td>
        <td class="sum" align="right" nowrap="nowrap"><?php echo currency::format(($item['price'] + $item['tax']) * $item['quantity'], false); ?></td>
      </tr>
      <?php } ?>
      
      <tr>
        <td align="right" colspan="6">&nbsp;</td>
      </tr>
      
      <?php foreach ($order_total as $row) { ?>
      <tr>
        <td colspan="5" align="right"><strong><?php echo $row['title']; ?>:</strong></td>
        <td align="right" nowrap="nowrap"><?php echo $row['value']; ?></td>
      </tr>
      <?php } ?>
      
      <tr>
        <td align="right" colspan="6">&nbsp;</td>
      </tr>
      
      <?php if ($tax_total) { ?>
      <tr>
        <td colspan="5" align="right" style="color: #999999;"><?php echo $incl_excl_tax; ?>:</td>
        <td align="right" nowrap="nowrap" style="color: #999999;"><?php echo $tax_total; ?></td>
      </tr>
      <?php } ?>
      
      <tr class="footer">
        <td colspan="5" align="right"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td align="right" nowrap="nowrap"><strong><?php echo $payment_due; ?></strong></td>
      </tr>
    </table>
    
    <?php echo functions::form_draw_form_begin('order_form', 'post', document::ilink('order_process'));  ?>
      <div class="comments">
        <strong><?php echo language::translate('title_comments', 'Comments'); ?></strong><br />
          <?php echo functions::form_draw_textarea('comments', true); ?>
      </div>
      
      <div class="confirm">
        <?php if ($selected_payment) echo '<p>'. $selected_payment['icon'] ? '<img src="'. $selected_payment['icon'] .'" alt="'. htmlspecialchars($selected_payment['title']) .'" />' : '<strong>'. $selected_payment['title'] .'</strong></p>'; ?>
        
        <?php if ($error) echo '<div class="warning">'. $error .'</div>' . PHP_EOL; ?>

        <p><?php echo functions::form_draw_button('confirm_order', $confirm, 'submit', !empty($error) ? 'disabled="disabled"' : ''); ?></p>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>