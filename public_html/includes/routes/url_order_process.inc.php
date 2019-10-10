<?php

  class url_order_process {

    function routes() {
      return array(
        array(
          'pattern' => '#^order_process$#',
          'page' => 'order_process',
          'params' => 'page_id=$1',
          'options' => array(
            'redirect' => false,
            'post_security' => false,
          ),
        ),
      );
    }
  }
