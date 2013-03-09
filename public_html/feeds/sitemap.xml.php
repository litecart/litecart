<?php
  require_once('../includes/app_header.inc.php');
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
  
  $products_query = $system->functions->catalog_products_query(array('sort' => 'name'));
  while ($product = $system->database->fetch($products_query)) {
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product['id']))) .'</loc>' . PHP_EOL
             . '  </url>' . PHP_EOL;
  }
  
  $output .= '</urlset>';
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  header('Content-type: application/xml; charset='. $system->language->selected['charset']);
  
  echo $output;
?>