<?php

  return [
    [
      'resource' => '',
      'pattern' => '#^'. BACKEND_ALIAS .'/?$#',
      'endpoint' => 'backend',
      'controller' => 'app://backend/pages/index.inc.php',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],

    [
      'resource' => '*/*',
      'pattern' => '#^'. BACKEND_ALIAS .'/(.*?)/(.*?)$#',
      'endpoint' => 'backend',
      'controller' => 'app://backend/pages/index.inc.php',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],

    [
      'resource' => '*',
      'pattern' => '#^'. BACKEND_ALIAS .'/(.*)$#',
      'endpoint' => 'backend',
      'controller' => 'app://backend/pages/$1.inc.php',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
  ];
