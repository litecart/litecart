<div id="box-checkout-summary" class="box">
  <h2 class="title"><?php echo language::translate('title_order_summary', 'Order Summary'); ?></h2>
  <div id="order_confirmation-wrapper" class="content">
    <table class="dataTable rounded-corners" style="width: 100%;">
      <tr class="header">
        <th class="quantity" style="width: 50px; text-align: center;"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
        <th class="item"><?php echo language::translate('title_product', 'Product'); ?></th>
        <th class="sku"><?php echo language::translate('title_sku', 'SKU'); ?></th>
        <th class="unit-cost" style="text-align: right;"><?php echo language::translate('title_unit_cost', 'Unit Cost'); ?></th>
        <th class="tax" style="text-align: right;"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_incl_tax', 'Incl. Tax') : language::translate('title_excl_tax', 'Excl. Tax'); ?></th>
        <th class="sum" style="text-align: right;"><?php echo language::translate('title_total', 'Total'); ?></th>
      </tr>

      <?php foreach ($items as $item) { ?>
      <tr>
        <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
        <td class="item"><?php echo $item['name']; ?></td>
        <td class="sku"><?php echo $item['sku']; ?></td>
        <td class="unit-cost" style="text-align: right;"><?php echo $item['price']; ?></td>
        <td class="tax" style="text-align: right;"><?php echo $item['tax']; ?></td>
        <td class="sum" style="text-align: right;"><?php echo $item['sum']; ?></td>
      </tr>
      <?php } ?>

      <tr>
        <td style="text-align: right;" colspan="6">&nbsp;</td>
      </tr>

      <?php foreach ($order_total as $row) { ?>
      <tr>
        <td colspan="5" style="text-align: right;"><strong><?php echo $row['title']; ?>:</strong></td>
        <td style="text-align: right;"><?php echo $row['value']; ?></td>
      </tr>
      <?php } ?>

      <tr>
        <td colspan="6">&nbsp;</td>
      </tr>

      <?php if ($tax_total) { ?>
      <tr>
        <td colspan="5" style="text-align: right; color: #999999;"><?php echo $incl_excl_tax; ?>:</td>
        <td style="text-align: right; color: #999999;"><?php echo $tax_total; ?></td>
      </tr>
      <?php } ?>

      <tr class="footer">
        <td colspan="5" style="text-align: right;"><strong><?php echo language::translate('title_payment_due', 'Payment Due'); ?>:</strong></td>
        <td style="text-align: right;"><strong><?php echo $payment_due; ?></strong></td>
      </tr>
    </table>

    <?php echo functions::form_draw_form_begin('order_form', 'post', document::ilink('order_process'));  ?>
      <div class="comments">
        <strong><?php echo language::translate('title_comments', 'Comments'); ?></strong><br />
          <?php echo functions::form_draw_textarea('comments', true); ?>
      </div>

      <div class="confirm">
        <?php if ($error) echo '<div class="warning">'. $error .'</div>' . PHP_EOL; ?>

        <p><?php echo functions::form_draw_button('confirm_order', $confirm, 'submit', !empty($error) ? 'disabled="disabled"' : ''); ?></p>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>