<?php

  $app_config = array(
    'name' => language::translate('title_reports', 'Reports'),
    'default' => 'monthly_sales',
    'theme' => array(
      'color' => '#b79d82',
      'icon' => 'fa-pie-chart',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_monthly_sales', 'Monthly Sales'),
        'doc' => 'monthly_sales',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_most_sold_products', 'Most Sold Products'),
        'doc' => 'most_sold_products',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_most_shopping_customers', 'Most Shopping Customers'),
        'doc' => 'most_shopping_customers',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'monthly_sales' => 'monthly_sales.inc.php',
      'most_sold_products' => 'most_sold_products.inc.php',
      'most_shopping_customers' => 'most_shopping_customers.inc.php',
    ),
  );
