<?php

  class url_order_process {

    function routes() {
      return [
        [
          'pattern' => '#^order_process$#',
          'page' => 'order_process',
          'params' => 'page_id=$1',
          'options' => [
            'redirect' => false,
            'post_security' => false,
          ],
        ],
      ];
    }
  }
