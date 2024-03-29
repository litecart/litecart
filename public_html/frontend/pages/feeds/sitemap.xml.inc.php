<?php

  @set_time_limit(300);

  language::set(settings::get('store_language_code'));

  $output = '<?xml version="1.0" encoding="'. mb_http_output() .'"?>' . PHP_EOL
          . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

  $hreflangs = [];
  foreach (language::$languages as $language) {
    if ($language['url_type'] == 'none') continue;
    $hreflangs[] = '    <xhtml:link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink('', [], false, [], $language['code']) .'" />';
  }

  $output .= implode(PHP_EOL, [
    '  <url>',
    '    <loc>'. document::ilink('') .'</loc>',
    implode(PHP_EOL, $hreflangs),
    '    <lastmod>'. date('Y-m-d') .'</lastmod>',
    '    <changefreq>daily</changefreq>',
    '    <priority>1.0</priority>',
    '  </url>',
  ]) . PHP_EOL;

  $category_iterator = function($parent_id=0) use (&$category_iterator) {
    $categories_query = functions::catalog_categories_query($parent_id);

    $output = '';

    while ($category = database::fetch($categories_query)) {

      $hreflangs = [];
      foreach (language::$languages as $language) {
        if ($language['url_type'] == 'none') continue;
        $hreflangs[] = '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('category', ['category_id' => $category['id']], false, [], $language_code) .'" />';
      }

      $images = [];
      if ($category['image']) {
        $images[] = implode(PHP_EOL, [
          '    <image:image>',
          '      <image:loc>'. document::link('storage://images/' . $category['image']) .'</image:loc>',
          '    </image:image>',
        ]);
      }

      $output .= implode(PHP_EOL, [
        '  <url>',
        '    <loc>'. document::ilink('category', ['category_id' => $category['id']]) .'</loc>',
        implode(PHP_EOL, $hreflangs),
        implode(PHP_EOL, $images),
       '    <lastmod>'. date('Y-m-d', strtotime($category['date_updated'])) .'</lastmod>',
       '    <changefreq>weekly</changefreq>',
       '    <priority>1.0</priority>',
       '  </url>',
      ]) . PHP_EOL;

      $category_iterator($category['id']);
    }

    return $output;
  };

  $output .= $category_iterator(0);

  $products_query = database::query(
    "select id, image, date_updated from ". DB_TABLE_PREFIX ."products
    where status
    order by id;"
  );

  while ($product = database::fetch($products_query)) {

    $hreflangs = [];
    foreach (language::$languages as $language) {
      if ($language['url_type'] == 'none') continue;
      $hreflangs[] = '    <xhtml:link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink('product', ['product_id' => $product['id']], false, [], $language_code) .'" />' . PHP_EOL;
    }

      $images = [];
      if ($product['image']) {
        $images[] = implode(PHP_EOL, [
          '    <image:image>',
          '      <image:loc>'. document::link('storage://images/' . $product['image']) .'</image:loc>',
          '    </image:image>',
        ]);
      }

    $output .= implode(PHP_EOL, [
      '  <url>',
      '    <loc>'. document::ilink('product', ['product_id' => $product['id']]) .'</loc>',
      implode(PHP_EOL, $hreflangs),
      implode(PHP_EOL, $images),
      '    <lastmod>'. date('Y-m-d', strtotime($product['date_updated'])) .'</lastmod>',
      '    <changefreq>weekly</changefreq>',
      '    <priority>0.8</priority>',
      '  </url>',
	]) . PHP_EOL;
  }

  $output .= '</urlset>';

  ob_clean();
  header('Content-type: application/xml; charset='. mb_http_output());
  echo $output;
  exit;
