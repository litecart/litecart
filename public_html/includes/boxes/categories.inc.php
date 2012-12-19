<?php
  $categories_query = $system->functions->catalog_categories_query();
  if ($system->database->num_rows($categories_query) > 0) {
?>
<div class="box" id="box-categories">
  <div class="heading"><h3><?php echo $system->language->translate('title_categories', 'Categories'); ?></h3></div>
  <div class="content listing-wrapper">
<?php
    while ($category = $system->database->fetch($categories_query)) {
?>
    <div class="category">
      <a class="link" href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME .'category.php', array('category_id' => $category['id'])); ?>">
        <div class="image" style="position: relative;">
          <img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 320, 180, 'CROP'); ?>" width="320" height="180" alt="'. $category['name'] .'" title="'. $category['name'] .'" style="padding-right: 10px;" />
          <div class="footer" style="position: absolute; bottom: 0;">
            <div class="title"><?php echo $category['name']; ?></div>
            <div class="description"><?php echo $category['short_description']; ?></div>
          </div>
        </div>
      </a>
    </div>
<?php
    }
?>
  </div>
</div>
<?php
  }
?>