<div id="box-category-tree" class="box">
  <h3 class="title"><?php echo language::translate('title_categories', 'Categories'); ?></h3>
  <nav class="content">
    <?php
      if (!function_exists('custom_draw_category_tree')) {
        function custom_draw_category_tree($categories, $indent=0) {
          echo '<ul class="list-vertical">' . PHP_EOL;
          foreach ($categories as $category) {
            echo '  <li class="category-'. $category['id'] . (!empty($category['active']) ? ' active' : '') .'">'. functions::draw_fonticon(!empty($category['opened']) ? 'fa-minus-square' : 'fa-plus-square', 'style="font-size: 0.75em;"') .' <a href="'. htmlspecialchars($category['link']) .'">'. $category['name'] .'</a>';
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