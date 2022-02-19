<section id="box-campaign-products" class="box box-default">

  <h2 class="title"><?php echo language::translate('title_campaign_products', 'Campaign Products'); ?></h2>

  <div data-toggle="momentumScroll">
    <div class="listing products scroll-content">
      <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
    </div>
  </div>

</section>