<?php
  $categories_query = functions::catalog_categories_query();
  if (database::num_rows($categories_query) > 0) {
?>
<div class="box" id="box-categories">
  <div class="heading"><h3><?php echo language::translate('title_categories', 'Categories'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper categories">
<?php
    while ($category = database::fetch($categories_query)) {
      echo functions::draw_listing_category($category);
    }
?>
    </ul>
  </div>
</div>
<?php
  }
?>