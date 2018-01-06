<main id="content">
  {snippet:notices}

  <div id="box-order-success" class="box text-center">

    <h1 class="title"><?php echo strtr(language::translate('title_order_completed', 'Your order #%order_id is successfully completed!'), array('%order_id' => $order['id'])); ?></h1>

    <p><?php echo language::translate('description_order_completed', 'Thank you for your purchase. An order confirmation email has been sent. We will process your order shortly.'); ?></p>

    <ul class="items list-unstyled">
      <?php foreach ($order['items'] as $item) { ?>
      <li class="item" data-id="<?php echo $item['product_id']; ?>" data-sku="<?php echo $item['sku']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo currency::format_raw($item['price'], $order['currency_code'], $order['currency_value']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">
        <?php echo $item['quantity']; ?> x <?php echo $item['name']; ?>
      </li>
      <?php } ?>
    </ul>

    <p><strong><?php echo language::translate('title_order_total', 'Order Total'); ?></strong>: <?php echo currency::format($order['payment_due'], false, $order['currency_code'], $order['currency_value']); ?>

    <p><a href="<?php echo htmlspecialchars($printable_link); ?>" target="_blank"><?php echo language::translate('description_click_printable_copy', 'Click here for a printable copy'); ?></a></p>

    <?php if ($payment_receipt) echo $payment_receipt; ?>

    <?php if ($order_success_modules_output) echo $order_success_modules_output; ?>
  </div>
</main>
