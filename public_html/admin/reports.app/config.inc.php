<?php

$app_config = array(
  'name' => $GLOBALS['system']->language->translate('title_reports', 'Reports'),
  'default' => 'monthly_sales',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'title' => $GLOBALS['system']->language->translate('title_monthly_sales', 'Monthly Sales'),
      'doc' => 'monthly_sales',
      'params' => array(),
    ),
  ),
  'docs' => array(
    'monthly_sales' => 'monthly_sales.inc.php',
  ),
);

?>