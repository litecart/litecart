<div id="page" style="width: 640px; margin: 0px auto;">

  <header id="header" style="margin-bottom: 10px;">
    <table cellspacing="0" cellpadding="0" style="width: 100%;">
      <tr>
        <td style="text-align: left;"><img style="float: left; max-width: 300px; max-height: 50px; font-size: 32px;" src="<?php echo document::link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" /></td>
        <td style="text-align: right;">
          <h1 style="margin: 0; font-size: 18px;"><?php echo language::translate('title_packing_slip', 'Packing Slip'); ?></h1>
          <div><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></div>
          <div><?php echo !empty($order['date_created']) ? date(language::$selected['raw_date'], strtotime($order['date_created'])) : date(language::$selected['raw_date']); ?></div>
        </td>
      </tr>
    </table>
  </header>

  <div id="body">
    <table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; padding: 15px 10px; border: 1px solid #ccc;">
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></strong><br />
        <?php echo nl2br(functions::format_address($order['customer']['shipping_address'])); ?></td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_payment_address', 'Payment Address'); ?></strong><br />
        <?php echo nl2br(functions::format_address($order['customer'])); ?></td>
      </tr>
    </table>

    <table id="items" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; border: 1px solid #ccc;">
      <tr style="font-weight: bold; background-color: #f0f0f0;">
        <th style="padding: 10px 10px 10px 20px; border-bottom: 1px solid #ccc; text-align: center; width: 30px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #ccc; text-align: left;"><?php echo language::translate('title_item', 'Item'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #ccc; text-align: left;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
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
      <tr style="<?php echo ($rowclass == 'odd') ? 'background-color: #fcfcfc;' : 'background-color: #f5f5f5;'; ?>">
        <td style="padding: 10px 10px 10px 20px; text-align: center;"><?php echo (float)$item['quantity']; ?></td>
        <td style="padding: 5px 10px;"><?php echo $item['name']; ?>
<?php
    if (!empty($item['options'])) {
      foreach ($item['options'] as $key => $value) {
        echo '<br />- '.$key .': '. $value;
      }
    }
?>
        </td>
        <td><?php echo $item['sku']; ?></td>
      </tr>
      <?php } ?>
    </table>
  </div>

  <footer id="footer" style="width: 640px; margin: 0px auto;">
    <table cellspacing="0" cellpadding="0" style="width: 100%; border-top: 1px solid #ccc; padding-top: 20px; margin-top: 40px;">
      <tr>
        <td style="vertical-align: top;">
          <strong><?php echo language::translate('title_address', 'Address'); ?></strong><br />
          <?php echo nl2br(settings::get('store_postal_address')); ?>
        </td>
        <?php if (settings::get('store_phone')) { ?>
        <td style="vertical-align: top;">
          <strong><?php echo language::translate('title_phone', 'Phone'); ?></strong><br />
            <?php echo settings::get('store_phone'); ?><br />
        </td>
        <?php } ?>
        <td style="vertical-align: top;">
          <strong><?php echo language::translate('title_email', 'Email'); ?></strong><br />
            <?php echo settings::get('store_email'); ?>
        </td>
          <?php if (settings::get('store_tax_id')) { ?>
        <td style="vertical-align: top;">
          <strong><?php echo language::translate('title_vat_registration_id', 'VAT Registration ID'); ?></strong><br />
          <?php echo settings::get('store_tax_id'); ?>
        </td>
        <?php } ?>
      </tr>
    </table>
  </div>
</div>