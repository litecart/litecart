<?php  
  $box_site_footer_cache_id = cache::cache_id('box_site_footer', array('language', 'login', 'region'));
  if (cache::capture($box_site_footer_cache_id, 'file')) {
    
    $box_site_footer = new view();
    
    $box_site_footer->snippets = array(
      'categories' => array(),
      'manufacturers' => array(),
      'pages' => array(),
    );
    
  // Categories
    $categories_query = database::query(
      "select c.id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.status
      and c.parent_id = 0
      order by c.priority asc, ci.name asc;"
    );
    
    $i = 0;
    while ($category = database::fetch($categories_query)) {
      if (++$i < 10) {
        $box_site_footer->snippets['categories'][] = array(
          'id' => $category['id'],
          'name' => $category['name'],
          'link' => document::href_ilink('category', array('category_id' => $category['id'])),
        );
      } else {
        $box_site_footer->snippets['categories'][] = array(
          'id' => 0,
          'name' => language::translate('title_more', 'More') . '…',
          'link' => document::href_ilink('categories'),
        );
        break;
      }
    }
    
  // Manufacturers
    $manufacturers_query = database::query(
      "select m.id, m.name
      from ". DB_TABLE_MANUFACTURERS ." m
      where status
      order by m.name asc;"
    );
    
    $i = 0;
    while ($manufacturer = database::fetch($manufacturers_query)) {
      if (++$i < 10) {
        $box_site_footer->snippets['manufacturers'][] = array(
          'id' => $manufacturer['id'],
          'name' => $manufacturer['name'],
          'link' => document::href_ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
        );
      } else {
        $box_site_footer->snippets['manufacturers'][] = array(
          'id' => 0,
          'name' => language::translate('title_more', 'More') . '…',
          'link' => document::href_ilink('manufacturers'),
        );
        break;
      }
    }
    
    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    );
    while ($page = database::fetch($pages_query)) {
      $box_site_footer->snippets['pages'][] = array(
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::href_ilink('information', array('page_id' => $page['id'])),
      );
    }
    
    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and find_in_set('customer_service', dock)
      order by p.priority, pi.title;"
    );
    while ($page = database::fetch($pages_query)) {
      $box_site_footer->snippets['customer_service_pages'][] = array(
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::href_ilink('customer_service', array('page_id' => $page['id'])),
      );
    }
    
    echo $box_site_footer->stitch('views/box_site_footer');
    
    cache::end_capture($box_site_footer_cache_id);
  }
?>