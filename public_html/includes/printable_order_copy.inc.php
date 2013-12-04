<div id="page" style="min-width: 560px; max-width: 640px; margin: 0px auto;">
  
  <div id="header" style="margin-bottom: 10px;">
    <table cellspacing="0" cellpadding="0" style="width: 100%;">
      <tr>
        <td style="text-align: left;"><img style="float: left; max-width: 300px; max-height: 75px; font-size: 32px;" src="<?php echo document::link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" /></td>
        <td style="text-align: right;">
          <h1 style="margin: 0; font-size: 18px;"><?php echo language::translate('title_order_copy', 'Order Copy'); ?></h1>
          <div><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></div>
          <div><?php echo date(language::$selected['raw_date']); ?>
        </td>
      </tr>
    </table>
  </div>

  <div id="body">
    <table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; border: 1px solid #dfdfdf;">
      <tr>
        <td style="padding: 5px 10px; width: 55%;"><strong><?php echo language::translate('title_payment_address', 'Payment Address'); ?>:</strong></td>
        <td style="padding: 5px 10px; width: 45%;"><strong><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?>:</strong></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><?php echo nl2br(functions::format_address($order['customer'])); ?></td>
        <td style="padding: 5px 10px;"><?php echo nl2br(functions::format_address($order['customer']['shipping_address'])); ?></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_phone', 'Phone'); ?>:</strong><br />
        <?php echo !empty($order['customer']['phone']) ? $order['customer']['phone'] : '-'; ?></td>
        <td style="padding: 5px 10px;"></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_payment_option', 'Payment Option'); ?>:</strong><br />
        <?php echo !empty($order['payment_option']['name']) ? $order['payment_option']['name'] : '-'; ?></td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_shipping_option', 'Shipping Option'); ?>:</strong><br />
        <?php echo !empty($order['shipping_option']['name']) ? $order['shipping_option']['name'] : '-'; ?></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_transaction_number', 'Transaction Number'); ?>:</strong><br />
          <?php echo !empty($order['payment_transaction_id']) ? $order['payment_transaction_id'] : '-'; ?></td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?>:</strong><br />
          <?php echo !empty($order['shipping_tracking_id']) ? $order['shipping_tracking_id'] : '-'; ?></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;">&nbsp;</td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_weight', 'Weight'); ?>:</strong><br />
          <?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_class']) : '-'; ?></td>
      </tr>
    </table>
    
    <table id="items" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; border: 1px solid #dfdfdf;">
      <tr style="font-weight: bold; background-color: #f0f0f0;">
        <th style="padding: 10px; border-bottom: 1px solid #dfdfdf; text-align: center; width: 30px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #dfdfdf; text-align: left;"><?php echo language::translate('title_item', 'Item'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #dfdfdf; text-align: left;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #dfdfdf; text-align: right;"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #dfdfdf; text-align: right;"><?php echo language::translate('title_tax', 'Tax'); ?> </th>
        <th style="padding: 10px; border-bottom: 1px solid #dfdfdf; text-align: right;"><?php echo language::translate('title_sum', 'Sum'); ?></th>
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
        <td style="padding: 5px 10px; text-align: center;"><?php echo $item['quantity']; ?></td>
        <td style="padding: 5px 10px;"><?php echo $item['name']; ?>
<?php
    if (!empty($item['options'])) {
      foreach ($item['options'] as $key => $value) {
        echo '<br />- '.$key .': '. $value;
      }
    }
?>
        </td>
        <td align="left"><?php echo $item['sku']; ?></td>
      <?php if (settings::get('display_prices_including_tax')) { ?>
        <td style="padding: 5px 10px; text-align: right; width: 75px;"><?php echo currency::format($item['price'] + $item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
        <td style="padding: 5px 10px; text-align: right; width: 75px;"><?php echo currency::format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
        <td style="padding: 5px 10px; text-align: right; width: 75px;"><?php echo currency::format($item['quantity'] * ($item['price'] + $item['tax']), false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <?php } else { ?>
        <td style="padding: 5px 10px; text-align: right; width: 75px;"><?php echo currency::format($item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
        <td style="padding: 5px 10px; text-align: right; width: 75px;"><?php echo currency::format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
        <td style="padding: 5px 10px; text-align: right; width: 75px;"><?php echo currency::format($item['quantity'] * $item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <?php } ?>
      </tr>
      <?php } ?>
    </table>
    
    <table id="order-total" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; border: none;">
      <?php foreach ($order['order_total'] as $ot_row) { ?>
      <?php if (settings::get('display_prices_including_tax')) { ?>
      <tr>
        <td style="padding: 3px 10px; white-space: nowrap; text-align: right;"><?php echo $ot_row['title']; ?>:</td>
        <td style="padding: 3px 10px; text-align: right; width: 75px;"><?php echo currency::format($ot_row['value'] + $ot_row['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } else { ?>
      <tr>
        <td style="padding: 3px 10px; white-space: nowrap; text-align: right;"><?php echo $ot_row['title']; ?>:</td>
        <td style="padding: 3px 10px; text-align: right; width: 75px;"><?php echo currency::format($ot_row['value'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>
      <?php } ?>
      
      <?php if (!empty($order['tax_total'])) { ?>
      <tr>
        <td style="padding: 3px 10px; white-space: nowrap; text-align: right;"><?php echo (settings::get('display_prices_including_tax')) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
        <td style="padding: 3px 10px; text-align: right; width: 75px;"><?php echo currency::format($order['tax_total'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>
      
      <tr>
        <td style="padding: 3px 10px; white-space: nowrap; text-align: right;"><strong><?php echo language::translate('title_grand_total', 'Grand Total'); ?>:</strong></td>
        <td style="padding: 3px 10px; text-align: right; width: 75px;"><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
    </table>
    
    <p>&nbsp;</p>
  
<?php
  if (!empty($order['comments'])) {
?>
  <table id="comments" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; border: 1px solid #dfdfdf;">
<?php
    foreach ($order['comments'] as $comment) {
      if (!empty($comment['hidden'])) continue;
?>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo strftime(language::$selected['format_date'], strtotime($comment['date_created'])); ?>:</strong> <?php echo $comment['text']; ?></td>
      </tr>
<?php
    }
?>
    </table>
<?php
  }
?>
  </div>

  <table id="footer" cellspacing="0" cellpadding="0" style="width: 100%;">
    <tr>
      <td style="vertical-align: top;"><strong><?php echo language::translate('title_address', 'Address'); ?>:</strong><br />
        <?php echo nl2br(settings::get('store_postal_address')); ?>
      </td>
      <?php if (settings::get('store_phone')) { ?>
      <td style="vertical-align: top;"><strong><?php echo language::translate('title_phone', 'Phone'); ?>:</strong><br />
        <?php echo settings::get('store_phone'); ?>
      </td>
      <?php } ?>
      <?php if (settings::get('store_tax_id')) { ?>
      <td style="vertical-align: top;"><strong><?php echo language::translate('title_tax_id', 'Tax ID'); ?>:</strong><br />
        <?php echo settings::get('store_tax_id'); ?>
      </td>
      <?php } ?>
      <td style="vertical-align: top;"><strong><?php echo language::translate('title_email', 'E-mail'); ?>:</strong><br />
        <?php echo settings::get('store_email'); ?>
      </td>
    </tr>
  </table>
  
</div>