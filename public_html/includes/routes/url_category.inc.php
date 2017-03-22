<?php
  class url_category {

    function routes() {
      return array(
        array(
          'pattern' => '#^.*-c-([0-9]+)/?$#',
          'page' => 'category',
          'params' => 'category_id=$1',
          'redirect' => true,
        ),
      );
    }

    function rewrite($parsed_link, $language_code) {

      if (!isset($parsed_link['query']['category_id'])) return;

      $category_trail = functions::catalog_category_trail($parsed_link['query']['category_id'], $language_code);

      if (empty($category_trail)) return;

      $parsed_link['path'] = '';
      foreach ($category_trail as $category_id => $category_name) $parsed_link['path'] .= functions::general_path_friendly($category_name, $language_code) .'-c-'. $category_id .'/';

      unset($parsed_link['query']['category_id']);

      return $parsed_link;
    }
  }
