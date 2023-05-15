<section id="box-recently-viewed-products" class="hidden-xs">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="listing">

    <?php foreach ($products as $product) { ?>
    <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
      <img class="thumbnail hover-light <?php echo $product['image']['viewport']['clipping']; ?>" style="aspect-ratio: <?php echo str_replace(':', '/', $product['image']['viewport']['ratio']); ?>" src="<?php echo document::href_rlink($product['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink($product['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink($product['image']['thumbnail_2x']); ?> 2x" alt="" />
    </a>
    <?php } ?>

  </div>
</section>