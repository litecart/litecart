<?php
  functions::draw_lightbox();

  $box_region = new ent_view();

  echo $box_region->render(FS_DIR_TEMPLATE . 'partials/box_region.inc.php');
