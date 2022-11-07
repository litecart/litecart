<section id="box-recently-viewed-products" class="box hidden-xs">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="listing">

    <?php foreach ($products as $product) { ?>

      <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
        <img class="img-thumbnail hover-light" src="<?php echo document::rlink(FS_DIR_APP . $product['image']['thumbnail_1x']); ?>" srcset="<?php echo document::rlink(FS_DIR_APP . $product['image']['thumbnail_1x']); ?> 1x, <?php echo document::rlink(FS_DIR_APP . $product['image']['thumbnail_2x']); ?> 2x" alt="" />
      </a>

    <?php } ?>

  </div>
</section>