<?php

  class url_backend {

    function routes() {
      return [
        [
          'pattern' => '#^'. BACKEND_ALIAS .'/?$#',
          'endpoint' => 'backend',
          'page' => 'index',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
        [
          'pattern' => '#^'. BACKEND_ALIAS .'/(.*?)/(.*?)$#',
          'endpoint' => 'backend',
          'page' => 'index',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
        [
          'pattern' => '#^'. BACKEND_ALIAS .'/(.*)$#',
          'endpoint' => 'backend',
          'page' => '$1',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
      ];
    }
  }
