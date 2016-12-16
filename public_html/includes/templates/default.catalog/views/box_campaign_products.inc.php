<div id="box-campaigns" class="box">

  <h3 class="title"><?php echo language::translate('title_campaigns', 'Campaigns'); ?></h3>

  <div class="products row half-gutter text-center">
    <?php foreach($products as $product) echo functions::draw_listing_product($product); ?>
  </div>
</div>