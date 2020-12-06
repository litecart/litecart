<?php

  language::set(settings::get('store_language_code'));

  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

  $hreflangs = '';
  if (settings::get('seo_links_language_prefix')) {
    foreach (array_keys(language::$languages) as $language_code) {
      $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('', array(), false, array(), $language_code) .'" />' . PHP_EOL;
    }
  }

  $output .= '  <url>' . PHP_EOL
           . '    <loc>'. document::ilink('') .'</loc>' . PHP_EOL
           . $hreflangs
           . '    <lastmod>'. date('Y-m-d') .'</lastmod>' . PHP_EOL
           . '    <changefreq>daily</changefreq>' . PHP_EOL
           . '    <priority>1.0</priority>' . PHP_EOL
           . '  </url>' . PHP_EOL;

  $category_iterator = function($parent_id=0) use (&$category_iterator) {
    $categories_query = functions::catalog_categories_query($parent_id);

    $output = '';

    while ($category = database::fetch($categories_query)) {

      $hreflangs = '';
      if (settings::get('seo_links_language_prefix')) {
        foreach (array_keys(language::$languages) as $language_code) {
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

      $category_iterator($category['id']);
    }

    return $output;
  };

  $output .= $category_iterator(0);

  $products_query = database::query(
    "select id, date_updated from ". DB_TABLE_PRODUCTS ."
    where status
    order by id;"
  );

  while ($product = database::fetch($products_query)) {

    $hreflangs = '';
    if (settings::get('seo_links_language_prefix')) {
      foreach (array_keys(language::$languages) as $language_code) {
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

  $output = language::convert_characters($output, language::$selected['charset'], 'UTF-8');

  header('Content-type: application/xml; charset=UTF-8');

  echo $output;
  exit;
