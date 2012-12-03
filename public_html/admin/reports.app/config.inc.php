<?php

$app_config = array(
  'name' => $system->language->translate('title_reports', 'Reports'),
  'index' => 'monthly_sales.php',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_monthly_sales', 'Monthly Sales'),
      'link' => 'monthly_sales.php',
      'params' => array(),
    ),
  ),
);

?>