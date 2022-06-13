<?php

  class url_customer_service {

    function routes() {

      $titles = [];
      foreach (language::$languages as $language) {
        $titles[] = preg_quote(functions::format_path_friendly(language::translate('title_customer_service', 'Customer Service', $language['code'])), '#');
      }

      return [
        [
          'pattern' => '#^('. implode('|', array_filter($titles)) .')$#',
          'page' => 'customer_service',
          'params' => '',
          'endpoint' => 'frontend',
          'options' => [
            'redirect' => true,
          ],
        ],
        [
          'pattern' => '#^.*-s-([0-9]+)/?$#',
          'page' => 'customer_service',
          'params' => 'page_id=$1',
          'endpoint' => 'frontend',
          'options' => [
            'redirect' => true,
          ],
        ],
      ];
    }

    function rewrite(ent_link $link, $language_code) {

      if (!empty($link->query['page_id'])) {

        $page = reference::page($link->query['page_id'], $language_code);
        if (empty($page->id)) return $link;

        if (!empty($page->title)) {
          $link->path = functions::format_path_friendly($page->title, $language_code) .'-s-'. $page->id;
        } else {
          $link->path = 'untitled-s-'. $page->id;
        }

      } else {
        $link->path = functions::format_path_friendly(language::translate('title_customer_service', 'Customer Service', $language_code));
      }

      if (isset($link->query['page_id'])) $link->unset_query('page_id');

      return $link;
    }
  }
