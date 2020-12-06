<?php

  $box_information_links_cache_token = cache::token('box_information_links', array('language', isset($_GET['page_id']) ? $_GET['page_id'] : ''), 'file');
  if (cache::capture($box_information_links_cache_token)) {

    if (!empty($_GET['page_id'])) {
      $current_page_path = array_keys(reference::page($_GET['page_id'])->path);
    } else {
      $current_page_path = array();
    }

    $box_information_links = new ent_view();

    $box_information_links->snippets['pages'] = array();

    $box_information_links->snippets = array(
      'title' =>  language::translate('title_information', 'Information'),
      'pages' => array(),
      'page_path' => $current_page_path,
    );

    $iterator = function($parent_id, $level) use (&$iterator, &$current_page_path) {

      $output = array();

      $pages_query = database::query(
        "select p.id, p.parent_id, pi.title, p.priority, p.date_updated from ". DB_TABLE_PAGES ." p
        left join ". DB_TABLE_PAGES_INFO ." pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where p.status
        ". (!empty($parent_id) ? "and p.parent_id = ". (int)$parent_id ."" : "and find_in_set('information', p.dock)") ."
        order by p.priority asc, pi.title asc;"
      );

      while ($page = database::fetch($pages_query)) {
        $output[$page['id']] = array(
          'id' => $page['id'],
          'parent_id' => $page['parent_id'],
          'title' => $page['title'],
          'link' => document::ilink('information', array('page_id' => $page['id']), false),
          'active' => (!empty($_GET['page_id']) && $page['id'] == $_GET['page_id']) ? true : false,
          'opened' => (!empty($current_page_path) && in_array($page['id'], $current_page_path)) ? true : false,
          'subpages' => array(),
        );

        if (in_array($page['id'], $current_page_path)) {
          $sub_pages_query = database::query(
            "select id from ". DB_TABLE_PAGES ."
            where parent_id = ". (int)$page['id'] .";"
          );
          if (database::num_rows($sub_pages_query) > 0) {
            $output[$page['id']]['subpages'] = $iterator($page['id'], $level+1);
          }
        }
      }

      return $output;
    };

    if ($box_information_links->snippets['pages'] = $iterator(0, 0)) {
      echo $box_information_links->stitch('views/box_information_links');
    }

    cache::end_capture($box_information_links_cache_token);
  }
