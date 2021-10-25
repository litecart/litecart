<section id="box-similar-products" class="box box-default">

  <h2 class="title"><?php echo language::translate('title_similar_products', 'Similar Products'); ?></h2>

  <div style="position: relative; margin: -2em;">
    <div class="listing products" data-toggle="momentumScroll">
      <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
    </div>
  </div>

</section>