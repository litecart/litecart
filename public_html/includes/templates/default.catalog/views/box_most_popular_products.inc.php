<div id="box-most-popular" class="box">

  <h3 class="title"><?php echo language::translate('title_most_popular', 'Most Popular'); ?></h3>

  <div class="products row half-gutter text-center">
    <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</div>