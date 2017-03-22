<?php

  class url_information {

    function routes() {
      return array(
        array(
          'pattern' => '#^.*-i-([0-9]+)/?$#',
          'page' => 'information',
          'params' => 'page_id=$1',
          'redirect' => true,
        ),
      );
    }

    function rewrite($parsed_link, $language_code) {

      if (empty($parsed_link['query']['page_id'])) return false;

      $page_query = database::query(
        "select page_id, title from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$parsed_link['query']['page_id'] ."'
        and language_code = '". database::input($language_code) ."'
        limit 1;"
      );
      $page = database::fetch($page_query);

      if (empty($page)) return false;

      $parsed_link['path'] = functions::general_path_friendly($page['title'], $language_code) .'-i-'. $page['page_id'];

      unset($parsed_link['query']['page_id']);

      return $parsed_link;
    }
  }
