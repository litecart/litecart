<section id="box-similar-products" class="box white">

  <h2 class="title"><?php echo language::translate('title_similar_products', 'Similar Products'); ?></h2>

  <section class="listing products">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </section>

</section>