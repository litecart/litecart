<div id="header">
  <img src="<?php echo $this->system->document->link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo $this->system->settings->get('store_name'); ?>" style="float: left;" />
  <h1 align="right"><?php echo $this->system->language->translate('title_order_copy', 'Order Copy'); ?></h1>
  <p align="right"><?php echo date($this->system->language->selected['raw_date']); ?><br />
    <?php echo $this->system->language->translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></p>
</div>

<div id="body">
  <table width="100%" border="0" cellpadding="5" cellspacing="0" style="border: 1px solid #666;">
    <tr>
      <td><strong><?php echo $this->system->language->translate('title_payment_address', 'Payment Address'); ?>:</strong></td>
      <td><strong><?php echo $this->system->language->translate('title_shipping_address', 'Shipping Address'); ?>:</strong></td>
    </tr>
    <tr>
      <td width="50%"><?php echo nl2br($this->system->functions->format_address($order['customer'])); ?></td>
      <td width="50%"><?php echo nl2br($this->system->functions->format_address($order['customer']['shipping_address'])); ?></td>
    </tr>
    <tr>
      <td align="left" valign="top"><strong><?php echo $this->system->language->translate('title_payment_option', 'Payment Option'); ?>:</strong><br />
      <?php echo $order['payment_option']['name']; ?></td>
      <td align="left" valign="top"><strong><?php echo $this->system->language->translate('title_shipping_option', 'Shipping Option'); ?>:</strong><br />
      <?php echo $order['shipping_option']['name']; ?></td>
    </tr>
    <tr>
      <td align="left" valign="top"><strong><?php echo $this->system->language->translate('title_transaction_number', 'Transaction Number'); ?>:</strong><br />
        <?php echo $order['payment_transaction_id'] ? $order['payment_transaction_id'] : '-'; ?>
      </td>
      <td align="left" valign="top"><strong><?php echo $this->system->language->translate('title_weight', 'Weight'); ?>:</strong><br />
        <?php echo $this->system->weight->format($order['weight'], $order['weight_class']); ?>
      </td>
    </tr>
  </table>
  
  <p>&nbsp;</p>
  <table cellpadding="5" cellspacing="0" border="0" width="100%" style="border: 1px solid #666; font-size: 0.9;">
    <tr>
      <th nowrap="nowrap" valign="top" align="center" width="30"><?php echo $this->system->language->translate('title_qty', 'Qty'); ?></th>
      <th valign="top" align="left"><?php echo $this->system->language->translate('title_item', 'Item'); ?></th>
      <th nowrap="nowrap" valign="top" align="right" width="100"><?php echo $this->system->language->translate('title_unit_price', 'Unit Price'); ?></th>
      <th nowrap="nowrap" valign="top" align="right" width="100"><?php echo ($this->system->settings->get('display_prices_including_tax') == 'true') ? $this->system->language->translate('title_including_tax', 'Including Tax') : $this->system->language->translate('title_excluding_tax', 'Excluding Tax'); ?> </th>
      <th nowrap="nowrap" valign="top" align="right" width="100"><?php echo $this->system->language->translate('title_sum', 'Sum'); ?></th>
    </tr>
    <?php foreach ($order['items'] as $item) { ?>
    <tr>
      <td nowrap="nowrap" valign="top" align="center"><?php echo $item['quantity']; ?></td>
      <td valign="top" align="left"><?php echo $item['name']; ?>
<?php
  if (!empty($item['options'])) {
    foreach ($item['options'] as $key => $value) {
      echo '<br />- '.$key .': '. $value;
    }
  }
?>
      </td>
    <?php if ($this->system->settings->get('display_prices_including_tax') == 'true') { ?>
      <td nowrap="nowrap" valign="top" align="right"><?php echo $this->system->currency->format($item['price'] + $item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td nowrap="nowrap" valign="top" align="right"><?php echo $this->system->currency->format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td nowrap="nowrap" valign="top" align="right"><?php echo $this->system->currency->format($item['quantity'] * ($item['price'] + $item['tax']), false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <?php } else { ?>
      <td nowrap="nowrap" valign="top" align="right"><?php echo $this->system->currency->format($item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td nowrap="nowrap" valign="top" align="right"><?php echo $this->system->currency->format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td nowrap="nowrap" valign="top" align="right"><?php echo $this->system->currency->format($item['quantity'] * $item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <?php } ?>
    </tr>
    <?php } ?>
  </table>
  <p>&nbsp;</p>
  <table cellpadding="5" cellspacing="0" border="0" width="100%">
    <?php foreach ($order['order_total'] as $ot_row) { ?>
    <?php if ($this->system->settings->get('display_prices_including_tax') == 'true') { ?>
    <tr>
      <td align="right" nowrap="nowrap"><?php echo $ot_row['title']; ?>:</td>
      <td align="right" nowrap="nowrap"><?php echo $this->system->currency->format($ot_row['value'] + $ot_row['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
    <?php } else { ?>
    <tr>
      <td align="right" nowrap="nowrap"><?php echo $ot_row['title']; ?>:</td>
      <td align="right" nowrap="nowrap"><?php echo $this->system->currency->format($ot_row['value'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
    <?php } ?>
    <?php } ?>
    <?php if ($order['tax']['total']) { ?>
    <?php foreach ($order['tax']['rates'] as $tax_rate) { ?>
    <tr>
      <td valign="top" align="right"><?php echo ($this->system->settings->get('display_prices_including_tax') == 'true') ? $this->system->language->translate('title_including_tax', 'Including Tax') : $system->language->translate('title_excluding_tax', 'Excluding Tax'); ?> (<?php echo $tax_rate['name']; ?>):</td>
      <td valign="top" align="right"><?php echo $this->system->currency->format($tax_rate['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
    <?php } ?>
    <?php } ?>
    <tr>
      <td align="right" nowrap="nowrap"><strong><?php echo $this->system->language->translate('title_grand_total', 'Grand Total'); ?>:</strong></td>
      <td align="right" nowrap="nowrap" width="100"><?php echo $this->system->currency->format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
  </table>
</div>

<table id="footer" cellpadding="2" cellspacing="0" border="0" width="100%">
  <tr>
    <td><strong><?php echo $this->system->language->translate('title_address', 'Address'); ?>:</strong><br />
      <?php echo nl2br($this->system->settings->get('store_postal_address')); ?>
    </td>
    <?php if ($this->system->settings->get('store_phone')) { ?>
    <td valign="top"><strong><?php echo $this->system->language->translate('title_phone', 'Phone'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_phone'); ?>
    </td>
    <?php } ?>
    <?php if ($this->system->settings->get('store_tax_id')) { ?>
    <td valign="top"><strong><?php echo $this->system->language->translate('title_tax_id', 'Tax ID'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_tax_id'); ?>
    </td>
    <?php } ?>
  </tr>
</table>