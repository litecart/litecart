<div id="header" style="margin-bottom: 10px;">
  <table style="width: 100%;">
    <tr>
      <td style="text-align: left;"><img style="float: left; max-width: 300px; max-height: 75px; font-size: 32px;" src="<?php echo $this->system->document->link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo $this->system->settings->get('store_name'); ?>" /></td>
      <td style="text-align: right;">
        <h1 style="margin: 0;"><?php echo $this->system->language->translate('title_order_copy', 'Order Copy'); ?></h1>
        <div><?php echo $this->system->language->translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></div>
        <div><?php echo date($this->system->language->selected['raw_date']); ?>
      </td>
    </tr>
  </table>
</div>

<div id="body">
  <table id="addresses" class="dataTable" style="width: 100%;">
    <tr>
      <td style="width: 55%;"><strong><?php echo $this->system->language->translate('title_payment_address', 'Payment Address'); ?>:</strong></td>
      <td style="width: 45%;"><strong><?php echo $this->system->language->translate('title_shipping_address', 'Shipping Address'); ?>:</strong></td>
    </tr>
    <tr>
      <td><?php echo nl2br($this->system->functions->format_address($order['customer'])); ?></td>
      <td><?php echo nl2br($this->system->functions->format_address($order['customer']['shipping_address'])); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $this->system->language->translate('title_payment_option', 'Payment Option'); ?>:</strong><br />
      <?php echo $order['payment_option']['name'] ? $order['payment_option']['name'] : '-'; ?></td>
      <td align="left"><strong><?php echo $this->system->language->translate('title_shipping_option', 'Shipping Option'); ?>:</strong><br />
      <?php echo $order['shipping_option']['name'] ? $order['shipping_option']['name'] : '-'; ?></td>
    </tr>
    <tr>
      <td><strong><?php echo $this->system->language->translate('title_transaction_number', 'Transaction Number'); ?>:</strong><br />
        <?php echo $order['payment_transaction_id'] ? $order['payment_transaction_id'] : '-'; ?></td>
      <td align="left"><strong><?php echo $this->system->language->translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?>:</strong><br />
        <?php echo $order['shipping_tracking_id'] ? $order['shipping_tracking_id'] : '-'; ?></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><strong><?php echo $this->system->language->translate('title_weight', 'Weight'); ?>:</strong><br />
        <?php echo $this->system->weight->format($order['weight'], $order['weight_class']); ?></td>
    </tr>
  </table>
  
  <table id="items" class="dataTable" style="width: 100%; clear: both;">
    <tr class="header">
      <th style="text-align: center; width: 30px;"><?php echo $this->system->language->translate('title_qty', 'Qty'); ?></th>
      <th style="text-align: left;"><?php echo $this->system->language->translate('title_item', 'Item'); ?></th>
      <th style="text-align: left;"><?php echo $this->system->language->translate('title_sku', 'SKU'); ?></th>
      <th style="text-align: left;"><?php echo $this->system->language->translate('title_unit_price', 'Unit Price'); ?></th>
      <th style="text-align: right;"><?php echo $this->system->language->translate('title_tax', 'Tax'); ?> </th>
      <th style="text-align: right;"><?php echo $this->system->language->translate('title_sum', 'Sum'); ?></th>
    </tr>
<?php
  $rowclass = '';
  foreach ($order['items'] as $item) {
    if ($rowclass == 'odd') {
      $rowclass = 'even';
    } else {
      $rowclass = 'odd';
    }
?>
    <tr class="<?php echo $rowclass; ?>">
      <td align="center"><?php echo $item['quantity']; ?></td>
      <td align="left"><?php echo $item['name']; ?>
<?php
    if (!empty($item['options'])) {
      foreach ($item['options'] as $key => $value) {
        echo '<br />- '.$key .': '. $value;
      }
    }
?>
      </td>
      <td align="left"><?php echo $item['sku']; ?></td>
    <?php if ($this->system->settings->get('display_prices_including_tax') == 'true') { ?>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($item['price'] + $item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($item['quantity'] * ($item['price'] + $item['tax']), false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <?php } else { ?>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($item['quantity'] * $item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    <?php } ?>
    </tr>
    <?php } ?>
  </table>
  
  <table id="order-total" class="dataTable" style="width: 100%; border: none;">
    <?php foreach ($order['order_total'] as $ot_row) { ?>
    <?php if ($this->system->settings->get('display_prices_including_tax') == 'true') { ?>
    <tr>
      <td style="white-space: nowrap; text-align: right;"><?php echo $ot_row['title']; ?>:</td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($ot_row['value'] + $ot_row['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
    <?php } else { ?>
    <tr>
      <td style="white-space: nowrap; text-align: right;"><?php echo $ot_row['title']; ?>:</td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($ot_row['value'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
    <?php } ?>
    <?php } ?>
    
    <?php if ($order['tax']['total']) { ?>
    <?php foreach ($order['tax']['rates'] as $tax_rate) { ?>
    <tr>
      <td style="white-space: nowrap; text-align: right;"><?php echo ($this->system->settings->get('display_prices_including_tax') == 'true') ? $this->system->language->translate('title_including_tax', 'Including Tax') : $system->language->translate('title_excluding_tax', 'Excluding Tax'); ?> (<?php echo $tax_rate['name']; ?>):</td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($tax_rate['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
    <?php } ?>
    <?php } ?>
    
    <tr>
      <td style="white-space: nowrap; text-align: right;"><strong><?php echo $this->system->language->translate('title_grand_total', 'Grand Total'); ?>:</strong></td>
      <td style="text-align: right; width: 75px;"><?php echo $this->system->currency->format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
    </tr>
  </table>
  
  <p>&nbsp;</p>
  
<?php
  if (!empty($order['comments'])) {
?>
  <table id="comments" class="dataTable" style="width: 100%;">
<?php
    foreach ($order['comments'] as $comment) {
      if (!empty($order['hidden'])) continue;
?>
    <tr>
      <td><strong><?php echo strftime($this->system->language->selected['format_date'], strtotime($comment['date_created'])); ?>:</strong> <?php echo $comment['text']; ?></td>
    </tr>
<?php
    }
?>
  </table>
<?php
  }
?>
</div>

<table id="footer" style="width: 100%;">
  <tr>
    <td style="vertical-align: top;"><strong><?php echo $this->system->language->translate('title_address', 'Address'); ?>:</strong><br />
      <?php echo nl2br($this->system->settings->get('store_postal_address')); ?>
    </td>
    <?php if ($this->system->settings->get('store_phone')) { ?>
    <td style="vertical-align: top;"><strong><?php echo $this->system->language->translate('title_phone', 'Phone'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_phone'); ?>
    </td>
    <?php } ?>
    <?php if ($this->system->settings->get('store_tax_id')) { ?>
    <td style="vertical-align: top;"><strong><?php echo $this->system->language->translate('title_tax_id', 'Tax ID'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_tax_id'); ?>
    </td>
    <?php } ?>
    <td style="vertical-align: top;"><strong><?php echo $this->system->language->translate('title_email', 'E-mail'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_email'); ?>
    </td>
  </tr>
</table>