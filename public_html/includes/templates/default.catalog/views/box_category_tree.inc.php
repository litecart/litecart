<?php
  if (!function_exists('custom_draw_category')) {
    function custom_draw_category($category, $category_path) {
      if (empty($category_path) || in_array($category['id'], $category_path) || in_array($category['parent_id'], $category_path)) {
        $visible = true;
      }
      echo '<li class="category-'. $category['id'] . (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'"'. (empty($visible) ? ' style="display: none;"' : '') .'>' . PHP_EOL
         . '  <a href="'. htmlspecialchars($category['link']) .'"><i class="fa fa-fw fa-'. (empty($category['opened']) ? 'caret-right' : 'caret-down') .'"></i> '. $category['name'] .'</a>' . PHP_EOL;
      if (!empty($category['subcategories'])) {
        echo '  <ul class="nav nav-pills nav-stacked">' . PHP_EOL;
        foreach ($category['subcategories'] as $subcategory) {
          echo PHP_EOL . custom_draw_category($subcategory, $category_path);
        }
        echo '  </ul>' . PHP_EOL;
      }
      echo '</li>' . PHP_EOL;
    }
  }
?>
<div id="box-category-tree" class="box">
  <h1 class="title"><?php echo $title; ?></h1>
  <ul class="nav nav-primary nav-pills nav-stacked">
    <?php if ($category_path) { ?><li><a href="#" data-toggle="showall"><i class="fa fa-caret-left"></i> <?php echo language::translate('title_view_all', 'View All'); ?></a></li><?php } ?>
    <?php foreach ($categories as $category) custom_draw_category($category, $category_path); ?>
  </ul>
</div>

<script>
  $('a[data-toggle="showall"]').click(function(e){
    e.preventDefault();
    $(this).slideUp();
    $('#box-category-tree li:hidden').slideDown();
  });
</script>