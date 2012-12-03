<?php
  require_once('includes/app_header.inc.php');
  
  $pages_query = $system->database->query(
    "select p.id, pi.title, pi.content, pi.head_title, pi.meta_keywords, pi.meta_description from ". DB_TABLE_PAGES ." p
    left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". $system->language->selected['code'] ."')
    where p.id = '". (int)$_GET['page_id'] ."'
    limit 1;"
  );
  $page = $system->database->fetch($pages_query);
  if (empty($page)) die('Error: Invalid page_id');
  
  $system->document->snippets['title'][] = !empty($page['head_title']) ? $page['head_title'] : $page['title'];
  $system->document->snippets['keywords'] = !empty($page['meta_keywords']) ? $page['meta_keywords'] : '';
  $system->document->snippets['description'] = !empty($page['meta_description']) ? $page['meta_description'] : '';
  
  $system->breadcrumbs->add($page['title'], $system->document->link('', array(), true));
?>
<h1 style="margin-top: 0px;"><?php echo $page['title']; ?></h1>
<?php echo $page['content']; ?>

<?php  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>