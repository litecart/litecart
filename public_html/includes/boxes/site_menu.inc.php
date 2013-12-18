<nav id="site-menu" class="nine-eighty">
  <ul>
    <li class="rounded-corners-left"><a href="<?php echo document::link(WS_DIR_HTTP_HOME); ?>"><img src="{snippet:template_path}images/home.png" width="12" height="12" alt="<?php echo htmlspecialchars(language::translate('title_home', 'Home')); ?>" /></a></li>
<?php  
  function site_menu_category_tree($parent_id=0, $depth=0) {
    
    $output = '';
    
    $categories_query = database::query(
      "select c.id, c.image, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
      where status
      ". (($depth == 0) ? "and find_in_set('menu', c.dock)" : "and parent_id = '". (int)$parent_id ."'") ."
      order by c.priority asc, ci.name asc;"
    );
    
    if ($depth > 0 && database::num_rows($categories_query) > 0) $output .= str_repeat('  ', $depth) .'<ul>' . PHP_EOL;
    
    while ($category = database::fetch($categories_query)) {
    
      if ($parent_id == 0) {
        $output .= str_repeat('  ', $depth) .'  <li'. ((isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? ' class="active"' : '') .'><a href="'. document::href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id'])) .'">'. $category['name'] .'</a>' . PHP_EOL;
      } else {
        $output .= str_repeat('  ', $depth) .'  <li'. ((isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? ' class="active"' : '') .'><a href="'. document::href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id'])) .'"><img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 24, 24, 'CROP') .'" width="24" height="24" style="vertical-align: middle; margin-right: 10px;" alt="" />'. $category['name'] .'</a>' . PHP_EOL;
      }
      
      if ($depth < 1) { // Max depth
        $subcategories_query = database::query(
          "select id
          from ". DB_TABLE_CATEGORIES ." c
          where status
          and parent_id = '". (int)$category['id'] ."'
          limit 1;"
        );
        
        if (database::num_rows($subcategories_query) > 0) {
          $output .= site_menu_category_tree($category['id'], $depth+1);
        }
      }
      
      $output .= '</li>' . PHP_EOL;
    }
    
    if ($depth > 0 && database::num_rows($categories_query) > 0) $output .= str_repeat('  ', $depth) .'</ul>' . PHP_EOL;
    
    database::free($categories_query);
    
    return $output;
  }
  
  echo site_menu_category_tree();
?>
  </ul>
</nav>