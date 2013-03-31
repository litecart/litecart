<?php
  $categories_query = $system->functions->catalog_categories_query();
  if ($system->database->num_rows($categories_query) > 0) {
?>
<div class="box" id="box-categories">
  <div class="heading"><h3><?php echo $system->language->translate('title_categories', 'Categories'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper categories">
<?php
    while ($category = $system->database->fetch($categories_query)) {
      echo $system->functions->draw_listing_category($category);
    }
?>
    </ul>
  </div>
</div>
<?php
  }
?>