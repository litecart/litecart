<?php

  class url_index {

    function routes() {
      return array(
        array(
          'pattern' => '#^(?:index\.php)?$#',
          'page' => 'index',
          'params' => '',
          'redirect' => true,
        ),
      );
    }

    function rewrite(object $link, $language_code) {

      $link->path = ''; // Remove index file for site root

      return $link;
    }
  }
