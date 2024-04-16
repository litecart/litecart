<style>
.logotype {
  max-width: 250px;
  max-height: 70px;
}

h1 {
  margin: 0;
  border: none;
}

.addresses .row {
  margin-bottom: 8mm;
}
.addresses .billing-address {
  margin-top: -4mm;
}

.rounded-rectangle {
  border: 1px solid #000;
  border-radius: 5mm;
  padding: 4mm;
  margin-inline-start: -15px;
}
.billing-address .value {
  margin: 0 !important;
}

.items tr th:last-child, .order-total tr td:last-child {
  width: 30mm;
}
hr {
  margin: 0 0 2.5mm 0;
}
.page .label {
  font-weight: bold;
  margin-bottom: 3pt;
}
.page .value {
  margin-bottom: 3mm;
}
.page .footer .row {
  margin-bottom: 0;
}

@media print {
  button[name="print"] {
    display: none;
  }
}

@media screen {
  button[name="print"] {
    display: none;
  }

  html:hover button[name="print"] {
    position: fixed;
    top: 0cm;
    right: 1cm;
    display: block;
    margin: 5mm auto;
    z-index: 999999;
    border-radius: 0.25em;
  }
}
</style>

<section class="page" data-size="A4" dir="<?php echo $text_direction; ?>">
  <header class="header">
    <div class="row">
      <div class="col-6">
        <img class="logotype" src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" />
      </div>

      <div class="col-6 text-end">
        <h1><?php echo language::translate('title_order', 'Order'); ?></h1>
        <div><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></div>
        <div><?php echo !empty($order['date_created']) ? date(language::$selected['raw_date'], strtotime($order['date_created'])) : date(language::$selected['raw_date']); ?></div>
      </div>
    </div>
  </header>

  <div class="content">
    <div class="addresses">
      <div class="row">
        <div class="col-3 shipping-address">
          <div class="label"><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></div>
          <div class="value"><?php echo nl2br(functions::escape_html(reference::country($order['shipping_address']['country_code'])->format_address($order['shipping_address']))); ?></div>
        </div>

        <div class="col-3">
          <div class="label"><?php echo language::translate('title_shipping_weight', 'Shipping Weight'); ?></div>
          <div class="value"><?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_unit'])  : '-'; ?></div>
        </div>

        <div class="col-6 billing-address">
          <div class="rounded-rectangle">
            <div class="label"><?php echo language::translate('title_billing_address', 'Billing Address'); ?></div>
            <div class="value"><?php echo nl2br(functions::escape_html(reference::country($order['billing_address']['country_code'])->format_address($order['billing_address']))); ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-6">
        <div class="label"><?php echo language::translate('title_shipping_option', 'Shipping Option'); ?></div>
        <div class="value"><?php echo fallback($order['shipping_option']['name'], '-'); ?></div>

        <div class="label"><?php echo language::translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></div>
        <div class="value"><?php echo fallback($order['shipping_tracking_id'], '-'); ?></div>
      </div>

      <div class="col-6">
        <div class="label"><?php echo language::translate('title_payment_option', 'Payment Option'); ?></div>
        <div class="value"><?php echo fallback($order['payment_option']['name'], '-'); ?></div>

        <div class="label"><?php echo language::translate('title_transaction_number', 'Transaction Number'); ?></div>
        <div class="value"><?php echo fallback($order['payment_transaction_id'], '-'); ?></div>
      </div>

      <div class="col-xs-6">
        <div class="label"><?php echo language::translate('title_email', 'Email Address'); ?></div>
        <div class="value"><?php echo $order['customer']['email']; ?></div>

        <div class="row" style="margin-bottom: 0;">
          <div class="col-md-6">
            <div class="label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
            <div class="value"><?php echo $order['customer']['phone']; ?></div>
          </div>

          <div class="col-md-6">
            <div class="label"><?php echo language::translate('title_tax_id', 'Tax ID'); ?></div>
            <div class="value"><?php echo !empty($order['customer']['tax_id']) ? functions::escape_html($order['customer']['tax_id']) : '-'; ?></div>
          </div>
        </div>
      </div>
    </div>

    <table class="items table table-striped data-table">
      <thead>
        <tr>
          <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
          <th><?php echo language::translate('title_qty', 'Qty'); ?></th>
          <th class="text-end"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
          <?php if ($order['tax_total']) { ?>
          <th class="text-end"><?php echo language::translate('title_tax', 'Tax'); ?> </th>
          <?php } ?>
          <th class="text-end"><?php echo language::translate('title_sum', 'Sum'); ?></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($order['items'] as $item) { ?>
        <tr>
          <td><?php echo $item['sku']; ?></td>
          <td style="white-space: normal;"><?php echo $item['name']; ?>
<?php
    if (!empty($item['data'])) {
      foreach ($item['data'] as $key => $value) {
        if (is_array($value)) {
          echo '<br>- '.$key .': ';
          $use_comma = false;
          foreach ($value as $v) {
            if ($use_comma) echo ', ';
            echo $v;
            $use_comma = true;
          }
        } else {
          echo '<br>- '.$key .': '. $value;
        }
      }
    }
?>
          </td>
          <td><?php echo ($item['quantity'] > 1) ? '<strong>'. (float)$item['quantity'].'</strong>' : (float)$item['quantity']; ?></td>
          <td class="text-end"><?php echo currency::format($order['display_prices_including_tax'] ? ($item['price'] + $item['tax']) : $item['price'], false, $order['currency_code'], $order['currency_value']); ?></td>
          <?php if ($order['tax_total']) { ?>
          <td class="text-end"><?php echo currency::format($item['tax'], false, $order['currency_code'], $order['currency_value']); ?> (<?php echo ($item['price'] != 0 && $item['tax'] != 0) ? round($item['tax'] / $item['price'] * 100) : '0'; ?> %)</td>
          <?php } ?>
          <td class="text-end"><?php echo currency::format($item['quantity'] * ($order['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price']), false, $order['currency_code'], $order['currency_value']); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>

    <table class="order-total table data-table">
      <tbody>
        <?php foreach ($order['order_total'] as $row) { ?>
        <tr>
          <td class="text-end"><?php echo $row['title']; ?>:</td>
          <td class="text-end"><?php echo currency::format($order['display_prices_including_tax'] ? ($row['amount'] + $row['tax']) : $row['amount'], false, $order['currency_code'], $order['currency_value']); ?></td>
        </tr>
        <?php } ?>

        <?php if ($order['total_tax'] != 0) { ?>
        <tr>
          <td class="text-end"><?php echo !empty($order['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
          <td class="text-end"><?php echo currency::format($order['total_tax'], false, $order['currency_code'], $order['currency_value']); ?></td>
        </tr>
        <?php } ?>

        <tr>
          <td class="text-end"><strong><?php echo language::translate('title_grand_total', 'Grand Total'); ?>:</strong></td>
          <td class="text-end"><strong><?php echo currency::format($order['total'], false, $order['currency_code'], $order['currency_value']); ?></strong></td>
        </tr>
      </tbody>
    </table>
  </div>

  <?php if (count($order['items']) <= 10) { ?>
  <footer class="footer">

    <hr>

    <div class="row">
      <div class="col-3">
        <div class="label"><?php echo language::translate('title_address', 'Address'); ?></div>
        <div class="value"><?php echo nl2br(settings::get('store_postal_address'), false); ?></div>
      </div>

      <div class="col-3">
        <?php if (settings::get('store_phone')) { ?>
        <div class="label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
        <div class="value"><?php echo settings::get('store_phone'); ?></div>
        <?php } ?>

        <?php if (settings::get('store_tax_id')) { ?>
        <div class="label"><?php echo language::translate('title_vat_registration_id', 'VAT Registration ID'); ?></div>
        <div class="value"><?php echo settings::get('store_tax_id'); ?></div>
        <?php } ?>
      </div>

      <div class="col-3">
        <div class="label"><?php echo language::translate('title_email', 'Email'); ?></div>
        <div class="value"><?php echo settings::get('store_email'); ?></div>

        <div class="label"><?php echo language::translate('title_website', 'Website'); ?></div>
        <div class="value"><?php echo document::ilink(''); ?></div>
      </div>

      <div class="col-3">
      </div>
    </div>

  </footer>
  <?php } ?>
</section>

<button name="print" class="btn btn-default btn-lg">
  <?php echo functions::draw_fonticon('fa-print'); ?> <?php echo language::translate('title_print', 'Print'); ?>
</button>

<script>
  $('button[name="print"]').click(function(){
    window.print();
  });
</script>