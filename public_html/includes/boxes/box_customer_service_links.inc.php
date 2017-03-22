<?php
  $box_customer_service_links_cache_id = cache::cache_id('box_customer_service_links', array('language', isset($_GET['page_id']) ? $_GET['page_id'] : ''));
  if (cache::capture($box_customer_service_links_cache_id, 'file')) {

    $box_customer_service_links = new view();

    $box_customer_service_links->snippets['pages'] = array(
      array(
        'id' => 0,
        'title' => language::translate('title_contact_us', 'Contact Us'),
        'link' => document::href_ilink('customer_service'),
        'active' => empty($_GET['page_id']) ? true : false,
      ),
    );

    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and find_in_set('customer_service', dock)
      order by p.priority, pi.title;"
    );

    if (database::num_rows($pages_query)) {
      while ($page = database::fetch($pages_query)) {
        $box_customer_service_links->snippets['pages'][] = array(
          'id' => $page['id'],
          'title' => $page['title'],
          'link' => document::ilink('customer_service', array('page_id' => $page['id'])),
          'active' => (isset($_GET['page_id']) && $_GET['page_id'] == $page['id']) ? true : false,
        );
      }

      echo $box_customer_service_links->stitch('views/box_customer_service_links');
    }

    cache::end_capture($box_customer_service_links_cache_id);
  }
