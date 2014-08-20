<nav>
  <h4><?php echo language::translate('title_categories', 'Categories'); ?></h4>
  <ul class="list-vertical">
<?php  
  $categories_query = database::query(
    "select c.id, c.image, ci.name
    from ". DB_TABLE_CATEGORIES ." c
    left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
    where status
    and parent_id = '0'
    order by c.priority asc, ci.name asc;"
  );
  
  $i = 0;
  while ($category = database::fetch($categories_query)) {
    if (++$i == 10) {
      echo '  <li><a href="'. document::href_ilink('categories') .'">'. language::translate('title_more', 'More') .'...</a></li>' . PHP_EOL;
      break;
    }
    echo '  <li><a href="'. document::href_ilink('category', array('category_id' => $category['id'])) .'">'. $category['name'] .'</a></li>' . PHP_EOL;
  }
?>
  </ul>
</nav>