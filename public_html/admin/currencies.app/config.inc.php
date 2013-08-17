<?php

$app_config = array(
  'name' => $GLOBALS['system']->language->translate('title_currencies', 'Currencies'),
  'default' => 'currencies',
  'icon' => 'icon.png',
  'menu' => array(),
  'docs' => array(
    'currencies' => 'currencies.inc.php',
    'edit_currency' => 'edit_currency.inc.php',
  ),
);

?>