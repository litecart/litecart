<?php

$app_config = array(
  'name' => $system->language->translate('title_discount_codes', 'Discount Codes'),
  'default' => 'discount_codes',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'title' => $system->language->translate('title_discount_codes', 'Discount Codes'),
      'doc' => 'discount_codes',
      'params' => array(),
    ),
  ),
  'docs' => array(
    'discount_codes' => 'discount_codes.inc.php',
    'edit_discount_code' => 'edit_discount_code.inc.php',
  ),
);

?>