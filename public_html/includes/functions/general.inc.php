<?php

  function general_url_friendly($text) {
    global $system;
    
  // Remove HTML tags
    $text = strip_tags($text);
    
  // Decode special characters
    $text = html_entity_decode($text, ENT_QUOTES, $system->language->selected['charset']);
    
  // Treat special cases
    $special_cases = array('&' => 'and');
    $text = str_replace(array_keys($special_cases), array_values($special_cases), $text);
    
  // Remove system characters []
    $text = preg_replace("/\[.*\]/U", "", $text);
    
  // Convert foreign characters
    $text = htmlentities($text, ENT_QUOTES, $system->language->selected['charset']);
    $text = preg_replace('/&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);/i', '$1', $text);
    
  // Keep a-z0-9 and convert symbols to -
    $text = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $text);
    $text = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $text);
    $text = trim($text, '-');
    
  // Convert to lowercases
    $text = strtolower($text);
    
    return $text;
  }
  
  function general_order_public_checksum($order_id, $print=false) {
    global $system;
    
    $query = $system->database->query(
      "select * from ". DB_TABLE_ORDERS ."
      where id = '". (int)$order_id ."'
      limit 1;"
    );
    $order = $system->database->fetch($query);
    
    $checksum = md5($order['id'] . $order['uid'] . $order['customer_email'] . $order['date_created']);
    
    return $checksum;
  }
  
?>