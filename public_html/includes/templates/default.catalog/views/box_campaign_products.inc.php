<div id="box-campaigns" class="box">

  <h2 class="title"><?php echo language::translate('title_campaigns', 'Campaigns'); ?></h2>

  <div class="products row half-gutter text-center">
    <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
  </div>
</div>