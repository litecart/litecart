<section id="box-campaign-products" class="box">

  <h2 class="title"><?php echo language::translate('title_campaign_products', 'Campaign Products'); ?></h2>

  <div class="listing products columns">
    <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
  </div>

</section>