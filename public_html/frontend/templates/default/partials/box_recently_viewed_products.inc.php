<section id="box-recently-viewed-products" class="hidden-xs">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="products">
    <?php foreach ($products as $product) { ?>
    <div class="product">
      <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
        <img class="img-thumbnail hover-light" src="<?php echo document::link($product['image']['thumbnail_1x']); ?>" srcset="<?php echo document::link($product['image']['thumbnail_1x']); ?> 1x, <?php echo document::link($product['image']['thumbnail_2x']); ?> 2x" alt="" />
      </a>
    </div>
    <?php } ?>
  </div>

</section>