<?php

  header('X-Robots-Tag: noindex');

  $order = &session::$data['order'];

  if (empty($order->data['items'])) return;

  $box_checkout_summary = new ent_view();

  $box_checkout_summary->snippets = [
    'order_total' => $order->data['order_total'],
    'tax_total' => $order->data['tax_total'],
    'payment_due' => $order->data['payment_due'],
    'currency_code' => $order->data['currency_code'],
    'error' => $order->validate(),
    'selected_shipping' => null,
    'selected_payment' => null,
    'consent' => null,
    'confirm' => !empty($order->payment->selected['confirm']) ? $order->payment->selected['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
  ];

  if (!empty($order->shipping->selected)) {
    $box_checkout_summary->snippets['selected_shipping'] = [
      'icon' => is_file(FS_DIR_APP . $order->shipping->selected['icon']) ? functions::image_thumbnail(FS_DIR_APP . $order->shipping->selected['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $order->shipping->selected['title'],
    ];
  }

  if (!empty($order->payment->selected)) {
    $box_checkout_summary->snippets['selected_payment'] = [
      'icon' => is_file(FS_DIR_APP . $order->payment->selected['icon']) ? functions::image_thumbnail(FS_DIR_APP . $order->payment->selected['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $order->payment->selected['title'],
    ];
  }

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

  echo $box_checkout_summary->stitch('views/box_checkout_summary');
