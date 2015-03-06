<div class="box" id="box-category-tree">
  <div class="heading"><h3><?php echo language::translate('title_categories', 'Categories'); ?></h3></div>
  <nav class="content">
    <?php
      if (!function_exists('custom_draw_category_tree')) {
        function custom_draw_category_tree($categories, $indent=0) {
          echo '<ul class="list-vertical">' . PHP_EOL;
          foreach ($categories as $category) {
            echo '  <li class="category-'. $category['id'] . (!empty($category['active']) ? ' active' : '') .'" style="margin-left: '. ($indent*5) .'px;" >'. functions::draw_fontawesome_icon(!empty($category['opened']) ? 'minus-square' : 'plus-square') .' <a href="'. htmlspecialchars($category['link']) .'">'. $category['name'] .'</a>';
            if (!empty($category['subcategories'])) {
              echo PHP_EOL . custom_draw_category_tree($category['subcategories'], $indent+1);
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