<?php
  functions::draw_lightbox('a.lightbox');

  $box_region = new view();

  echo $box_region->stitch('views/box_region');
