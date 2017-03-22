<?php
  header('Content-type: text/plain; charset='. language::$selected['charset']);

  if (empty($_GET['trigger'])) die('{}');

  $customer = new mod_customer();

  $result = $customer->get_address(array_merge($_POST, $_GET));

  if (empty($result)) die('{}');

  if (!empty($result['error'])) die('{}');

  $json = array(
    'tax_id' => isset($result['tax_id']) ? $result['tax_id'] : '',
    'company' => isset($result['company']) ? $result['company'] : '',
    'firstname' => isset($result['firstname']) ? $result['firstname'] : '',
    'lastname' => isset($result['lastname']) ? $result['lastname'] : '',
    'address1' => isset($result['address1']) ? $result['address1'] : '',
    'address2' => isset($result['address2']) ? $result['address2'] : '',
    'postcode' => isset($result['postcode']) ? $result['postcode'] : '',
    'city' => isset($result['city']) ? $result['city'] : '',
    'country_code' => isset($result['country_code']) ? $result['country_code'] : '',
    'zone_code' => isset($result['zone_code']) ? $result['zone_code'] : '',
    'phone' => isset($result['phone']) ? $result['phone'] : '',
    'email' => isset($result['email']) ? $result['email'] : '',
    'alert' => isset($result['alert']) ? $result['alert'] : '',
  );

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
