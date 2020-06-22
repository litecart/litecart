<?php

  $box_filter = new ent_view();

  $box_filter->snippets = [
    'manufacturers' => [],
    'attributes' => [],
  ];

// Manufacturers
  if (empty($_GET['manufacturer_id'])) {
    $manufacturers_query = database::query(
      "select distinct m.id, m.name from ". DB_PREFIX ."products p
      left join ". DB_PREFIX ."manufacturers m on m.id = p.manufacturer_id ".
      (!empty($_GET['category_id']) ? " left join ". DB_PREFIX ."products_to_categories pc on pc.product_id = p.id " : "")."
      where p.status
      and manufacturer_id
      ". (!empty($_GET['category_id']) ? "and pc.category_id = " . (int)$_GET['category_id']  : "") ."
      order by m.name asc;"
    );

    if (database::num_rows($manufacturers_query) > 1) {
      while ($manufacturer = database::fetch($manufacturers_query)) {
        $box_filter->snippets['manufacturers'][] = [
          'id' => $manufacturer['id'],
          'name' => $manufacturer['name'],
          'href' => document::ilink('manufacturer', ['manufacturer_id' => $manufacturer['id']]),
        ];
      }
    }
  }

// Attributes
  $category_filters_query = database::query(
    "select cf.attribute_group_id as id, agi.name as name, cf.select_multiple from ". DB_PREFIX ."categories_filters cf
    left join ". DB_PREFIX ."attribute_groups_info agi on (agi.group_id = cf.attribute_group_id and agi.language_code = '". database::input(language::$selected['code']) ."')
    where category_id = ". (int)$_GET['category_id'] ."
    order by priority;"
  );

  while ($group = database::fetch($category_filters_query)) {
    $attribute_values_query = database::query(
      "select distinct cf.value_id as id, if(cf.custom_value != '', cf.custom_value, avi.name) as value from ". DB_PREFIX ."products_attributes cf
      left join ". DB_PREFIX ."attribute_values_info avi on (avi.value_id = cf.value_id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where product_id in (
        select product_id from ". DB_PREFIX ."products_to_categories
        where category_id = ". (int)$_GET['category_id'] ."
      )
      and cf.group_id = ". (int)$group['id'] ."
      order by `value`;"
    );

    $group['values'] = [];
    while ($value = database::fetch($attribute_values_query)) {
      $group['values'][] = $value;
    }

    if (empty($group['values'])) continue;

    $box_filter->snippets['attributes'][] = $group;
  }

  echo $box_filter->stitch('views/box_filter');
