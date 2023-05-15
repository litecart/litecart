<?php

  return [
    [
      'resource' => 'category',
      'pattern' => '#^categories/([0-9]+)(/.*|/?$)#',
      'controller' => 'app://frontend/pages/category.inc.php',
      'params' => 'category_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {

        if (empty($link->query['category_id'])) return;

        $link->path = 'categories/'. $link->query['category_id'];

        $category = reference::category($link->query['category_id'], $language_code);
        foreach ($category->path as $parent_id => $parent) {
          $link->path .= '/'. functions::format_path_friendly($parent->name, $language_code);
        }

        $link->unset_query('category_id');

        return $link;
      }
    ],

    [
      'resource' => false,
      'pattern' => '#^.*-c-([0-9]+)/?$#',
      'controller' => 'app://frontend/pages/category.inc.php.inc.php',
      'params' => 'category_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
    ],
  ];
