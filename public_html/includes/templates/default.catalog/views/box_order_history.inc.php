<div id="box-order-history" class="box">
  <h1 class="title"><?php echo language::translate('title_order_history', 'Order History'); ?></h1>
  <div class="content">
    <table width="100%" class="dataTable">
      <tr class="header">
        <th width="100%"><?php echo language::translate('title_order', 'Order'); ?></th>
        <th style="text-align: center;"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
        <th style="text-align: center;"><?php echo language::translate('title_date', 'Date'); ?></th>
        <th style="text-align: center;"><?php echo language::translate('title_amount', 'Amount'); ?></th>
      </tr>
      <?php if ($orders) { ?>
      <?php foreach($orders as $order) { ?>
      <tr class="row">
        <td><a href="<?php echo htmlspecialchars($order['link']); ?>" class="fancybox"><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></a></td>
        <td style="text-align: center;"><?php echo $order['order_status']; ?></td>
        <td style="text-align: right;"><?php echo $order['date_created']; ?></td>
        <td style="text-align: right;"><?php echo $order['payment_due']; ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td colspan="4"><em><?php echo language::translate('title_nothing_found', 'Nothing found'); ?></em></td>
      </tr>
    <?php } ?>
    </table>
    <?php echo $pagination; ?>
  </div>
</div>