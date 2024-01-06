<?php

  return [
    'f:brands' => [
      'pattern' => '#^brands/?$#',
      'controller' => 'app://frontend/pages/brands.inc.php',
      'params' => 'brand_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {
        $link->path = 'brands/';
        return $link;
      }
    ],

    'f:brand' => [
      'pattern' => '#^brands/([0-9]+)(/.*|/?$)#',
      'controller' => 'app://frontend/pages/brand.inc.php',
      'params' => 'brand_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {

        if (empty($link->query['brand_id'])) return;

        $brand = reference::brand($link->query['brand_id'], $language_code);
        if (empty($brand->id)) return $link;

        $link->path = 'brands/'. $brand->id .'/'. functions::format_path_friendly($brand->name, $language_code);
        $link->unset_query('brand_id');

        return $link;
      }
    ],

    null => [
      'pattern' => '#^.*-m-([0-9]+)/?$#',
      'controller' => 'app://frontend/pages/brand.inc.php',
      'params' => 'brand_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
    ],
  ];
