<div class="row">
  <div class="col-full">
    <h3><?php echo language::translate('title_also_purchased_products', 'Also Purchased Products'); ?></h3>
  </div>
</div>

<div class="products row half-gutter text-center">
  <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
</div>