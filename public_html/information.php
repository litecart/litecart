<?php
  require_once('includes/config.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
  
  ob_start();
?>
<aside class="shadow rounded-corners">
  <div class="box" id="box-information">
    <div class="heading"><h3><?php echo language::translate('title_information', 'Information'); ?></h3></div>
    <div class="content">
      <nav>
        <ul class="list-vertical">
        <?php
          $pages_query = database::query(
            "select p.id, pi.title from ". DB_TABLE_PAGES ." p
            left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
            where status
            and find_in_set('information', dock)
            order by p.priority, pi.title;"
          );
          while ($page = database::fetch($pages_query)) {
            echo '<li'. ((isset($_GET['page_id']) && $_GET['page_id'] == $page['id']) ? ' class="active"' : '') .'><a href="'. document::href_link('', array('page_id' => $page['id'])) .'">'. $page['title'] .'</a></li>' . PHP_EOL;
          }
        ?>
        </ul>
      </nav>
    </div>
  </div>
</aside>
<?php
  document::$snippets['column_left'] = ob_get_clean();
  
  $pages_query = database::query(
    "select p.id, p.status, pi.title, pi.content, pi.head_title, pi.meta_keywords, pi.meta_description from ". DB_TABLE_PAGES ." p
    left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
    where p.id = '". (int)$_GET['page_id'] ."'
    limit 1;"
  );
  $page = database::fetch($pages_query);
  
  if (empty($page['status'])) {
    notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
    header('HTTP/1.1 404 Not Found');
    header('Location: '. document::link(WS_DIR_HTTP_HOME));
    exit;
  }
  
  document::$snippets['title'][] = !empty($page['head_title']) ? $page['head_title'] : $page['title'];
  document::$snippets['keywords'] = !empty($page['meta_keywords']) ? $page['meta_keywords'] : '';
  document::$snippets['description'] = !empty($page['meta_description']) ? $page['meta_description'] : '';
  
  breadcrumbs::add($page['title'], document::link('', array(), true));
?>
<?php echo $page['content']; ?>

<?php  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>