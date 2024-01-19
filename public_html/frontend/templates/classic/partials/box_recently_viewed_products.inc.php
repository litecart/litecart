<section id="box-recently-viewed-products" class="hidden-xs">
  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>
  </div>

  <div class="card-body">
    <div class="listing">
      <?php foreach ($products as $product) { ?>
      <a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
        <?php echo functions::draw_thumbnail($product['image'], 64, 0, 'product', 'alt=""'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
</section>