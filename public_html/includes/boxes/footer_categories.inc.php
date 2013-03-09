<ul class="navigation-vertical">
<?php  
  $categories_query = $system->database->query(
    "select c.id, c.image, ci.name
    from ". DB_TABLE_CATEGORIES ." c
    left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
    where status
    and parent_id = '0'
    order by c.priority asc, ci.name asc;"
  );
  
  $i = 0;
  while ($category = $system->database->fetch($categories_query)) {
    if (++$i == 10) {
      echo '  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'categories.php') .'">'. $system->language->translate('title_more', 'More') .'...</a></li>' . PHP_EOL;
      break;
    }
    echo '  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id'])) .'">'. $category['name'] .'</a></li>' . PHP_EOL;
  }
?>
</ul>