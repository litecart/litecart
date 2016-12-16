<div id="box-recently-viewed-products" class="box">

  <h3 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h3>

  <div class="products row half-gutter">

    <?php foreach ($products as $product) { ?>
    <div class="product col-xs-thirds">
      <a href="<?php echo htmlspecialchars($product['link']); ?>" class="thumbnail" data-toggle="lightbox" data-gallery="recently-viewed">
        <img src="<?php echo htmlspecialchars($product['thumbnail']); ?>" alt="" />
        </a>
      </div>
    <?php } ?>

  </div>
</div>