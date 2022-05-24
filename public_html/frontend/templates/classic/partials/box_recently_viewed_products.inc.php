<section id="box-recently-viewed-products" class="box hidden-xs">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="listing">

    <?php foreach ($products as $product) { ?>
    <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
      <img class="thumbnail hover-light" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $product['image']['thumbnail']); ?>" srcset="<?php echo document::link(WS_DIR_STORAGE . $product['image']['thumbnail']); ?> 1x, <?php echo document::link(WS_DIR_STORAGE . $product['image']['thumbnail_2x']); ?> 2x" alt="" />
    </a>
    <?php } ?>

  </div>
</section>