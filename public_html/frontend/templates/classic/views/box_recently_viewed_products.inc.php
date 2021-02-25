<section id="box-recently-viewed-products" class="box hidden-xs">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="listing products">

    <?php foreach ($products as $product) { ?>
    <div class="product">
      <a class="link" href="<?php echo htmlspecialchars($product['link']); ?>" title="<?php echo htmlspecialchars($product['name']); ?>">
        <img class="img-thumbnail hover-light" src="<?php echo document::link(WS_DIR_STORAGE . $product['image']['thumbnail_1x']); ?>" srcset="<?php echo document::link(WS_DIR_STORAGE . $product['image']['thumbnail_1x']); ?> 1x, <?php echo document::link(WS_DIR_STORAGE . $product['image']['thumbnail_2x']); ?> 2x" alt="" />
      </a>
    </div>
    <?php } ?>

  </div>
</section>