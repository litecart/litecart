<?php

  language::set(settings::get('store_language_code'));

  $output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
          . '<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

  $hreflangs = '';
  if (settings::get('seo_links_language_prefix')) {
    foreach (array_keys(language::$languages) as $language_code) {
      $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('', [], false, [], $language_code) .'" />' . PHP_EOL;
    }
  }

  $output .= '  <url>' . PHP_EOL
           . '    <loc>'. document::ilink('') .'</loc>' . PHP_EOL
           . $hreflangs
           . '    <lastmod>'. date('Y-m-d') .'</lastmod>' . PHP_EOL
           . '    <changefreq>daily</changefreq>' . PHP_EOL
           . '    <priority>1.0</priority>' . PHP_EOL
           . '  </url>' . PHP_EOL;

  $category_iterator = function($parent_id=0, $_this) {
    $categories_query = functions::catalog_categories_query($parent_id);

    $output = '';

    while ($category = database::fetch($categories_query)) {

      $hreflangs = '';
      if (settings::get('seo_links_language_prefix')) {
        foreach (array_keys(language::$languages) as $language_code) {
          $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('category', ['category_id' => $category['id']], false, [], $language_code) .'" />' . PHP_EOL;
        }
      }

      $images = '';
      if ($category['image']) {
        $images = '    <image:image>' . PHP_EOL
                . '      <image:loc>'. document::link(WS_DIR_STORAGE . 'images/' . $category['image']) .'</image:loc>' . PHP_EOL
                . '    </image:image>' . PHP_EOL;
      }

      $output .= '  <url>' . PHP_EOL
               . '    <loc>'. document::ilink('category', ['category_id' => $category['id']]) .'</loc>' . PHP_EOL
               . $hreflangs
               . $images
               . '    <lastmod>'. date('Y-m-d', strtotime($category['date_updated'])) .'</lastmod>' . PHP_EOL
               . '    <changefreq>weekly</changefreq>' . PHP_EOL
               . '    <priority>1.0</priority>' . PHP_EOL
               . '  </url>' . PHP_EOL;

      $_this($category['id'], $_this);
    }

    return $output;
  };

  $output .= $category_iterator(0, $category_iterator);

  $products_query = database::query(
    "select id, image, date_updated from ". DB_TABLE_PREFIX ."products
    where status
    order by id;"
  );

  while ($product = database::fetch($products_query)) {

    $hreflangs = '';
    if (settings::get('seo_links_language_prefix')) {
      foreach (array_keys(language::$languages) as $language_code) {
        $hreflangs .= '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('product', ['product_id' => $product['id']], false, [], $language_code) .'" />' . PHP_EOL;
      }
    }

      $images = '';
      if ($product['image']) {
        $images = '    <image:image>' . PHP_EOL
                . '      <image:loc>'. document::link(WS_DIR_STORAGE . 'images/' . $product['image']) .'</image:loc>' . PHP_EOL
                . '    </image:image>' . PHP_EOL;
      }


    $output .= '  <url>' . PHP_EOL
             . '    <loc>'. document::ilink('product', ['product_id' => $product['id']]) .'</loc>' . PHP_EOL
             . $hreflangs
             . $images
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
