<?php

  $box_filter = new ent_view();

  $box_filter->snippets = array(
    'manufacturers' => array(),
    'attributes' => array(),
  );

// Manufacturers
  if (empty($_GET['manufacturer_id'])) {
    $manufacturers_query = database::query(
      "select distinct m.id, m.name from ". DB_TABLE_PRODUCTS ." p
      left join ". DB_TABLE_MANUFACTURERS ." m on m.id = p.manufacturer_id ".
      (!empty($_GET['category_id']) ? " left join " . DB_TABLE_PRODUCTS_TO_CATEGORIES . " pc on pc.product_id = p.id " : "")."
      where p.status
      and manufacturer_id
      ". (!empty($_GET['category_id']) ? "and pc.category_id = " . (int)$_GET['category_id']  : "") ."
      order by m.name asc;"
    );
    if (database::num_rows($manufacturers_query)) {

      while ($manufacturer = database::fetch($manufacturers_query)) {
        $box_filter->snippets['manufacturers'][] = array(
          'id' => $manufacturer['id'],
          'name' => $manufacturer['name'],
          'href' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
        );
      }
    }
  }

// Attributes
  $category_filters_query = database::query(
    "select cf.attribute_group_id as id, agi.name as name, cf.select_multiple from ". DB_TABLE_CATEGORIES_FILTERS ." cf
    left join ". DB_TABLE_ATTRIBUTE_GROUPS_INFO ." agi on (agi.group_id = cf.attribute_group_id and agi.language_code = '". database::input(language::$selected['code']) ."')
    where category_id = ". (int)$_GET['category_id'] ."
    order by priority;"
  );

  while ($group = database::fetch($category_filters_query)) {

    $attribute_values_query = database::query(
      "select distinct cf.value_id as id, if(cf.custom_value != '', cf.custom_value, avi.name) as value from ". DB_TABLE_PRODUCTS_ATTRIBUTES ." cf
      left join ". DB_TABLE_ATTRIBUTE_VALUES_INFO ." avi on (avi.value_id = cf.value_id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where product_id in (
        select product_id from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
        where category_id = ". (int)$_GET['category_id'] ."
      )
      and cf.group_id = ". (int)$group['id'] ."
      order by `value`;"
    );

    $group['values'] = array();
    while ($value = database::fetch($attribute_values_query)) {
      $group['values'][] = $value;
    }

    if (empty($group['values'])) continue;

    $box_filter->snippets['attributes'][] = $group;
  }

  if (empty($box_filter->snippets['manufacturers']) && empty($box_filter->snippets['attributes'])) return;

  echo $box_filter->stitch('views/box_filter');
