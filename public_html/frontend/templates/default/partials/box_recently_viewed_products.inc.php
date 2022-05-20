<section id="box-recently-viewed-products" class="hidden-xs">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="products">
    <?php foreach ($products as $product) { ?>
    <div class="product">
      <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
        <img class="img-thumbnail <?php echo $product['image']['viewport']['clipping']; ?> hover-light" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail_2x']); ?> 2x" alt="" style="aspect-ratio: <?php echo $product['image']['viewport']['ratio']; ?>;" />
      </a>
    </div>
    <?php } ?>
  </div>

</section>