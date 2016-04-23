<?php @document::$snippets['column_left'] .= $box_customer_service_links; ?>
<?php if (!empty($box_page)) { ?>
  <!--snippet:box_page-->
<?php } else { ?>
<div style="overflow: hidden;">
  <div style="float: left; display: inline-block;">
    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_contact_us.inc.php'); ?>
  </div>

  <div style="float: right; display: inline-block;">
    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_store_map.inc.php'); ?>
  </div>
</div>
<?php } ?>
