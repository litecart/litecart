<div class="box" id="box-category-tree">
  <div class="heading"><h3><?php echo $system->language->translate('title_categories', 'Categories'); ?></h3></div>
  <nav class="content">
<?php
  function custom_catalog_trail($category_id) {
    global $system;
    
    if ($category_id == 0) return array(0);
    
    $categories_query = $system->database->query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
      where c.id = '". (int)$category_id ."'
      order by c.priority asc, ci.name asc
      limit 1;"
    );
    $category = $system->database->fetch($categories_query);
    
    if ($category['parent_id'] != 0) {
      $tree = array_merge(custom_catalog_trail($category['parent_id']), array($category['id']));
    } else {
      $tree = array($category['id']);
    }
    
    return $tree;
  }
  
  function output_category_tree($category_id, $level, $category_trail) {
    global $system;
    
    $output = '<ul class="navigation-vertical">' . PHP_EOL;
    
    $categories_query = $system->database->query(
      "select c.id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". $system->language->selected['code'] ."')
      where status
      and parent_id = '". (int)$category_id ."'
      order by c.priority asc, ci.name asc;"
    );
    
    while ($category = $system->database->fetch($categories_query)) {
    
      $output .= '<li'. ((!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? ' class="active"' : '') .'><img src="'. WS_DIR_IMAGES .'icons/16x16/'. ((@in_array($category['id'], $category_trail)) ? 'collapse.png' : 'expand.png') .'" width="16" height="16" align="absmiddle" /> <a href="'. $system->document->link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id']), false) .'">'. $category['name'] .'</a></li>';
      
      if (in_array($category['id'], $category_trail)) {
        $sub_categories_query = $system->database->query(
          "select id
          from ". DB_TABLE_CATEGORIES ." c
          where status
          and parent_id = '". (int)$category['id'] ."'
          limit 1;"
        );
        if ($system->database->num_rows($sub_categories_query) > 0) {
          $output .= output_category_tree($category['id'], $level+1, $category_trail);
        }
      }
    }
    
    $system->database->free($categories_query);
    
    $output .= '</ul>' . PHP_EOL;
    
    return $output;
  }
  
  echo output_category_tree(0, 0, custom_catalog_trail(empty($_GET['category_id']) ? 0 : $_GET['category_id']));
?>
  </nav>
</div>