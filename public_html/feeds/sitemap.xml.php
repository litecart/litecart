<?php
  require_once('../includes/app_header.inc.php');
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
  
  $categories_query = $system->database->query(
    "select id, date_updated from ". DB_TABLE_CATEGORIES ."
    where status
    order by id;"
  );
  while ($category = $system->database->fetch($categories_query)) {
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id']))) .'</loc>' . PHP_EOL
             . '    <lastmod>'. date('Y-m-d', strtotime($category['date_updated'])) .'</lastmod>' . PHP_EOL
             . '    <changefreq>weekly</changefreq>' . PHP_EOL
             . '    <priority>1.0</priority>' . PHP_EOL
             . '  </url>' . PHP_EOL;
  }
  
  $products_query = $system->database->query(
    "select id, date_updated from ". DB_TABLE_PRODUCTS ."
    where not find_in_set('1', categories)
    order by id;"
  );
  while ($product = $system->database->fetch($products_query)) {
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. htmlspecialchars($system->document->link(WS_DIR_HTTP_HOME . 'product.php', array('product_id' => $product['id']))) .'</loc>' . PHP_EOL
             . '    <lastmod>'. date('Y-m-d', strtotime($product['date_updated'])) .'</lastmod>' . PHP_EOL
             . '    <changefreq>weekly</changefreq>' . PHP_EOL
             . '    <priority>0.8</priority>' . PHP_EOL
             . '  </url>' . PHP_EOL;
  }
  
  $output .= '</urlset>';
  
  if (strtolower($system->language->selected['charset']) != 'utf-8') {
    $output = utf8_encode($output);
  }
  
  header('Content-type: application/xml; charset='. $system->language->selected['charset']);
  
  echo $output;
?>