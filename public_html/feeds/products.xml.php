<?php
  require_once('../includes/app_header.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<products>' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $product = new ref_product($product['id'], $system->currency->selected['code']);
    
    $output .= '  <product>' . PHP_EOL
             . '    <name>'. htmlspecialchars($product->name[$system->language->selected['code']]) .'</name>' . PHP_EOL
             . '    <manufacturer_name>'. htmlspecialchars($product->manufacturer['name']) .'</manufacturer_name>' . PHP_EOL
             . '    <short_description>'. htmlspecialchars($product->short_description[$system->language->selected['code']]) .'</short_description>' . PHP_EOL
             . '    <image>'. ($product->image ? htmlspecialchars($system->document->link(WS_DIR_IMAGES . $product->image)) : '') .'</image>' . PHP_EOL
             . '    <sku>'. $product->quantity .'</sku>' . PHP_EOL
             . '    <quantity>'. $product->quantity .'</quantity>' . PHP_EOL
             . '    <price>'. $system->currency->calculate($product->price, $system->currency->selected['code']) .'</price>' . PHP_EOL
             . '    <currency>'. $system->currency->selected['code'] .'</currency>' . PHP_EOL
             . '    <campaign_price>'. (!empty($product->campaign) ? $system->currency->calculate($product->campaign['price'], $system->currency->selected['code']) : '') .'</campaign_price>' . PHP_EOL
             . '    <link>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product->id))) .'</link>' . PHP_EOL
             . '  </product>' . PHP_EOL;
  }
  
  $output .= '</products>';
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  header('Content-type: application/xml; charset='. $system->language->selected['charset']);
  
  echo $output;
?>