<?php  
  $box_category_tree_cache_id = cache::cache_id('box_category_tree', array('language', isset($_GET['category_id']) ? $_GET['category_id'] : null));
  if (cache::capture($box_category_tree_cache_id, 'file')) {
  
    $box_category_tree = new view();
    
    $box_category_tree->snippets = array(
      'categories' => array(),
    );
    
    $category_trail = array_keys(functions::catalog_category_trail(empty($_GET['category_id']) ? 0 : $_GET['category_id']));
    
    if (!function_exists('output_category_tree')) {
      function output_category_tree($category_id, $level, $category_trail, &$output) {
        
        $categories_query = functions::catalog_categories_query($category_id, ($level == 0) ? 'tree' : null);
        
        while ($category = database::fetch($categories_query)) {
          
          $output[$category['id']] = array(
            'id' => $category['id'],
            'name' => $category['name'],
            'link' => document::ilink('category', array('category_id' => $category['id']), false),
            'active' => (!empty($_GET['category_id']) && $category['id'] == $_GET['category_id']) ? true : false,
            'opened' => (!empty($category_trail) && in_array($category['id'], $category_trail)) ? true : false,
            'subcategories' => array(),
          );
          
          if (in_array($category['id'], $category_trail)) {
            $sub_categories_query = functions::catalog_categories_query($category['id']);
            if (database::num_rows($sub_categories_query) > 0) {
              output_category_tree($category['id'], $level+1, $category_trail, $output[$category['id']]['subcategories']);
            }
          }
          
        }
      
      database::free($categories_query);
      
      return $output;
      }
    }
    
    $box_category_tree->snippets['categories'] = output_category_tree(0, 0, $category_trail, $box_category_tree->snippets['categories']);

    echo $box_category_tree->stitch('views/box_category_tree');
    
    cache::end_capture($box_category_tree_cache_id);
  }
?>