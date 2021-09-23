<section id="box-latest-products" class="box">

  <h2 class="title"><?php echo language::translate('title_latest_products', 'Latest Products'); ?></h2>

  <div class="listing products columns">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>