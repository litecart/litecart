<?php

  document::$title = [language::translate('index:head_title', 'Online Store'), settings::get('store_name')];
  document::$description = language::translate('index:meta_description', '');

  document::add_head_tags([
    '<link rel="canonical" href="'. document::href_ilink('') .'" />',
  ], 'canonical');

  document::add_head_tags([
    '<meta property="og:url" content="'. document::href_ilink('') .'" />',
    '<meta property="og:type" content="website" />',
    '<meta property="og:image" content="'. document::href_rlink('storage://images/logotype.png') .'" />',
  ], 'opengraph');

  $_page = new ent_view();

  echo $_page->render(FS_DIR_TEMPLATE . 'pages/index.inc.php');
