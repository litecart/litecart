<?php

$app_config = array(
  'name' => $system->language->translate('title_tax', 'Tax'),
  'index' => 'tax_classes.php',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_tax_classes', 'Tax Classes'),
      'link' => 'tax_classes.php'
    ),
    array(
      'name' => $system->language->translate('title_tax_rates', 'Tax Rates'),
      'link' => 'tax_rates.php'
    ),
  ),
);

?>