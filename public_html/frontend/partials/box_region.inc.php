<?php
  functions::draw_lightbox();

  $box_region = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_region.inc.php');

  echo $box_region->render();
