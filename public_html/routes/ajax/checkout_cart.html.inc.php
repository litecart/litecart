<?php
  if (realpath(__FILE__) == realpath($_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'])) {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
  }
  
  if (cart::$data['total']['items'] == 0) {
    echo '<p><em>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</em></p>' . PHP_EOL
       . '<p><a href="javascript:history.go(-1);">&lt;&lt; '. language::translate('title_back', 'Back') .'</a></p>';
    return;
  }
  
  $box_checkout_cart = new view();
  
  $box_checkout_cart->snippets['items'] = array();
  foreach (cart::$data['items'] as $key => $item) {
    $box_checkout_cart->snippets['items'][$item['id']] = array(
      'items' => cart::$data['items'],
      'link' => document::ilink('product', array('product_id' => $item['product_id'])),
      'thumbnail' => functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $item['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 160, 160, 'FIT_USE_WHITESPACING'),
      'name' => $item['name'][language::$selected['code']],
      'sku' => $item['sku'],
      'options' => array(),
      'price' => $item['price'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => $item['quantity'],
    );
    if (!empty($item['options'])) {
      foreach ($item['options'] as $k => $v) {
        $box_checkout_cart->snippets['items'][$item['id']]['options'][] = $k .': '. $v;
      }
    }
  }
  
  echo $box_checkout_cart->stitch('box_checkout_cart');
  
?>
