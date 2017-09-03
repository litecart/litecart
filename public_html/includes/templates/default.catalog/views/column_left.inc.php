<div id="column-left">
  <div id="navigation">
    <div class="toggle">
      <label for="mobile-menu-toggle"><?php echo functions::draw_fonticon('fa-bars'); ?></label>
    </div>
  </div>

  <input id="mobile-menu-toggle" type="checkbox" style="display: none;" />

  <div class="content">

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_category_tree.inc.php'); ?>

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_filter.inc.php'); ?>

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_recently_viewed_products.inc.php'); ?>

  </div>
</div>