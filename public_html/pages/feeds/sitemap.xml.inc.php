<?php

  language::set(settings::get('store_language_code'));
  
  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<urlset xmlns="http://www.sitemaps.org/scheman/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;
  
  $categories_query = database::query(
    "select id, date_updated from ". DB_TABLE_CATEGORIES ."
    where status
    order by id;"
  );
  while ($category = database::fetch($categories_query)) {

    $hreflangs = '';
    if (settings::get('seo_links_language_prefix')) {
      foreach (array_keys(language::$languages) as $language_code) {
        if ($language_code == settings::get('store_language_code')) continue;
        $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('category', array('category_id' => $category['id']), false, array(), $language_code) .'" />' . PHP_EOL;
      }
    }
    
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. document::ilink('category', array('category_id' => $category['id'])) .'</loc>' . PHP_EOL
             . $hreflangs
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
  
    $hreflangs = '';
    if (settings::get('seo_links_language_prefix')) {
      foreach (array_keys(language::$languages) as $language_code) {
        if ($language_code == settings::get('store_language_code')) continue;
        $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('product', array('product_id' => $product['id']), false, array(), $language_code) .'" />' . PHP_EOL;
      }
    }
    
    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. document::ilink('product', array('product_id' => $product['id'])) .'</loc>' . PHP_EOL
             . $hreflangs
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