<div class="box" id="box-most-popular">
  <div class="heading"><h3><?php echo language::translate('title_most_popular', 'Most Popular'); ?></h3></div>
  <div class="content">
    <?php if ($products) { ?>
    <ul class="listing-wrapper products">
      <?php foreach ($products as $product) echo functions::draw_listing_product($product, 'column'); ?>
    </ul>
    <?php } ?>
  </div>
</div>