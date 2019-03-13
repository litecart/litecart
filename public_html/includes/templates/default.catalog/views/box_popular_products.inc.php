<section id="box-popular-products" class="box">

  <h2 class="title"><?php echo language::translate('title_popular_products', 'Popular Products'); ?></h2>

  <div class="listing products">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>