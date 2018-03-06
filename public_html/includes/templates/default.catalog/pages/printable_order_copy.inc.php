<style>
.logotype {
  max-width: 320px;
  max-height: 70px;
}

h1 {
  margin: 0;
}

.addresses .row > *:nth-child(1), .addresses .row > *:nth-child(2) {
  margin-top: 4mm;
}

.billing-address .rounded-rectangle {
  border: 1px solid #000;
  border-radius: 5mm;
  padding: 4mm;
  margin-left: -15px;
  margin-bottom: 3mm;
}
.billing-address .value {
  margin: 0 !important;
}

.items tr th:last-child, .order-total tr td:last-child {
  width: 30mm;
}

.order-total tr td:first-child:after {
  content: ':';
}

.page .label {
  font-weight: bold;
  margin-bottom: 3pt;
}
.page .value {
  margin-bottom: 3mm;
}
</style>

<section class="page" data-size="A4">
  <header class="header">
    <div class="row">
      <div class="col-xs-6">
        <img class="logotype" src="<?php echo document::link(WS_DIR_IMAGES . 'logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" />
      </div>

      <div class="col-xs-6 text-right">
        <h1><?php echo language::translate('title_order_copy', 'Order Copy'); ?></h1>
        <div><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></div>
        <div><?php echo !empty($order['date_created']) ? date(language::$selected['raw_date'], strtotime($order['date_created'])) : date(language::$selected['raw_date']); ?></div>
      </div>
    </div>
  </header>

  <main class="content">
    <div class="addresses">
      <div class="row">
        <div class="col-xs-3 shipping-address">
          <div class="label"><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></div>
          <div class="value"><?php echo nl2br(reference::country($order['customer']['shipping_address']['country_code'])->format_address($order['customer']['shipping_address'])); ?></div>
        </div>

        <div class="col-xs-3">
          <div class="label"><?php echo language::translate('title_shipping_weight', 'Shipping Weight'); ?></div>
          <div class="value"><?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_class'])  : '-'; ?></div>
        </div>

        <div class="col-xs-6 billing-address">
          <div class="rounded-rectangle">
            <div class="label"><?php echo language::translate('title_billing_address', 'Billing Address'); ?></div>
            <div class="value"><?php echo nl2br(reference::country($order['customer']['country_code'])->format_address($order['customer'])); ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-3">
        <div class="label"><?php echo language::translate('title_shipping_option', 'Shipping Option'); ?></div>
        <div class="value"><?php echo !empty($order['shipping_option']['name']) ? $order['shipping_option']['name'] : '-'; ?></div>

        <div class="label"><?php echo language::translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></div>
        <div class="value"><?php echo !empty($order['shipping_tracking_id']) ? $order['shipping_tracking_id'] : '-'; ?></div>
      </div>

      <div class="col-xs-3">
        <div class="label"><?php echo language::translate('title_email', 'Email'); ?></div>
        <div class="value"><?php echo !empty($order['customer']['email']) ? $order['customer']['email'] : '-'; ?></div>

        <div class="label"><?php echo language::translate('title_phone', 'Phone'); ?></div>
        <div class="value"><?php echo !empty($order['customer']['phone']) ? $order['customer']['phone'] : '-'; ?></div>
      </div>

      <div class="col-xs-6">
        <div class="label"><?php echo language::translate('title_payment_option', 'Payment Option'); ?></div>
        <div class="value"><?php echo !empty($order['payment_option']['name']) ? $order['payment_option']['name'] : '-'; ?></div>

        <div class="label"><?php echo language::translate('title_transaction_number', 'Transaction Number'); ?></div>
        <div class="value"><?php echo !empty($order['payment_transaction_id']) ? $order['payment_transaction_id'] : '-'; ?></div>
      </div>
    </div>

    <table class="items table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo language::translate('title_qty', 'Qty'); ?></th>
          <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
          <th class="text-right"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
          <th class="text-right"><?php echo language::translate('title_tax', 'Tax'); ?> </th>
          <th class="text-right"><?php echo language::translate('title_sum', 'Sum'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($order['items'] as $item) { ?>
        <tr>
          <td><?php echo (float)$item['quantity']; ?></td>
          <td><?php echo $item['sku']; ?></td>
          <td style="white-space: normal;"><?php echo $item['name']; ?>
<?php
    if (!empty($item['options'])) {
      foreach ($item['options'] as $key => $value) {
        echo '<br />- '.$key .': '. $value;
      }
    }
?>
          </td>
          <?php if (!empty(customer::$data['display_prices_including_tax'])) { ?>
          <td class="text-right"><?php echo currency::format($item['price'] + $item['tax'], false, $order['currency_code'], $order['currency_value']); ?></td>
          <td class="text-right"><?php echo currency::format($item['tax'], false, $order['currency_code'], $order['currency_value']); ?> (<?php echo @round($item['tax']/$item['price']*100); ?> %)</td>
          <td class="text-right"><?php echo currency::format($item['quantity'] * ($item['price'] + $item['tax']), false, $order['currency_code'], $order['currency_value']); ?></td>
          <?php } else { ?>
          <td class="text-right"><?php echo currency::format($item['price'], false, $order['currency_code'], $order['currency_value']); ?></td>
          <td class="text-right"><?php echo currency::format($item['tax'], false, $order['currency_code'], $order['currency_value']); ?> (<?php echo @round($item['tax']/$item['price']*100); ?> %)</td>
          <td class="text-right"><?php echo currency::format($item['quantity'] * $item['price'], false, $order['currency_code'], $order['currency_value']); ?></td>
          <?php } ?>
        </tr>
        <?php } ?>
      </tbody>
    </table>

    <table class="order-total table data-table">
      <tbody>
        <?php foreach ($order['order_total'] as $ot_row) { ?>
        <?php if (!empty(customer::$data['display_prices_including_tax'])) { ?>
        <tr>
          <td class="text-right"><?php echo $ot_row['title']; ?></td>
          <td class="text-right"><?php echo currency::format($ot_row['value'] + $ot_row['tax'], false, $order['currency_code'], $order['currency_value']); ?></td>
        </tr>
        <?php } else { ?>
        <tr>
          <td class="text-right"><?php echo $ot_row['title']; ?></td>
          <td class="text-right"><?php echo currency::format($ot_row['value'], false, $order['currency_code'], $order['currency_value']); ?></td>
        </tr>
        <?php } ?>
        <?php } ?>

        <?php if (!empty($order['tax_total']) && $order['tax_total'] != 0) { ?>
        <tr>
          <td class="text-right"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?></td>
          <td class="text-right"><?php echo currency::format($order['tax_total'], false, $order['currency_code'], $order['currency_value']); ?></td>
        </tr>
        <?php } ?>

        <tr>
          <td class="text-right"><strong><?php echo language::translate('title_grand_total', 'Grand Total'); ?></strong></td>
          <td class="text-right"><strong><?php echo currency::format($order['payment_due'], false, $order['currency_code'], $order['currency_value']); ?></strong></td>
        </tr>
      </tbody>
    </table>

<?php
  if (!empty($order['comments'])) {
    if (in_array('0', array_column($order['comments'], 'hidden'))) {
?>
  <h2><?php echo language::translate('title_comments', 'Comments'); ?></h2>
  <ul class="comments list-unstyled">
<?php
      foreach ($order['comments'] as $comment) {
        if (!empty($comment['hidden'])) continue;
?>
    <li><?php echo date(language::$selected['raw_date'], strtotime($comment['date_created'])); ?>: <?php echo $comment['text']; ?></li>
<?php
      }
?>
  </ul>
<?php
    }
  }
?>
  </main>

  <?php if (count($order['items']) <= 10) { ?>
  <footer class="footer">

    <hr />

    <div class="row">
      <div class="col-md-auto">
        <div class="label"><?php echo language::translate('title_address', 'Address'); ?></div>
        <div class="value"><?php echo nl2br(settings::get('store_postal_address')); ?></div>
      </div>

      <?php if (settings::get('store_phone')) { ?>
      <div class="col-md-auto">
        <div class="label"><?php echo language::translate('title_phone', 'Phone'); ?></div>
        <div class="value"><?php echo settings::get('store_phone'); ?></div>
      </div>
      <?php } ?>

      <div class="col-md-auto">
        <div class="label"><?php echo language::translate('title_email', 'Email'); ?></div>
        <div class="value"><?php echo settings::get('store_email'); ?></div>

        <div class="label"><?php echo language::translate('title_website', 'Website'); ?></div>
        <div class="value"><?php echo htmlspecialchars(link::decode_idn(document::ilink(''))); ?></div>
      </div>

      <?php if (settings::get('store_tax_id')) { ?>
      <div class="col-md-auto">
        <div class="label"><?php echo language::translate('title_vat_registration_id', 'VAT Registration ID'); ?></div>
        <div class="value"><?php echo settings::get('store_tax_id'); ?></div>
      </div>
      <?php } ?>
    </div>
  </footer>
  <?php } ?>
</section>
