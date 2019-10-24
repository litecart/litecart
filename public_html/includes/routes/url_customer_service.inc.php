<?php

  class url_customer_service {

    function routes() {
      return array(
        array(
          'pattern' => '#^.*-s-([0-9]+)/?$#',
          'page' => 'customer_service',
          'params' => 'page_id=$1',
          'options' => array(
            'redirect' => true,
          ),
        ),
      );
    }

    function rewrite(ent_link $link, $language_code) {

      if (!empty($link->query['page_id'])) {

        $page = reference::page($link->query['page_id'], $language_code);
        if (empty($page->id)) return $link;

        if (!empty($page->title)) {
          $link->path = functions::general_path_friendly($page->title, $language_code) .'-s-'. $page->id;
        } else {
          $link->path = 'untitled-s-'. $page->id;
        }

      } else {

        $title = language::translate('title_customer_service', 'Customer Service', $language_code);
        $link->path = functions::general_path_friendly($title, $language_code) .'-s-0';
      }

      if (isset($link->query['page_id'])) $link->unset_query('page_id');

      return $link;
    }
  }
