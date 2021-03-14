<section id="box-also-purchased-products" class="box white">

  <h2 class="title"><?php echo language::translate('title_also_purchased_products', 'Also Purchased Products'); ?></h2>

  <div class="listing products">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>