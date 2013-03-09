<?php
  require_once('../includes/app_header.inc.php');
  header('Content-type: application/json; charset='. $system->language->selected['charset']);
  
  echo '{'
     . '"num_items":"'.$system->cart->data['total']['items'].'",'
     . '"total":"'. (($system->settings->get('display_prices_including_tax')) ? $system->currency->format($system->cart->data['total']['value'] + $system->cart->data['total']['tax']) : $system->currency->format($system->cart->data['total']['value'])) .'",'
     . '}';
?>