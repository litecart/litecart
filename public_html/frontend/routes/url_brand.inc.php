<?php

  return [
    'brand' => [
      'pattern' => '#^.*-[mb]-([0-9]+)/?$#',
      'controller' => 'app://frontend/pages/brand.inc.php.inc.php',
      'params' => 'brand_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {

        if (empty($link->query['brand_id'])) return;

        $brand = reference::brand($link->query['brand_id'], $language_code);
        if (empty($brand->id)) return $link;

        $link->path = functions::format_path_friendly($brand->name, $language_code) .'-b-'. $brand->id .'/';
        $link->unset_query('brand_id');

        return $link;
      }
    ],
  ];
