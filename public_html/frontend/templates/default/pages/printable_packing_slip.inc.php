<style>
.logotype {
  max-width: 320px;
  max-height: 70px;
}

h1 {
  margin: 0;
  border: none;
}

.addresses .row > :not(.shipping-address) {
  margin-top: 4mm;
}

.rounded-rectangle {
  border: 1px solid #000;
  border-radius: 2mm;
  padding: 4mm;
  margin-inline-start: -15px;
  margin-bottom: 3mm;
}
.rounded-rectangle .value {
  margin: 0 !important;
}

.items tr th:last-child {
  width: 30mm;
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

table.items tbody tr:nth-child(11) {
  page-break-before: always;
}
</style>

<main>
  <section class="page" data-size="A4">
    <header class="header">
      <div class="row">
        <div class="col-6">
          <img class="logotype" src="<?php echo document::link('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('site_name'); ?>" />
        </div>

        <div class="col-6 text-end">
          <h1><?php echo language::translate('title_packing_slip', 'Packing Slip'); ?></h1>
          <div><?php echo language::translate('title_order', 'Order'); ?> <?php echo $order['no']; ?></div>
          <div><?php echo !empty($order['date_created']) ? date(language::$selected['raw_date'], strtotime($order['date_created'])) : date(language::$selected['raw_date']); ?></div>
        </div>
      </div>
    </header>

    <div class="content">
      <div class="addresses">
        <div class="row">
          <div class="col-6">
            <div class="label"><?php echo language::translate('title_shipping_option', 'Shipping Option'); ?></div>
            <div class="value"><?php echo fallback($order['shipping_option']['name'], '-'); ?></div>

            <div class="label"><?php echo language::translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></div>
            <div class="value"><?php echo fallback($order['shipping_tracking_id'], '-'); ?></div>

            <div class="label"><?php echo language::translate('title_shipping_weight', 'Shipping Weight'); ?></div>
            <div class="value"><?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_unit'])  : '-'; ?></div>
          </div>

          <div class="col-6 shipping-address">
            <div class="rounded-rectangle">
              <div class="label"><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></div>
              <div class="value"><?php echo nl2br(reference::country($order['customer']['shipping_address']['country_code'])->format_address($order['customer']['shipping_address'])); ?></div>
            </div>

            <div class="label"><?php echo language::translate('title_email', 'Email'); ?></div>
            <div class="value"><?php echo fallback($order['customer']['email'], '-'); ?></div>

            <div class="label"><?php echo language::translate('title_phone', 'Phone'); ?></div>
            <div class="value"><?php echo fallback($order['customer']['shipping_address']['phone'], '-'); ?></div>
          </div>
        </div>
      </div>

      <table class="items table table-striped data-table">
        <thead>
          <tr>
            <th><?php echo language::translate('title_qty', 'Qty'); ?></th>
            <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($order['items'] as $item) { ?>
          <tr>
            <td><?php echo (float)$item['quantity']; ?></td>
            <td><?php echo $item['sku']; ?></td>
            <td style="white-space: normal;"><?php echo $item['name']; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if (count($order['items']) <= 10) { ?>
    <footer class="footer">

      <hr />

      <div class="row">
        <div class="col-3">
          <div class="label"><?php echo language::translate('title_address', 'Address'); ?></div>
          <div class="value"><?php echo nl2br(settings::get('site_postal_address')); ?></div>
        </div>

        <div class="col-3">
          <?php if (settings::get('site_phone')) { ?>
          <div class="label"><?php echo language::translate('title_phone', 'Phone'); ?></div>
          <div class="value"><?php echo settings::get('site_phone'); ?></div>
          <?php } ?>

          <?php if (settings::get('site_tax_id')) { ?>
          <div class="label"><?php echo language::translate('title_vat_registration_id', 'VAT Registration ID'); ?></div>
          <div class="value"><?php echo settings::get('site_tax_id'); ?></div>
          <?php } ?>
        </div>

        <div class="col-3">
          <div class="label"><?php echo language::translate('title_email', 'Email'); ?></div>
          <div class="value"><?php echo settings::get('site_email'); ?></div>

          <div class="label"><?php echo language::translate('title_website', 'Website'); ?></div>
          <div class="value"><?php echo document::ilink(''); ?></div>
        </div>

        <div class="col-3">
        </div>
      </div>
    </footer>
    <?php } ?>
  </section>
</main>

<script>
  document.title = "<?php echo functions::escape_js(language::translate('title_packing_slip', 'Packing Slip')); ?> #<?php echo $order['id']; ?>";
</script>