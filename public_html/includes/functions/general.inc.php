<?php

  function general_url_friendly($text) {
    global $system;
    
    $friendly = $text;
    $friendly = strip_tags($friendly);
    $friendly = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '$1', $friendly); // If entities are input
    $friendly = preg_replace("/\[.*\]/U", "", $friendly);
    $friendly = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $friendly);
    $friendly = htmlentities($friendly, ENT_COMPAT, $system->language->selected['charset']);
    $friendly = preg_replace('/&([a-z])+?(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '$1', $friendly);
    $friendly = preg_replace(array("`[^a-z0-9]`i","`[-]+`"), "-", $friendly);
    $friendly = strtolower(trim($friendly, '-'));
    
    return $friendly;
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