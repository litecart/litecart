<?php

  header('X-Robots-Tag: noindex');

  $shopping_cart = &session::$data['checkout']['shopping_cart'];

  if (empty($shopping_cart->data['items'])) return;

  $box_checkout_summary = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_checkout_summary.inc.php');

  $box_checkout_summary->snippets = [
    'shopping_cart' => $shopping_cart->data,
    'error' => $shopping_cart->validate(),
    'consent' => null,
    'confirm' => !empty($shopping_cart->payment->selected['confirm']) ? $shopping_cart->payment->selected['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
  ];

  $privacy_policy_id = settings::get('privacy_policy');
  $terms_of_purchase_id = settings::get('terms_of_purchase');

  switch(true) {
    case ($terms_of_purchase_id && $privacy_policy_id):
      $box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy_and_terms_of_purchase', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
      break;
    case ($privacy_policy_id):
      $box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.');
      break;
    case ($terms_of_purchase_id):
      $box_checkout_summary->snippets['consent'] = language::translate('consent:terms_of_purchase', 'I have read the <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
      break;
  }

  $box_checkout_summary->snippets['consent'] = strtr($box_checkout_summary->snippets['consent'], [
    '%privacy_policy_link' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
    '%terms_of_purchase_link' => document::href_ilink('information', ['page_id' => $terms_of_purchase_id]),
  ]);

  echo $box_checkout_summary->render();
