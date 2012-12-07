<ul class="navigation-vertical columns-two">
<?php  
  function footer_categories_category_tree($parent_id=0, $depth=0) {
    global $system;
    
    $output = '';
    
    $categories_query = $system->database->query(
      "select c.id, c.image, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
      where status
      and parent_id = '". (int)$parent_id ."'
      order by c.priority asc, ci.name asc;"
    );
    
    if ($depth > 0 && $system->database->num_rows($categories_query) > 0) $output .= str_repeat('  ', $depth) .'<ul>' . PHP_EOL;
    while ($category = $system->database->fetch($categories_query)) {
    
      $output .= str_repeat('  ', $depth) .'  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id'])) .'">'. $category['name'] .'</a></li>' . PHP_EOL;
      
      /*
      $subcategories_query = $system->database->query(
        "select id
        from ". DB_TABLE_CATEGORIES ." c
        where status
        and parent_id = '". (int)$category['id'] ."'
        limit 1;"
      );
      
      if ($system->database->num_rows($subcategories_query) > 0) {
        $output .= footer_categories_category_tree($category['id'], $depth+1);
      }
      */
    }
    if ($depth > 0 && $system->database->num_rows($categories_query) > 0) $output .= str_repeat('  ', $depth) .'</ul>' . PHP_EOL;
    
    $system->database->free($categories_query);
    
    return $output;
  }
  
  echo footer_categories_category_tree();
?>
</ul>