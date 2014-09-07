<div id="box-order-history" class="box">
  <div class="heading">
    <h1><?php echo language::translate('title_order_history', 'Order History'); ?></h1>
  </div>
  <div class="content">
    <table width="100%" class="dataTable">
      <tr class="header">
        <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_order', 'Order'); ?></th>
        <th nowrap="nowrap" align="center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
        <th nowrap="nowrap" align="center"><?php echo language::translate('title_date', 'Date'); ?></th>
        <th nowrap="nowrap" align="center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
      </tr>
      <?php if ($orders) { ?>
      <?php foreach($orders as $order) { ?>
      <tr class="<?php if (empty($rowclass) || $rowclass == 'even') { $rowclass = 'odd'; } else { $rowclass = 'even'; } echo $rowclass; ?>">
        <td nowrap="nowrap" align="left" nowrap="nowrap"><a href="<?php echo htmlspecialchars($order['link']); ?>" class="fancybox"><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></a></td>
        <td nowrap="nowrap" align="center" nowrap="nowrap"><?php echo $order['order_status']; ?></td>
        <td nowrap="nowrap" align="right" nowrap="nowrap"><?php echo $order['date_created']; ?></td>
        <td nowrap="nowrap" align="right" nowrap="nowrap"><?php echo $order['payment_due']; ?></td>
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