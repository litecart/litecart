<div class="row">
  <div class="col-md-4">
    <p style="font-size: 1.25em;"><?php echo language::translate('description_no_items_in_cart', 'There are no items in your cart.'); ?></p>
    <div><a class="btn btn-success btn-lg" href="<?php echo document::href_ilink(''); ?>"><?php echo language::translate('title_back', 'Back'); ?></a></div>
  </div>

  <div class="col-md-8">
    <?php include 'app://includes/partials/box_recently_viewed_products.inc.php'; ?>
  </div>
</div>

<?php include 'app://includes/partials/box_categories.inc.php'; ?>
