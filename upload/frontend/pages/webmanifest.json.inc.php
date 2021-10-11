<?php

  $manifest = [
    'name' => settings::get('site_name'),
    'start_url' => document::ilink(''),
    'display' => 'standalone',
    'background_color' => '#E9E9EE',
    'icons' => [
      [
        'src' => document::link(WS_DIR_STORAGE . 'images/favicon.ico'),
        'sizes' => '32x32 48x48 64x64 96x96',
      ],
      [
        'src' => document::link(WS_DIR_STORAGE . 'images/favicon-128x128.png'),
        'sizes' => '128x128',
      ],
      [
        'src' => document::link(WS_DIR_STORAGE . 'images/favicon-192x192.png'),
        'sizes' => '192x192',
      ],
      [
        'src' => document::link(WS_DIR_STORAGE . 'images/favicon-256x256.png'),
        'sizes' => '256x256',
      ]
    ],
  ];

  header('Content-Type: application/manifest+json; charset=utf-8');
  echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  exit;
