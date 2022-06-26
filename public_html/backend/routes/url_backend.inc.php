<?php

  return [
    '' => [
      'pattern' => '#^'. BACKEND_ALIAS .'/?$#',
      'endpoint' => 'backend',
      'controller' => 'index',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
    '*/*' => [
      'pattern' => '#^'. BACKEND_ALIAS .'/(.*?)/(.*?)$#',
      'endpoint' => 'backend',
      'controller' => 'index',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
    '*' => [
      'pattern' => '#^'. BACKEND_ALIAS .'/(.*)$#',
      'endpoint' => 'backend',
      'controller' => '$1',
      'params' => '',
      'options' => [
        'redirect' => false,
      ],
    ],
  ];
