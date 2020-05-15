<?php
  $draw_branch = function($category, &$category_path, &$draw_branch) {
    echo '<li class="category-'. $category['id'] . (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'">' . PHP_EOL
       . '  <a href="'. htmlspecialchars($category['link']) .'">'. functions::draw_fonticon('fa-caret-'. (!empty($category['opened']) ? 'down' : 'right') .' fa-fw') .' '. $category['name'] . (settings::get('category_tree_product_count') ? ' <small class="pull-right">('. reference::category($category['id'])->num_products .')</small>' : '') .'</a>' . PHP_EOL;
    if (!empty($category['subcategories'])) {
      echo '  <ul class="nav nav-stacked nav-pills">' . PHP_EOL;
      foreach ($category['subcategories'] as $subcategory) {
        echo PHP_EOL . $draw_branch($subcategory, $category_path, $draw_branch);
      }
      echo '  </ul>' . PHP_EOL;
    }
    echo '</li>' . PHP_EOL;
  };
?>

<section id="box-category-tree" class="box hidden-xs">
  <h2 class="title"><?php echo $title; ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <li><a href="<?php echo document::href_ilink('index'); ?>"><?php echo functions::draw_fonticon('fa-caret-left fa-fw'); ?> <?php echo language::translate('title_back', 'Back'); ?></a></li>
    <?php foreach ($categories as $category) $draw_branch($category, $category_path, $draw_branch); ?>
  </ul>
</section>
