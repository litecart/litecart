<style>
#box-similar-products .products {
  padding: 2em 0;
  margin-bottom: -1em;
  margin-top: -1em;
}
#box-similar-products .product {
  width: 190px;
}
@media (min-width: 768px) {
  #box-similar-products .products {
    margin-bottom: -2em;
    margin-top: -2em;
  }
}
</style>

<section id="box-similar-products" class="box box-default">

  <h2 class="title"><?php echo language::translate('title_similar_products', 'Similar Products'); ?></h2>

  <div class="listing products" data-toggle="momentum-scroll auto-scroll">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>