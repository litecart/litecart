<section id="box-recently-viewed-products" class="hidden-xs">
  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>
  </div>

  <div class="card-body">
    <div class="listing">
      <?php foreach ($products as $product) { ?>
      <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
        <img class="img-thumbnail" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail_1x']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail_1x']); ?> 1x, <?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail_2x']); ?> 2x" alt="">
      </a>
      <?php } ?>
    </div>
  </div>
</section>