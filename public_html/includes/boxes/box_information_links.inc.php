<?php
  $box_information_links_cache_id = cache::cache_id('box_information_links', array('language'));
  if (cache::capture($information_links_cache_id, 'file')) {
    
    $box_information_links = new view();
    
    $box_information_links->snippets['pages'] = array();
    
    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    );
    
    while ($page = database::fetch($pages_query)) {
      $box_information_links->snippets['pages'][] = array(
        'id' => $page['id'],
        'title' => $page['title'],
        'href' => document::href_ilink('information', array('page_id' => $page['id'])),
      );
    }
    
    echo $box_information_links->stitch('box_information_links');
    
    cache::end_capture($box_information_links_cache_id);
  }
?>