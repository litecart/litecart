<?php

  class url_customer_service {

    function routes() {
      return array(
        array(
          'pattern' => '#^.*-s-([0-9]+)/?$#',
          'page' => 'customer_service',
          'params' => 'page_id=$1',
          'redirect' => true,
        ),
      );
    }

    function rewrite($parsed_link, $language_code) {

      if (!empty($parsed_link['query']['page_id'])) {
        $page_query = database::query(
          "select page_id, title from ". DB_TABLE_PAGES_INFO ."
          where page_id = '". (int)$parsed_link['query']['page_id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
        $page = database::fetch($page_query);

        if (!empty($page)) {
          $parsed_link['path'] = functions::general_path_friendly($page['title'], $language_code) .'-s-'. $page['page_id'];
        } else {
          $parsed_link['path'] = 'untitled-s-'. $page['page_id'];
        }

      } else {

        $title = language::translate('title_customer_service', 'Customer Service', $language_code);
        $parsed_link['path'] = functions::general_path_friendly($title, $language_code) .'-s-0';
      }

      if (isset($parsed_link['query']['page_id'])) unset($parsed_link['query']['page_id']);

      return $parsed_link;
    }
  }

?>