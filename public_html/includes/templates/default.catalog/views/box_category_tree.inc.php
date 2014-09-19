<div class="box" id="box-category-tree">
  <div class="heading"><h3><?php echo language::translate('title_categories', 'Categories'); ?></h3></div>
  <nav class="content">
    <?php
      if (!function_exists('custom_draw_category_tree')) {
        function custom_draw_category_tree($categories, $indent=0) {
          echo '<ul class="list-vertical">' . PHP_EOL;
          foreach ($categories as $category) {
            echo '  <li class="category-'. $category['id'] . (!empty($category['active']) ? ' active' : '') .'" style="margin-left: '. (10*$indent) .'px;" ><img src="'. WS_DIR_IMAGES .'icons/16x16/'. (!empty($category['opened']) ? 'collapse.png' : 'expand.png') .'" width="16" height="16" alt="" style="vertical-align: middle; margin-right: 10px;" /> <a href="'. htmlspecialchars($category['link']) .'">'. $category['name'] .'</a>';
            if (!empty($category['subcategories'])) {
              echo custom_draw_category_tree($category['subcategories'], $indent+1);
            }
            echo '  </li>' . PHP_EOL;
          }
          echo '</ul>' . PHP_EOL;
        }
      }
      custom_draw_category_tree($categories);
    ?>
  </nav>
</div>