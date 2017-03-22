<?php
  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  document::$snippets['title'][] = language::translate('checkout:head_title', 'Checkout');

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'));

  functions::draw_lightbox();

  $_page = new view();
  echo $_page->stitch('pages/checkout');
