<?php

  $sitemap_cache_token = cache::TOKEN('sitemap', ['language'], 'file');
  if (!$output = cache::get($sitemap_cache_token, 43200)) {

    @set_time_limit(300);

    language::set(settings::get('store_language_code'));

    $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
            . '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="https://www.w3.org/1999/xhtml">' . PHP_EOL;

    $hreflangs = '';
    foreach (language::$languages as $language) {
      if ($language['url_type'] == 'none') continue;
      $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('', [], false, [], $language['code']) .'" />' . PHP_EOL;
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
        foreach (language::$languages as $language) {
          if ($language['url_type'] == 'none') continue;
          $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('category', ['category_id' => $category['id']], false, [], $language['code']) .'" />' . PHP_EOL;
        }

        $output .= '  <url>' . PHP_EOL
                 . '    <loc>'. document::ilink('category', ['category_id' => $category['id']]) .'</loc>' . PHP_EOL
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
      "select id, date_updated from ". DB_TABLE_PREFIX ."products
      where status
      order by id;"
    );

    while ($product = database::fetch($products_query)) {

      $hreflangs = '';
      foreach (language::$languages as $language) {
        if ($language['url_type'] == 'none') continue;
        $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('product', ['product_id' => $product['id']], false, [], $language['code']) .'" />' . PHP_EOL;
      }

      $output .= '  <url>' . PHP_EOL
               . '    <loc>'. document::ilink('product', ['product_id' => $product['id']]) .'</loc>' . PHP_EOL
               . $hreflangs
               . '    <lastmod>'. date('Y-m-d', strtotime($product['date_updated'])) .'</lastmod>' . PHP_EOL
               . '    <changefreq>weekly</changefreq>' . PHP_EOL
               . '    <priority>0.8</priority>' . PHP_EOL
               . '  </url>' . PHP_EOL;
    }

    $output .= '</urlset>';

    cache::set($sitemap_cache_token, $output);
  }

  header('Content-type: application/xml; charset='. language::$selected['charset']);

  echo $output;
  exit;
