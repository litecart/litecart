<?php

  class url_information {

    function routes() {
      return [
        [
          'pattern' => '#^.*-i-([0-9]+)/?$#',
          'page' => 'information',
          'params' => 'page_id=$1',
          'options' => [
            'redirect' => true,
          ],
        ],
      ];
    }

    function rewrite(ent_link $link, $language_code) {

      if (empty($link->query['page_id'])) return false;

      $page = reference::page($link->query['page_id'], $language_code);
      if (empty($page->id)) return $link;

      if (empty($page)) return false;

      $link->path = functions::format_path_friendly($page->title, $language_code) .'-i-'. $page->id;
      $link->unset_query('page_id');

      return $link;
    }
  }
