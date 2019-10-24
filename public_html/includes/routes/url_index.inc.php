<?php

  class url_index {

    function routes() {
      return array(
        array(
          'pattern' => '#^(?:index\.php)?$#',
          'page' => 'index',
          'params' => '',
          'options' => array(
            'redirect' => true,
          ),
        ),
      );
    }

    function rewrite(ent_link $link, $language_code) {

      $link->path = ''; // Remove index file for site root

      return $link;
    }
  }
