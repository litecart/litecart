<?php
  require_once('../includes/app_header.inc.php');
  
  $language_code = 'en';
  $currency_code = 'EUR';
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<rss version="2.0">' . PHP_EOL
          . '  <channel>' . PHP_EOL
          . '    <title>'. htmlspecialchars($system->settings->get('store_name')) .'</title>' . PHP_EOL
          . '    <description>'. htmlspecialchars($system->settings->get('store_name')) .'</description>' . PHP_EOL
          . '    <link>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME)) .'</link>' . PHP_EOL
          . '    <lastBuildDate>'. date('r') .'</lastBuildDate>' . PHP_EOL
          . '    <pubDate>'. date('r') .'</pubDate>' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $product = new ref_product($product['id'], $currency_code);
    
    $output .= '  <item>' . PHP_EOL
             . '    <title>'. $product->name[$language_code] .'</title>' . PHP_EOL
             . '    <description>'.
                      htmlspecialchars(
                        (count($product->images) ? '<p><img src="'. htmlspecialchars(htmlspecialchars($system->document->link(WS_DIR_IMAGES . $product->image))) .'" width="200" /></p>' : '') .
                        $product->description[$language_code]
                      )
             . '    </description>' . PHP_EOL
             . '    <link>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product->id))) .'</link>' . PHP_EOL
             . '    <pubDate>'. date('r', strtotime($product->date_created)) .'</pubDate>' . PHP_EOL
             . '  </item>' . PHP_EOL;
  }
  
  $output .= '  </channel>' . PHP_EOL
           . '</rss>';
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  header('Content-type: application/rss+xml; charset='. $system->language->selected['charset']);
  
  echo $output;
?>