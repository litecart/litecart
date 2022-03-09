<?php

  class url_push_jobs {

    function routes() {
      return [
        [
          'pattern' => '#^push_jobs$#',
          'page' => 'push_jobs',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
      ];
    }
  }
