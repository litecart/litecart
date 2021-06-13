<style>
#box-latest-products .products {
  padding: 2em 0;
  margin-bottom: -1em;
  margin-top: -1em;
}
#box-latest-products .product {
  width: 190px;
}
@media (min-width: 768px) {
  #box-latest-products .products {
    margin-bottom: -2em;
    margin-top: -2em;
  }
}
</style>

<section id="box-latest-products" class="box white">

  <h2 class="title"><?php echo language::translate('title_latest_products', 'Latest Products'); ?></h2>

  <div class="listing products" data-toggle="momentum-scroll auto-scroll">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>