<div id="header">
  <img src="<?php echo $this->system->document->link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo $this->system->settings->get('store_name'); ?>" style="float: left;" />
  <h1 align="right"><?php echo $this->system->language->translate('title_packing_slip', 'Packing Slip'); ?></h1>
  <p align="right"><?php echo date($this->system->language->selected['raw_date']); ?><br />
    &nbsp;
  </p>
</div>

<div id="body">
  <table width="100%" style="border: 1px solid #666;">
    <tr>
      <td><strong><?php echo $this->system->language->translate('title_payment_address', 'Payment Address'); ?>:</strong></td>
      <td><strong><?php echo $this->system->language->translate('title_shipping_address', 'Shipping Address'); ?>:</strong></td>
    </tr>
    <tr>
      <td width="50%"><?php echo nl2br($this->system->functions->format_address($order['customer'])); ?></td>
      <td width="50%"><?php echo nl2br($this->system->functions->format_address($order['customer']['shipping_address'])); ?></td>
    </tr>
  </table>
  
  <p>&nbsp;</p>
  <table width="100%" style="border: 1px solid #666; font-size: 0.9;">
    <tr>
      <th width="30" align="center" nowrap="nowrap"><?php echo $this->system->language->translate('title_qty', 'Qty'); ?></th>
      <th align="left"><?php echo $this->system->language->translate('title_item', 'Item'); ?></th>
    </tr>
    <?php foreach ($order['items'] as $item) { ?>
    <tr>
      <td nowrap="nowrap" align="center"><?php echo $item['quantity']; ?></td>
      <td align="left"><?php echo $item['name']; ?>
<?php
  if (!empty($item['options'])) {
    foreach ($item['options'] as $key => $value) {
      echo '<br />- '.$key .': '. $value;
    }
  }
?>
      </td>
    </tr>
    <?php } ?>
  </table>
</div>

<table id="footer">
  <tr>
    <td><strong><?php echo $this->system->language->translate('title_address', 'Address'); ?>:</strong><br />
      <?php echo nl2br($this->system->settings->get('store_postal_address')); ?>
    </td>
    <?php if ($this->system->settings->get('store_phone')) { ?>
    <td><strong><?php echo $this->system->language->translate('title_phone', 'Phone'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_phone'); ?>
    </td>
    <?php } ?>
    <?php if ($this->system->settings->get('store_tax_id')) { ?>
    <td><strong><?php echo $this->system->language->translate('title_tax_id', 'Tax ID'); ?>:</strong><br />
      <?php echo $this->system->settings->get('store_tax_id'); ?>
    </td>
    <?php } ?>
  </tr>
</table>