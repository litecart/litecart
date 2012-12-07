<?php
  require_once('../includes/app_header.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'product.inc.php');
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<products>' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $product = new ref_product($product['id'], $system->currency->selected['code']);
    
    $output .= '  <product>' . PHP_EOL
             . '    <name>'. htmlspecialchars($product->name[$system->language->selected['code']]) .'</name>' . PHP_EOL;
    
    if (count($product->categories)) {
      $output .= '  <categories>' . PHP_EOL;
      foreach ($product->categories as $category_id) {
        $category = $system->database->fetch($system->database->query("select name from ". DB_TABLE_CATEGORIES_INFO ." where category_id = '". (int)$category_id ."' and language_code = '". $system->language->selected['code'] ."' limit 1;"));
        $output .= '    <category>'. htmlspecialchars($category['name']) .'</category>' . PHP_EOL;
      }
      $output .= '  </categories>' . PHP_EOL;
    }
    
    $output .= '    <brand>'. htmlspecialchars($product->manufacturer['name']) .'</brand>' . PHP_EOL
             . '    <gender></gender>' . PHP_EOL
             . '    <description>'. htmlspecialchars($product->description[$system->language->selected['code']]) .'</description>' . PHP_EOL
             . '    <graphicUrl>'. ($product->image ? htmlspecialchars($system->document->link(WS_DIR_IMAGES . $product->image)) : '') .'</graphicUrl>' . PHP_EOL
             . '    <price>'. $system->currency->calculate($product->price, $system->currency->selected['code']) .'</price>' . PHP_EOL
             . '    <shippingPrice></shippingPrice>' . PHP_EOL
             . '    <currency>'. $system->currency->selected['code'] .'</currency>' . PHP_EOL
             . '    <campaign_price>'. (!empty($product->campaign) ? $system->currency->calculate($product->campaign['price'], $system->currency->selected['code']) : '') .'</campaign_price>' . PHP_EOL
             . '    <productUrl>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product->id))) .'</productUrl>' . PHP_EOL
             . '  </product>' . PHP_EOL;
  }
  
  $output .= '</products>';
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  header('Content-type: application/xml; charset='. $system->language->selected['charset']);
  
  echo $output;
?>