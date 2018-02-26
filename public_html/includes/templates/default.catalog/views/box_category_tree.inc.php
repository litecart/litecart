<?php if (!empty(document::$settings['compact_category_tree'])) { ?>
<style>
  #box-category-tree > ul.compact > li:not(.opened) {
    display: none;
  }
  #box-category-tree > ul.compact > li.toggle {
    display: block !important;
  }
</style>
<?php } ?>

<?php
  if (!function_exists('custom_draw_category')) {
    function custom_draw_category($category, $category_path) {
      echo '<li class="category-'. $category['id'] . (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'">' . PHP_EOL
         . '  <a href="'. htmlspecialchars($category['link']) .'"><i class="fa fa-fw fa-'. (empty($category['opened']) ? 'caret-right' : 'caret-down') .'"></i> '. $category['name'] .'</a>' . PHP_EOL;
      if (!empty($category['subcategories'])) {
        echo '  <ul class="nav nav-stacked nav-pills">' . PHP_EOL;
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
  <h2 class="title"><?php echo $title; ?></h2>
  <ul class="nav nav-stacked nav-pills<?php if (!empty(document::$settings['compact_category_tree']) && !empty($category_path)) echo ' compact'; ?>">
    <?php foreach ($categories as $category) custom_draw_category($category, $category_path); ?>
  </ul>
</div>

<?php if (!empty(document::$settings['compact_category_tree'])) { ?>
<script>
  $('#box-category-tree > ul.compact').prepend(
    '<li class="toggle"><a href="#" data-toggle="showall"><i class="fa fa-caret-left"></i> <?php echo language::translate('title_show_all', 'Show All'); ?></a></li>'
  );

  $('#box-category-tree > ul.compact').on('click', 'a[data-toggle="showall"]', function(e){
    e.preventDefault();
    $(this).slideUp();
    $('#box-category-tree > ul > li:hidden').slideDown();
  });
</script>
<?php } ?>
