<header id="header" class="hidden-print">
  <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
    <img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
  </a>

  <div class="hidden-xs">
    <?php include 'app://frontend/partials/box_region.inc.php'; ?>
  </div>

  <div class="text-end">
    <?php include 'app://frontend/partials/box_shopping_cart.inc.php'; ?>
  </div>
</header>