<?php

  $titles = [];
  foreach (language::$languages as $language) {
    $titles[] = preg_quote(functions::format_path_friendly(language::translate('title_customer_service', 'Customer Service', $language['code'])), '#');
  }

  return [
    'customer_service' => [
      'pattern' => '#^('. implode('|', array_filter($titles)) .'|.*-s-([0-9]+)/?)$#',
      'controller' => 'customer_service',
      'params' => 'page_id=$1',
      'endpoint' => 'frontend',
      'options' => [
        'redirect' => true,
      ],
      'rewrite' => function(ent_link $link, $language_code) {

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
    ],
  ];
