<div id="box-order-success" class="box">
  <h1 class="title"><?php echo language::translate('title_order_completed', 'Your order is successfully completed!'); ?></h1>
  <div class="content">
    <p><?php echo language::translate('description_order_completed', 'Thank you for shopping in our store. We will process your order shortly.'); ?></p>
    <p><a href="<?php echo htmlspecialchars($printable_link); ?>" class="fancybox"><?php echo language::translate('description_click_printable_copy', 'Click here for a printable copy'); ?></a></p>

    <?php if ($payment_receipt) { ?>
    <?php echo $payment_receipt; ?>
    <?php } ?>

    <?php if ($order_success_modules_output) { ?>
    <?php echo $order_success_modules_output; ?>
    <?php } ?>
  </div>
</div>
