<?php
  $box_information_links_cache_id = cache::cache_id('box_information_links', array('language', isset($_GET['page_id']) ? $_GET['page_id'] : ''));
  if (cache::capture($box_information_links_cache_id, 'file')) {

    $box_information_links = new view();

    $box_information_links->snippets['pages'] = array();

    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    );

    if (database::num_rows($pages_query)) {
      while ($information_link = database::fetch($pages_query)) {
        $box_information_links->snippets['pages'][] = array(
          'id' => $information_link['id'],
          'title' => $information_link['title'],
          'link' => document::ilink('information', array('page_id' => $information_link['id'])),
          'active' => (isset($_GET['page_id']) && $_GET['page_id'] == $information_link['id']) ? true : false,
        );
      }

      echo $box_information_links->stitch('views/box_information_links');
    }

    cache::end_capture($box_information_links_cache_id);
  }
