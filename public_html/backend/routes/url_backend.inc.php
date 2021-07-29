<?php

  class url_backend {

    function routes() {
      return [
        [
          'pattern' => '#^'. BACKEND_ALIAS .'/(.*?)/(.*?)$#',
          'endpoint' => 'backend',
          'page' => 'index',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],        [
          'pattern' => '#^'. BACKEND_ALIAS .'/(login|logout|search_results.json)$#',
          'endpoint' => 'backend',
          'page' => '$1',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
        [
          'pattern' => '#^'. BACKEND_ALIAS .'/?$#',
          'endpoint' => 'backend',
          'page' => 'index',
          'params' => '',
          'options' => [
            'redirect' => false,
          ],
        ],
      ];
    }

    function rewrite(ent_link $link, $language_code) {

      $link->path = ''; // Remove index file for site root

      if (!empty($_GET['app'])) {
        $link->path .= $_GET['app'].'/';
        $link->unset_query('app');
      }

      if (!empty(__DOC__)) {
        $link->path .= __DOC__;
        $link->unset_query('doc');
      }

      return $link;
    }
  }
