<?php
  ob_end_clean();
  header('Content-type: text/plain; charset='. language::$selected['charset']);

  $customer_query = database::query(
    "select * from ". DB_TABLE_CUSTOMERS ."
    where id = '". database::input($_REQUEST['customer_id']) ."'
    limit 1;"
  );
  $customer = database::fetch($customer_query);
  if (empty($customer)) exit;

  $json = array(
    'tax_id' => !empty($customer['tax_id']) ? $customer['tax_id'] : '',
    'company' => !empty($customer['company']) ? $customer['company'] : '',
    'firstname' => !empty($customer['firstname']) ? $customer['firstname'] : '',
    'lastname' => !empty($customer['lastname']) ? $customer['lastname'] : '',
    'address1' => !empty($customer['address1']) ? $customer['address1'] : '',
    'address2' => !empty($customer['address2']) ? $customer['address2'] : '',
    'postcode' => !empty($customer['postcode']) ? $customer['postcode'] : '',
    'city' => !empty($customer['city']) ? $customer['city'] : '',
    'country_code' => !empty($customer['country_code']) ? $customer['country_code'] : '',
    'zone_code' => !empty($customer['zone_code']) ? $customer['zone_code'] : '',
    'phone' => !empty($customer['phone']) ? $customer['phone'] : '',
    'mobile' => !empty($customer['mobile']) ? $customer['mobile'] : '',
    'email' => !empty($customer['email']) ? $customer['email'] : '',
  );

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
?>