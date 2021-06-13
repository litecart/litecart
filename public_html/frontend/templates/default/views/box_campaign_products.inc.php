<style>
#box-campaign-products .products {
  padding: 2em 0;
  margin-bottom: -1em;
  margin-top: -1em;
}
#box-campaign-products .product {
  width: 190px;
}
@media (min-width: 768px) {
  #box-campaign-products .products {
    margin-bottom: -2em;
    margin-top: -2em;
  }
}
</style>

<section id="box-campaign-products" class="box white">

  <h2 class="title"><?php echo language::translate('title_campaign_products', 'Campaign Products'); ?></h2>

  <div class="listing products" data-toggle="momentum-scroll auto-scroll">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>