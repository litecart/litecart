<section id="box-similar-products" class="box">

  <h2 class="title"><?php echo language::translate('title_similar_products', 'Similar Products'); ?></h2>

  <section class="listing products columns">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </section>

</section>