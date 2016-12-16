<div id="column-left">
  <div id="navigation">
    <div class="toggle">
      <label for="mobile-menu-toggle"><?php echo functions::draw_fonticon('fa-bars'); ?></label>
    </div>

    <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
    <div class="search input-group">
      <span class="input-group-addon"><?php echo functions::draw_fonticon('fa-search'); ?></span>
      <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search', 'Search') .'"'); ?>
    </div>
    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <input id="mobile-menu-toggle" type="checkbox" style="display: none;" />

  <div class="content">

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_category_tree.inc.php'); ?>

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_filter.inc.php'); ?>

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_recently_viewed_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_account.inc.php'); ?>

  </div>
</div>