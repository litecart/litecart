<section id="box-campaign-products" class="card">

  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_campaign_products', 'Campaign Products'); ?></h2>
  </div>

  <div class="card-body">
    <div data-toggle="momentumScroll">
      <div class="listing products scroll-content">
        <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
      </div>
    </div>
  </div>

</section>