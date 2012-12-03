<?php
  $categories_query = $system->functions->catalog_categories_query();
  if ($system->database->num_rows($categories_query) > 0) {
?>
<div class="box" id="box-similar-products">
  <div class="heading"><h3><?php echo $system->language->translate('title_categories', 'Categories'); ?></h3></div>
  <div class="content listing-wrapper">
<?php
    while ($subcategory = $system->database->fetch($categories_query)) {
?>
    <div class="subcategory">
      <a class="link" href="<?php echo $system->document->link(WS_DIR_HTTP_HOME .'category.php', array('category_id' => $subcategory['id'])); ?>">
        <div class="image" style="position: relative;">
          <img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $subcategory['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 320, 180, 'CROP'); ?>" width="320" height="180" border="0" style="padding-right: 10px;" />
          <div class="footer" style="position: absolute; bottom: 0;">
            <div class="title"><?php echo $subcategory['name']; ?></div>
            <div class="description"><?php echo $subcategory['short_description']; ?></div>
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