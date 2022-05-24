<header id="header" class="hidden-print">
  <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
    <img src="<?php echo document::href_rlink(FS_DIR_STORAGE . 'images/logotype.png'); ?>" alt="<?php echo settings::get('site_name'); ?>" title="<?php echo settings::get('site_name'); ?>" />
  </a>

    <div class="text-center hidden-xs">
      <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_region.inc.php'); ?>
    </div>

    <div class="text-end">
      <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_shopping_cart.inc.php'); ?>
    </div>
  </header>