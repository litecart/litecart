<?php

  $json = [
    'items' => [],
    'num_items' => cart::$total['num_items'],
    'total_value' => !empty(customer::$data['display_prices_including_tax']) ? cart::$total['subtotal'] + cart::$total['subtotal_tax'] : cart::$total['subtotal'],
    'formatted_total_value' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(cart::$total['subtotal'] + cart::$total['subtotal_tax']) : currency::format(cart::$total['subtotal']),
    'text_total' => language::translate('title_total', 'Total'),
  ];

  foreach (cart::$items as $key => $item) {
    $json['items'][] = [
      'key' => $key,
      'product_id' => $item['product_id'],
      'stock_option_id' => $item['stock_option_id'],
      'userdata' => $item['userdata'],
      'link' => document::rilink('product', ['product_id' => $item['product_id']]),
      'image' => $item['image'] ? 'storage://images/' . $item['image'] : 'no_image.png',
      'name' => $item['name'],
      'code' => $item['code'],
      'sku' => $item['sku'],
      'gtin' => $item['gtin'],
      'taric' => $item['taric'],
      'price' => !empty(customer::$data['display_prices_including_tax']) ? $item['price'] + $item['tax']: $item['price'],
      'formatted_price' => currency::format(!empty(customer::$data['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price']),
      'tax' => $item['tax'],
      'tax_class_id' => $item['tax_class_id'],
      'quantity' => $item['quantity'],
      'quantity_unit' => [
        'id' => $item['quantity_unit_id'],
        'name' => $item['quantity_unit_id'] ? reference::quantity_unit($item['quantity_unit_id'])->name : '',
      ],
    ];
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

  ob_end_clean();
  header('Content-type: application/json; charset='. mb_http_output());
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
