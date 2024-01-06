<?php

  $output = [];

  // General
  $output['general'] = [
    'User-agent: *',
    'Allow: /',
    'Disallow: /storage/cache/*',
  ];

  // Sitemap
  $output['sitemap'] = 'Sitemap: '. document::ilink('sitemap.xml');

  // Output
  ob_clean();
  header('Content-Type: text/plain;charset='. mb_http_output());

  foreach ($output as $block) {
    if (is_array($block)) {
      echo implode(PHP_EOL, $block);
    } else {
      echo $block;
    }
    echo PHP_EOL . PHP_EOL;
  }

  exit;
