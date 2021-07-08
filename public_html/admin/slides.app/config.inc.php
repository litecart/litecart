<?php

  return $app_config = [
    'name' => language::translate('title_slides', 'Slides'),
    'default' => 'slides',
    'priority' => 0,
    'theme' => [
      'color' => '#a880d8',
      'icon' => 'fa-picture-o',
    ],
    'menu' => [
    ],
    'docs' => [
      'slides' => 'slides.inc.php',
      'edit_slide' => 'edit_slide.inc.php',
    ],
  ];
