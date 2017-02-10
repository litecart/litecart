<div id="box-category-tree" class="box">
  <h2 class="title"><?php echo language::translate('title_categories', 'Categories'); ?></h2>
  <ul class="nav nav-primary nav-pills nav-stacked">
<?php
  if (!function_exists('custom_draw_category_tree')) {
    function custom_draw_category_tree($categories, $indent=0) {
      foreach ($categories as $category) {
        echo '  <li class="category-'. $category['id'] . (!empty($category['active']) ? ' active' : '') .'">' . PHP_EOL
           . '    <a href="'. htmlspecialchars($category['link']) .'"><i class="fa fa-'. (empty($category['active']) ? 'plus' : 'minus') .'-square" style="font-size: 0.75em;"></i> '. $category['name'] .'</a>' . PHP_EOL;
        if (!empty($category['subcategories'])) {
          echo '<ul class="nav nav-pills nav-stacked">' . PHP_EOL;
          echo PHP_EOL . custom_draw_category_tree($category['subcategories'], $indent+1);
          echo '</ul>' . PHP_EOL;
        }
        echo '  </li>' . PHP_EOL;
      }

    }
  }
  custom_draw_category_tree($categories);
?>
  </ul>
</div>