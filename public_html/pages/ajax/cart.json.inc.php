<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  $json = array(
    'items' => array(),
    'quantity' => cart::$total['items'],
    'value' => !empty(customer::$data['display_prices_including_tax']) ? cart::$total['value'] + cart::$total['tax'] : cart::$total['value'],
    'formatted_value' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(cart::$total['value'] + cart::$total['tax']) : currency::format(cart::$total['value']),
    'text_total' => language::translate('title_total', 'Total'),
  );

  foreach (cart::$items as $key => $item) {
    $json['items'][] = array(
      'key' => $key,
      'product_id' => $item['product_id'],
      'options' => $item['options'],
      'link' => document::ilink('product', ['product_id' => $item['product_id']]),
      'thumbnail' => document::link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $item['image'], 320, 320, 'FIT_USE_WHITESPACING')),
      'name' => $item['name'],
      'sku' => $item['sku'],
      'gtin' => $item['gtin'],
      'taric' => $item['taric'],
      'price' => !empty(customer::$data['display_prices_including_tax']) ? $item['price'] + $item['tax']: (float)$item['price'],
      'formatted_price' => currency::format(!empty(customer::$data['display_prices_including_tax']) ? $item['price'] + $item['tax']: (float)$item['price']),
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => (float)$item['quantity'],
      'quantity_unit' => $item['quantity_unit'],
      'link' => document::ilink('product', array('product_id' => $item['product_id'])),
    );
  }

  if (!empty(notices::$data['warnings'])) {
    $warnings = array_values(notices::$data['warnings']);
    $json['alert'] = array_shift($warnings);
  }

  if (!empty(notices::$data['errors'])) {
    $errors = array_values(notices::$data['errors']);
    $json['alert'] = array_shift($errors);
  }

  notices::reset();

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
