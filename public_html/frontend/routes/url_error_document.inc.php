<?php

  return [
    [
      'resource' => 'error_document',
      'pattern' => '#^error_document$#',
      'controller' => 'app://frontend/pages/error_document.inc.php',
      'params' => '',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => false,
      ],
      'rewrite' => function(ent_link $link, $language_code) {
        $link->path = ''; // Remove index file for site root
        return $link;
      }
    ],
  ];
