<div id="top-menu">
  <ul>
<?php  
  function top_menu_category_tree($parent_id=0, $depth=0) {
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
    
      $subcategories_query = $system->database->query(
        "select id
        from ". DB_TABLE_CATEGORIES ." c
        where status
        and parent_id = '". (int)$category['id'] ."'
        limit 1;"
      );
      if ($parent_id == 0) {
        $output .= str_repeat('  ', $depth) .'  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id'])) .'">'. $category['name'] .'</a>' . PHP_EOL;
      } else {
        $output .= str_repeat('  ', $depth) .'  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id'])) .'"><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 24, 24, 'CROP') .'" width="24" height="24" style="vertical-align: middle;" style="margin-right: 5px;" />'. $category['name'] .'</a>' . PHP_EOL;
      }
      
      if ($system->database->num_rows($subcategories_query) > 0) {
        $output .= top_menu_category_tree($category['id'], $depth+1);
      }
      
      $output .= '</li>' . PHP_EOL;
    }
    if ($depth > 0 && $system->database->num_rows($categories_query) > 0) $output .= str_repeat('  ', $depth) .'</ul>' . PHP_EOL;
    
    $system->database->free($categories_query);
    
    return $output;
  }
  
  echo top_menu_category_tree();
?>
  </ul>
</div>