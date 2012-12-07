<?php
  require_once('includes/app_header.inc.php');
  header('X-Robots-Tag: noindex');
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'order.inc.php');
  $order = new ctrl_order('resume');
  
  if (empty($order->data['id'])) die('Error: Missing session order object');
  
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  $system->document->snippets['title'][] = $system->language->translate('title_order_success', 'Order Success');
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
  
  $system->breadcrumbs->add($system->language->translate('title_checkout', 'Checkout'), $system->document->link('checkout.php'));
  $system->breadcrumbs->add($system->language->translate('title_order_success', 'Order Success'), $system->document->link());
  
  $system->cart->reset();
  
  $system->functions->draw_fancybox('a.fancybox', array(
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
<h1><?php echo $system->language->translate('title_order_completed', 'Your order is successfully completed!'); ?></h1>
<?php echo $system->language->translate('description_order_completed', 'Thank you for shopping in our store. We will process your order shortly.'); ?>
<p><a href="<?php echo $system->document->href_link('printable_order_copy.php', array('order_id' => $order->data['id'], 'checksum' => $system->functions->general_order_public_checksum($order->data['id']), 'media' => 'print')); ?>" class="fancybox"><?php echo $system->language->translate('description_click_printable_copy', 'Click here for a printable copy'); ?></a></p>
<?php
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_success.inc.php');
  $order_success = new order_success();
  
  echo $order_success->process();

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>