<?php

  class url_checkout_index {

    function routes() {
      return [
        [
          'pattern' => '#^checkout/(index)?$#',
          'page' => 'checkout/index',
          'params' => '',
          'endpoint' => 'frontend',
          'options' => [
            'redirect' => true,
          ],
        ],
      ];
    }

    function rewrite(ent_link $link, $language_code) {

      $link->path = 'checkout/'; // Remove index file for site root

      return $link;
    }
  }
