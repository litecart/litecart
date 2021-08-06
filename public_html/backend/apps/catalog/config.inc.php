<?php

  return $app_config = [
    'name' => language::translate('title_catalog', 'Catalog'),
    'default' => 'category_tree',
    'priority' => 0,
    'theme' => [
      'color' => '#d4ce12',
      'icon' => 'fa-th',
    ],
    'menu' => [
      [
        'title' => language::translate('title_category_tree', 'Category Tree'),
        'doc' => 'category_tree',
        'params' => [],
      ],
      [
        'title' => language::translate('title_products', 'Products'),
        'doc' => 'products',
        'params' => [],
      ],
      [
        'title' => language::translate('title_attributes', 'Attributes'),
        'doc' => 'attribute_groups',
        'params' => [],
      ],
      [
        'title' => language::translate('title_brands', 'Brands'),
        'doc' => 'brands',
        'params' => [],
      ],
      [
        'title' => language::translate('title_suppliers', 'Suppliers'),
        'doc' => 'suppliers',
        'params' => [],
      ],
      [
        'title' => language::translate('title_delivery_statuses', 'Delivery Statuses'),
        'doc' => 'delivery_statuses',
        'params' => [],
      ],
      [
        'title' => language::translate('title_sold_out_statuses', 'Sold Out Statuses'),
        'doc' => 'sold_out_statuses',
        'params' => [],
      ],
      [
        'title' => language::translate('title_stock_items', 'Stock Items'),
        'doc' => 'stock_items',
        'params' => [],
      ],
      [
        'title' => language::translate('title_stock_transactions', 'Stock Transactions'),
        'doc' => 'stock_transactions',
        'params' => [],
      ],
      [
        'title' => language::translate('title_quantity_units', 'Quantity Units'),
        'doc' => 'quantity_units',
        'params' => [],
      ],
      [
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
        'params' => [],
      ],
    ],
    'docs' => [
      'attribute_groups' => 'attribute_groups.inc.php',
      'attribute_values.json' => 'attribute_values.json.inc.php',
      'category_tree' => 'category_tree.inc.php',
      'edit_attribute_group' => 'edit_attribute_group.inc.php',
      'edit_product' => 'edit_product.inc.php',
      'edit_category' => 'edit_category.inc.php',
      'brands' => 'brands.inc.php',
      'edit_brand' => 'edit_brand.inc.php',
      'suppliers' => 'suppliers.inc.php',
      'edit_supplier' => 'edit_supplier.inc.php',
      'delivery_statuses' => 'delivery_statuses.inc.php',
      'edit_delivery_status' => 'edit_delivery_status.inc.php',
      'sold_out_statuses' => 'sold_out_statuses.inc.php',
      'edit_sold_out_status' => 'edit_sold_out_status.inc.php',
      'quantity_units' => 'quantity_units.inc.php',
      'edit_quantity_unit' => 'edit_quantity_unit.inc.php',
      'csv' => 'csv.inc.php',
      'category_picker' => 'category_picker.inc.php',
      'categories.json' => 'categories.json.inc.php',
      'products' => 'products.inc.php',
      'products.json' => 'products.json.inc.php',
      'stock_items' => 'stock_items.inc.php',
      'stock_item_picker' => 'stock_item_picker.inc.php',
      'stock_items.json' => 'stock_items.json.inc.php',
      'edit_stock_item' => 'edit_stock_item.inc.php',
      'stock_transactions' => 'stock_transactions.inc.php',
      'edit_stock_transaction' => 'edit_stock_transaction.inc.php',
    ],

    'search' => function($query) {

      $result = [
        'name' => language::translate('title_products', 'Products'),
        'results' => [],
      ];

      $code_regex = functions::format_regex_code($query);
      $query_fulltext = functions::format_mysql_fulltext($_GET['query']);

      $products_query = database::query(
        "select p.id, p.default_category_id, pi.name,
        (
          if(p.id = '". database::input($query) ."', 10, 0)
          + (match(pi.name) against ('". database::input($query_fulltext) ."' in boolean mode))
          + (match(pi.short_description) against ('". database::input($query_fulltext) ."' in boolean mode) / 2)
          + (match(pi.description) against ('". database::input($query_fulltext) ."' in boolean mode) / 3)
          + if(pi.name like '%". database::input($query) ."%', 3, 0)
          + if(pi.short_description like '%". database::input($query) ."%', 2, 0)
          + if(pi.description like '%". database::input($query) ."%', 1, 0)
          + if(p.code regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.sku regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)
          + if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)
          + if (p.id in (
            select product_id from ". DB_TABLE_PREFIX ."products_stock_options
            where sku regexp '". database::input($code_regex) ."'
          ), 5, 0)
        ) as relevance

        from ". DB_TABLE_PREFIX ."products p

        left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

        having relevance > 0
        order by relevance desc, id asc
        limit 5;"
      );

      if (!database::num_rows($products_query)) return;

      while ($product = database::fetch($products_query)) {
        $result['results'][] = [
          'id' => $product['id'],
          'title' => $product['name'],
          'description' => $product['default_category_id'] ? reference::category($product['default_category_id'])->name : '['.language::translate('title_root', 'Root').']',
          'link' => document::ilink('catalog/edit_product', ['product_id' => $product['id']]),
        ];
      }

      return [$result];
    },
  ];
