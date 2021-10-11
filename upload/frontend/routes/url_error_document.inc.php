<?php

  class url_error_document {

    function routes() {
      return [
        [
          'endpoint' => 'frontend',
          'pattern' => '#^error_document$#',
          'page' => 'error_document',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
      ];
    }

    function rewrite(ent_link $link, $language_code) {

      $link->path = ''; // Remove index file for site root

      return $link;
    }
  }
