<?php
  require_once('includes/app_header.inc.php');
  header('X-Robots-Tag: noindex');
  
  $payment = new payment();
  
  $order = new ctrl_order('resume');
  
  if (empty($order->data['id'])) die('Error: Missing session order object');
  
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  document::$snippets['title'][] = language::translate('title_order_success', 'Order Success');
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';
  
  breadcrumbs::add(language::translate('title_checkout', 'Checkout'), document::link('checkout.php'));
  breadcrumbs::add(language::translate('title_order_success', 'Order Success'), document::link());
  
  cart::reset();
  
  functions::draw_fancybox('a.fancybox', array(
    'type'          => 'iframe',
    'padding'       => '40',
    'width'         => 600,
    'height'        => 800,
    'titlePosition' => 'inside',
    'transitionIn'  => 'elastic',
    'transitionOut' => 'elastic',
    'speedIn'       => 600,
    'speedOut'      => 200,
    'overlayShow'   => true
  ));
  
?>
<h1><?php echo language::translate('title_order_completed', 'Your order is successfully completed!'); ?></h1>
<?php echo language::translate('description_order_completed', 'Thank you for shopping in our store. We will process your order shortly.'); ?>
<p><a href="<?php echo document::href_link('printable_order_copy.php', array('order_id' => $order->data['id'], 'checksum' => functions::general_order_public_checksum($order->data['id']), 'media' => 'print')); ?>" class="fancybox"><?php echo language::translate('description_click_printable_copy', 'Click here for a printable copy'); ?></a></p>
<?php
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_success.inc.php');
  $order_success = new mod_order_success();
  
  echo $order_success->process();
  
  echo $payment->run('receipt');
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>