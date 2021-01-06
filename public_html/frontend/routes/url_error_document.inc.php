<?php

  class url_error_document {

    function routes() {
      return array(
        array(
          'pattern' => '#^error_document$#',
          'page' => 'error_document',
          'params' => '',
          'options' => array(
            'redirect' => false,
          ),
        ),
      );
    }

    function rewrite(ent_link $link, $language_code) {

      $link->path = ''; // Remove index file for site root

      return $link;
    }
  }
