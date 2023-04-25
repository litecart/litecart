<?php

  return [
    '' => [
      'pattern' => '#^(?:index\.php)?$#',
      'controller' => 'app://frontend/pages/index.inc.php',
      'params' => '',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {
        $link->path = ''; // Remove index file for site root
        return $link;
      }
    ],
  ];
