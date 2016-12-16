<main id="content">
  <!--snippet:notices-->

  <div id="box-order-success" class="box">

    <h1 class="title"><?php echo language::translate('title_order_completed', 'Your order is successfully completed!'); ?></h1>

    <p><?php echo language::translate('description_order_completed', 'Thank you for shopping in our store. We will process your order shortly.'); ?></p>

    <p><a class="lightbox-iframe" href="<?php echo htmlspecialchars($printable_link); ?>"><?php echo language::translate('description_click_printable_copy', 'Click here for a printable copy'); ?></a></p>

    <?php if ($payment_receipt) { ?>
    <?php echo $payment_receipt; ?>
    <?php } ?>

    <?php if ($order_success_modules_output) { ?>
    <?php echo $order_success_modules_output; ?>
    <?php } ?>
  </div>
</main>
