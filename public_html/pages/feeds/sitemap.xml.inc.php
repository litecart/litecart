<?php
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
  
  $categories_query = database::query(
    "select id, date_updated from ". DB_TABLE_CATEGORIES ."
    where status
    order by id;"
  );
  while ($category = database::fetch($categories_query)) {
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. document::href_ilink('category', array('category_id' => $category['id'])) .'</loc>' . PHP_EOL
             . '    <lastmod>'. date('Y-m-d', strtotime($category['date_updated'])) .'</lastmod>' . PHP_EOL
             . '    <changefreq>weekly</changefreq>' . PHP_EOL
             . '    <priority>1.0</priority>' . PHP_EOL
             . '  </url>' . PHP_EOL;
  }
  
  $products_query = database::query(
    "select id, date_updated from ". DB_TABLE_PRODUCTS ."
    where status
    order by id;"
  );
  while ($product = database::fetch($products_query)) {
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. document::href_ilink('product', array('product_id' => $product['id'])) .'</loc>' . PHP_EOL
             . '    <lastmod>'. date('Y-m-d', strtotime($product['date_updated'])) .'</lastmod>' . PHP_EOL
             . '    <changefreq>weekly</changefreq>' . PHP_EOL
             . '    <priority>0.8</priority>' . PHP_EOL
             . '  </url>' . PHP_EOL;
  }
  
  $output .= '</urlset>';
  
  mb_convert_variables(language::$selected['charset'], 'UTF-8', $output);
  
  header('Content-type: application/xml; charset='. language::$selected['charset']);
  
  echo $output;
  exit;
?>