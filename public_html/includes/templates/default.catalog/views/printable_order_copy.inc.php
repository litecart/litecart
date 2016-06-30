<div id="page" style="width: 640px; margin: 0px auto;">

  <header id="header" style="margin-bottom: 10px;">
    <table cellspacing="0" cellpadding="0" style="width: 100%;">
      <tr>
        <td style="text-align: left;"><img style="float: left; max-width: 300px; max-height: 50px; font-size: 32px;" src="<?php echo document::link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" /></td>
        <td style="text-align: right;">
          <h1 style="margin: 0; font-size: 18px;"><?php echo language::translate('title_order_copy', 'Order Copy'); ?></h1>
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
      <tr>
        <td colspan="2" style="padding-top: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_phone', 'Phone'); ?></strong><br />
        <?php echo !empty($order['customer']['phone']) ? $order['customer']['phone'] : '-'; ?></td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_email', 'Email'); ?></strong><br />
        <?php echo !empty($order['customer']['email']) ? $order['customer']['email'] : '-'; ?></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_shipping_option', 'Shipping Option'); ?></strong><br />
        <?php echo !empty($order['shipping_option']['name']) ? $order['shipping_option']['name'] : '-'; ?></td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_payment_option', 'Payment Option'); ?></strong><br />
        <?php echo !empty($order['payment_option']['name']) ? $order['payment_option']['name'] : '-'; ?></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></strong><br />
          <?php echo !empty($order['shipping_tracking_id']) ? $order['shipping_tracking_id'] : '-'; ?></td>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_transaction_number', 'Transaction Number'); ?></strong><br />
          <?php echo !empty($order['payment_transaction_id']) ? $order['payment_transaction_id'] : '-'; ?></td>
      </tr>
      <tr>
        <td style="padding: 5px 10px;"><strong><?php echo language::translate('title_shipping_weight', 'Shipping Weight'); ?></strong><br />
          <?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_class'])  : '-'; ?></td>
        <td style="padding: 5px 10px;"></td>
      </tr>
    </table>

    <table id="items" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; border: 1px solid #ccc;">
      <tr style="font-weight: bold; background-color: #f0f0f0;">
        <th style="padding: 10px 10px 10px 20px; border-bottom: 1px solid #ccc; text-align: center; width: 30px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #ccc; text-align: left;"><?php echo language::translate('title_item', 'Item'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #ccc; text-align: left;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #ccc; text-align: right;"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
        <th style="padding: 10px; border-bottom: 1px solid #ccc; text-align: right;"><?php echo language::translate('title_tax', 'Tax'); ?> </th>
        <th style="padding: 10px 20px 10px 10px; border-bottom: 1px solid #ccc; text-align: right;"><?php echo language::translate('title_sum', 'Sum'); ?></th>
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
      <?php if (!empty(customer::$data['display_prices_including_tax'])) { ?>
        <td style="padding: 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($item['price'] + $item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
        <td style="padding: 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?> (<?php echo @round($item['tax']/$item['price']*100); ?> %)</td>
        <td style="padding: 10px 20px 10px 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($item['quantity'] * ($item['price'] + $item['tax']), false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <?php } else { ?>
        <td style="padding: 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
        <td style="padding: 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($item['tax'], false, false, $order['currency_code'], $order['currency_value']); ?> (<?php echo @round($item['tax']/$item['price']*100); ?> %)</td>
        <td style="padding: 10px 20px 10px 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($item['quantity'] * $item['price'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <?php } ?>
      </tr>
      <?php } ?>
    </table>

    <table id="order-total" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 20px; padding-right: 10px; border: none;">
      <?php foreach ($order['order_total'] as $ot_row) { ?>
      <?php if (!empty(customer::$data['display_prices_including_tax'])) { ?>
      <tr>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right;"><?php echo $ot_row['title']; ?></td>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($ot_row['value'] + $ot_row['tax'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } else { ?>
      <tr>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right;"><?php echo $ot_row['title']; ?></td>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($ot_row['value'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>
      <?php } ?>

      <?php if (!empty($order['tax_total']) && $order['tax_total'] != 0) { ?>
      <tr>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right;"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?></td>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right; width: 100px;"><?php echo currency::format($order['tax_total'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      </tr>
      <?php } ?>

      <tr>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right;"><strong><?php echo language::translate('title_grand_total', 'Grand Total'); ?></strong></td>
        <td style="padding: 5px 10px; white-space: nowrap; text-align: right; width: 100px;"><strong><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></strong></td>
      </tr>
    </table>

<?php
  if (!empty($order['comments'])) {
    $has_comments = false;
    foreach ($order['comments'] as $comment) {
      if (empty($comment['hidden'])) $has_comments = true;
      break;
    }
    if ($has_comments) {
?>
  <table id="comments" cellspacing="0" cellpadding="0" style="width: 100%; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc;">
    <tr>
      <td style="padding: 5px 20px;"><strong><?php echo language::translate('title_comments', 'Comments'); ?></strong></td>
    </tr>
<?php
      foreach ($order['comments'] as $comment) {
        if (!empty($comment['hidden'])) continue;
?>
    <tr>
      <td style="padding: 5px 20px;"><strong><?php echo language::strftime(language::$selected['format_date'], strtotime($comment['date_created'])); ?>:</strong> <?php echo $comment['text']; ?></td>
    </tr>
<?php
      }
?>
    </table>
<?php
    }
  }
?>
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
  </footer>
</div>