<?php

  return [
    'b:' => [
      'pattern' => '#^'. BACKEND_ALIAS .'/?$#',
      'controller' => 'app://backend/pages/index.inc.php',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
    'b:*/*' => [
      'pattern' => '#^'. BACKEND_ALIAS .'/(.*?)/(.*?)$#',
      'controller' => 'app://backend/pages/index.inc.php',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
    'b:*' => [
      'pattern' => '#^'. BACKEND_ALIAS .'/(.*)$#',
      'controller' => 'app://backend/pages/$1.inc.php',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
  ];
