<?php

  return [
    'checkout' => [
      'pattern' => '#^checkout/(index)?$#',
      'controller' => 'checkout/index',
      'params' => '',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {
        $link->path = 'checkout/'; // Remove index file for site root
        return $link;
      }
    ],
  ];
