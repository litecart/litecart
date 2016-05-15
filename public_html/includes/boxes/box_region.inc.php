<?php
  functions::draw_fancybox('.fancybox-region', array(
    'centerOnScroll' => true,
    'hideOnContentClick' => false,
    'modal' => false,
    'speedIn' => 600,
    'transitionIn' => 'fade',
    'transitionOut' => 'fade',
    'type' => 'ajax',
    'scrolling' => 'false',
  ));

  $box_region = new view();

  echo $box_region->stitch('views/box_region');
?>
