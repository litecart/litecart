<?php

  return [
    'error_document' => [
      'endpoint' => 'frontend',
      'pattern' => '#^error_document$#',
      'controller' => 'error_document',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
      'rewrite' => function(ent_link $link, $language_code) {
        $link->path = ''; // Remove index file for site root
        return $link;
      }
    ],
  ];
