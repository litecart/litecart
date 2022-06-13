<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include 'app://frontend/partials/box_customer_service_links.inc.php'; ?>
        <?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}

        <section id="box-order-history" class="card">

          <div class="card-header">
            <h1 class="card-title"><?php echo language::translate('title_order_history', 'Order History'); ?></h1>
          </div>

          <table class="table table-striped table-hover data-table">
            <thead>
            <tr>
              <th class="main"><?php echo language::translate('title_order', 'Order'); ?></th>
              <th class="text-end"></th>
              <th class="text-center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
              <th class="text-end"><?php echo language::translate('title_amount', 'Amount'); ?></th>
              <th class="text-end"><?php echo language::translate('title_date', 'Date'); ?></th>
              <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if ($orders) foreach ($orders as $order) { ?>
            <tr>
              <td><a href="<?php echo functions::escape_html($order['link']); ?>" class="lightbox-iframe"><?php echo $order['no']; ?></a></td>
              <td class="text-center"><?php echo $order['num_downloads'] ? '<a href="'. document::href_ilink('downloads') .'">'. language::translate('title_downloads', 'Downloads') .'</a>' : ''; ?></td>
              <td class="text-center"><?php echo $order['order_status']; ?></td>
              <td class="text-end"><?php echo $order['total']; ?></td>
              <td class="text-end"><?php echo $order['date_created']; ?></td>
              <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo functions::escape_html($order['printable_link']); ?>" target="_blank" title="<?php echo functions::escape_html(language::translate('title_print', 'Print')); ?>"><?php echo functions::draw_fonticon('fa-print'); ?></a></td>
            </tr>
            <?php } ?>
            </tbody>
          </table>

          <?php if ($pagination) { ?>
          <div class="card-footer">
            {{pagination}}
          </div>
          <?php } ?>
        </section>
    </div>
  </div>
</main>
