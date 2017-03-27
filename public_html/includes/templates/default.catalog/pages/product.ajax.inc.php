{snippet:notices}

<div id="view-full-page">
  <a href="<?php echo htmlspecialchars($link); ?>"><?php echo language::translate('text_view_full_page', 'View full page'); ?> <?php echo functions::draw_fonticon('fa-external-link'); ?></a>
</div>

{snippet:breadcrumbs}

<?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATE . 'views/box_product.inc.php'); ?>