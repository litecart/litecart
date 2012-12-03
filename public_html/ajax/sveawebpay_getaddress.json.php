<?php
  require_once('../includes/app_header.inc.php');
  header('Content-type: text/plain; charset='. $system->language->selected['charset']);
  
  $client_id = '1571';
  $username = 'LUCE1';
  $password = 'c7c1c15e57f07257407c6f884bf3ec6c2869ef57469a13e471a1f35f7d08230caa583b8d1f23fb406493869791b4464660f67a538cf6122d34f67e47d63d3745';
  $test_mode = true;
  
// Call Soap and set up data
  $client = new SoapClient($test_mode ? 'https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL' : 'https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL');
  
// Handle response
  $response = $client->GetAddresses(array(
    'request' => array(
      'Auth' => array(
        'ClientNumber' => $client_id,
        'Username' => $username,
        'Password' => $password,
       ),
      'IsCompany' => empty($_GET['company']) ? 0 : 1,
      'CountryCode' => isset($_GET['country_code']) ? $_GET['country_code'] : '',
      'SecurityNumber' => isset($_GET['tax_id']) ? $_GET['tax_id'] : '',
    )
  ));
  
  if (empty($response->GetAddressesResult)) {
    echo '{"error":"'. $response->GetAddressesResult->ErrorMessage .'"}';
    exit;
  }
  
  if (!empty($response->GetAddressesResult->ErrorMessage)) {
    echo '{"error":"'. $response->GetAddressesResult->ErrorMessage .'"}';
  } else {
    $info = array_shift(array_values($response->GetAddressesResult->Addresses->CustomerAddress));
    echo '{"firstname":"'. $info->FirstName .'",'
       . '"lastname":"'. $info->LastName .'",'
       . '"firstname":"'. $info->LegalName .'",'
       . '"address1":"'. $info->AddressLine1 .'",'
       . '"address2":"'. $info->AddressLine2 .'",'
       . '"postcode":"'. $info->Postcode .'",'
       . '"city":"'. $info->Postarea .'"}';
  }
?>