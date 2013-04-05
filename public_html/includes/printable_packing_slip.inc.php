<div id="header" style="margin-bottom: 10px; position: relative; height: 75px;">
  <img style="position: absolute; top: 0; left: 0; max-width: 300px; max-height: 75px; font-size: 32px;" src="<?php echo $this->system->document->link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo $this->system->settings->get('store_name'); ?>" />
  <h1 style="display: inline; position: absolute; right: 0; top: 0; margin: 0;"><?php echo $this->system->language->translate('title_packing_slip', 'Packing Slip'); ?></h1>
  <div style="display: inline; position: absolute; right: 0; bottom: 18px;"><?php echo $this->system->language->translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></div>
  <div style="display: inline; position: absolute; right: 0; bottom: 0;"><?php echo date($this->system->language->selected['raw_date']); ?></div>
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
  </table>
  
  <table id="items" class="dataTable" style="width: 100%; clear: both;">
    <tr class="header">
      <th style="text-align: center; width: 30px;"><?php echo $this->system->language->translate('title_qty', 'Qty'); ?></th>
      <th style="text-align: left;"><?php echo $this->system->language->translate('title_item', 'Item'); ?></th>
      <th style="text-align: left;"><?php echo $this->system->language->translate('title_sku', 'SKU'); ?></th>
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
      <td align="left"><?php echo $item['sku']; ?>
    </tr>
    <?php } ?>
  </table>
</div>
