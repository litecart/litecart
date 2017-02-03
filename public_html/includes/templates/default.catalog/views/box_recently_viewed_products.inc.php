<div id="box-recently-viewed-products" class="box">

  <h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

  <div class="products row half-gutter">

    <?php foreach ($products as $product) { ?>
    <div class="col-xs-thirds">
      <a href="<?php echo htmlspecialchars($product['link']); ?>"<?php echo !empty(document::$settings['product_modal_window']) ? ' data-toggle="lightbox"' : ''; ?>>
        <img class="img-thumbnail" src="<?php echo htmlspecialchars($product['thumbnail']); ?>" alt="" />
        </a>
      </div>
    <?php } ?>

  </div>
</div>