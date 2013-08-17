<?php

$app_config = array(
  'name' => $GLOBALS['system']->language->translate('title_slides', 'Slides'),
  'default' => 'slides',
  'icon' => 'icon.png',
  'menu' => array(
  ),
  'docs' => array(
    'slides' => 'slides.inc.php',
    'edit_slide' => 'edit_slide.inc.php',
  ),
);

?>