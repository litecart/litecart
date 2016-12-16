<div id="box-latest-products" class="box">

  <h3 class="title"><?php echo language::translate('title_latest_products', 'Latest Products'); ?></h3>

  <div class="products row half-gutter text-center">
    <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
  </div>
</div>