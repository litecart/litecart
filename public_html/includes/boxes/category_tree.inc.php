<?php  
  $box_category_tree_cache_id = cache::cache_id('box_category_tree', array('language'));
  if (cache::capture($box_category_tree_cache_id, 'file')) {
?>
<div class="box" id="box-category-tree">
  <div class="heading"><h3><?php echo language::translate('title_categories', 'Categories'); ?></h3></div>
  <nav class="content">
<?php
  function custom_catalog_trail($category_id) {
    
    if ($category_id == 0) return array(0);
    
    $categories_query = database::query(
      "select c.id, c.parent_id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
      where c.id = '". (int)$category_id ."'
      order by c.priority asc, ci.name asc
      limit 1;"
    );
    $category = database::fetch($categories_query);
    
    if ($category['parent_id'] != 0) {
      $tree = array_merge(custom_catalog_trail($category['parent_id']), array($category['id']));
    } else {
      $tree = array($category['id']);
    }
    
    return $tree;
  }
  
  function output_category_tree($category_id, $level, $category_trail) {
    
    $output = '<ul class="list-vertical">' . PHP_EOL;
    
    $categories_query = database::query(
      "select c.id, ci.name
      from ". DB_TABLE_CATEGORIES ." c
      left join ". DB_TABLE_CATEGORIES_INFO ." ci on (ci.category_id = c.id and ci.language_code = '". language::$selected['code'] ."')
      where status
      and parent_id = '". (int)$category_id ."'
      order by c.priority asc, ci.name asc;"
    );
    
    while ($category = database::fetch($categories_query)) {
    
      $output .= '  <li>' . PHP_EOL
               . '    <a href="'. document::href_link(WS_DIR_HTTP_HOME . 'category.php', array('category_id' => $category['id']), false) .'" '. ((!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? ' class="active"' : '') .'><img src="'. WS_DIR_IMAGES .'icons/16x16/'. ((@in_array($category['id'], $category_trail)) ? 'collapse.png' : 'expand.png') .'" width="16" height="16" alt="" style="vertical-align: middle;" /> '. $category['name'] .'</a>' . PHP_EOL;
      
      if (in_array($category['id'], $category_trail)) {
        $sub_categories_query = database::query(
          "select id
          from ". DB_TABLE_CATEGORIES ." c
          where status
          and parent_id = '". (int)$category['id'] ."'
          limit 1;"
        );
        if (database::num_rows($sub_categories_query) > 0) {
          $output .= output_category_tree($category['id'], $level+1, $category_trail);
        }
      }
      
      $output .= '  </li>' . PHP_EOL;
    }
    
    database::free($categories_query);
    
    $output .= '</ul>' . PHP_EOL;
    
    return $output;
  }
  
  echo output_category_tree(0, 0, custom_catalog_trail(empty($_GET['category_id']) ? 0 : $_GET['category_id']));
?>
  </nav>
</div>
<?php
    cache::end_capture($box_category_tree_cache_id);
  }
?>