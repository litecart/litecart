<?php

  class url_mcp {

    function routes() {
      return [
        [
          'pattern' => '#^mcp$#',
          'page' => 'mcp',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
      ];
    }
  }
