<?php

  function format_address($address) {
    
    $country_query = database::query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where iso_code_2 = '". database::input($address['country_code']) ."'
      limit 1;"
    );
    $country = database::fetch($country_query);
    if (empty($country)) trigger_error('Invalid country code for address format', E_USER_ERROR);
    
    if (isset($address['zone_code'])) {
      $zones_query = database::query(
        "select * from ". DB_TABLE_ZONES ."
        where country_code = '". database::input($country['iso_code_2']) ."'
        and code = '". database::input($address['zone_code']) ."'
        limit 1;"
      );
      $zone = database::fetch($zones_query);
    }
    
    $translation_map = array(
      '%company' => !empty($address['company']) ? $address['company'] : '',
      '%firstname' => !empty($address['firstname']) ? $address['firstname'] : '',
      '%lastname' => !empty($address['lastname']) ? $address['lastname'] : '',
      '%address1' => !empty($address['address1']) ? $address['address1'] : '',
      '%address2' => !empty($address['address2']) ? $address['address2'] : '',
      '%city' => !empty($address['city']) ? $address['city'] : '',
      '%postcode' => !empty($address['postcode']) ? $address['postcode'] : '',
      '%country_code' => $country['iso_code_2'],
      '%country_name' => $country['name'],
      '%zone_code' => !empty($zone['code']) ? $zone['code'] : '',
      '%zone_name' => !empty($zone['name']) ? $zone['name'] : '',
    );
    
    $output = $country['address_format'] ? $country['address_format'] : settings::get('default_address_format');
    
    foreach ($translation_map as $search => $replace) {
      $output = str_replace($search, $replace, $output);
    }
    
    while (strpos($output, "\r\n\r\n") !== false) $output = str_replace("\r\n\r\n", "\r\n", $output);
    while (strpos($output, "\r\r") !== false) $output = str_replace("\r\r", "\n\n", $output);
    while (strpos($output, "\n\n") !== false) $output = str_replace("\n\n", "\n\n", $output);
    
    $output = trim($output);
    
    return $output;
  }
  
  function format_safe_html($html, $blacklist=array(), $passed_by_user=true) {
    
  // Get array representations of the safe tags and attributes:
    $safeTags = explode(' ', 'a abbr acronym address b bdo big blockquote br caption center cite code col colgroup dd del dfn dir div dl dt em font h1 h2 h3 h4 h5 h6 hr i img ins kbd legend li ol p pre q s samp small span strike strong sub sup table tbody td tfoot th thead tr tt u ul var article aside figure footer header nav section rp rt ruby dialog hgroup mark time');
    $safeAttributes = explode(' ', 'href src title alt type rowspan colspan lang');
    $urlAttributes = explode(' ', 'href src');

    // Parse the HTML into a document object:
    $dom = new DOMDocument();
    $dom->loadHTML('<div>' . $html . '</div>');

  // Loop through all of the nodes:
    $stack = new SplStack();
    $stack->push($dom->documentElement);

    while ($stack->count() > 0) {
    // Get the next element for processing:
      $element = $stack->pop();

    // Add all the element's child nodes to the stack:
      foreach ($element->childNodes as $child) {
        if ($child instanceof DOMElement) {
          $stack->push($child);
        }
      }

    // And now, we do the filtering:
      if (!in_array(strtolower($element->nodeName), $safeTags)) {
      // It's not a safe tag; unwrap it:
        while ($element->hasChildNodes()) {
          $element->parentNode->insertBefore($element->firstChild, $element);
        }

      // Finally, delete the offending element:
        $element->parentNode->removeChild($element);
      } else {
      // The tag is safe; now filter its attributes:
        for ($i = 0; $i < $element->attributes->length; $i++) {
          $attribute = $element->attributes->item($i);
          $name = strtolower($attribute->name);

          if (!in_array($name, $safeAttributes) || (in_array($name, $urlAttributes) && substr($attribute->value, 0, 7) !== 'http://')) {
          // Found an unsafe attribute; remove it:
            $element->removeAttribute($attribute->name);
            $i--;
          }
        }
      }
    }

  // Finally, return the safe HTML, minus the DOCTYPE, <html> and <body>:
    $html  = $dom->saveHTML();
    $start = strpos($html, '<div>');
    $end   = strrpos($html, '</div>');

    return substr($html, $start + 5, $end - $start - 5);
  }

?>