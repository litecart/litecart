<?php

  $manifest = [
    'name' => settings::get('store_name'),
    'start_url' => document::ilink(''),
    'display' => 'standalone',
    'background_color' => '#E9E9EE',
    'icons' => [
      [
        'src' => document::link('storage://images/favicons/favicon.ico'),
        'sizes' => '32x32 48x48 64x64 96x96',
      ],
      [
        'src' => document::link('storage://images/favicons/favicon-128x128.png'),
        'sizes' => '128x128',
      ],
      [
        'src' => document::link('storage://images/favicons/favicon-192x192.png'),
        'sizes' => '192x192',
      ],
      [
        'src' => document::link('storage://images/favicons/favicon-256x256.png'),
        'sizes' => '256x256',
      ]
    ],
  ];

  header('Content-Type: application/manifest+json; charset=utf-8');
  echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  exit;
