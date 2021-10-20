<?php

  document::$snippets['title'] = [language::translate('index:head_title', 'Online Store'), settings::get('site_name')];
  document::$snippets['description'] = language::translate('index:meta_description', '');
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('') .'" />';
  document::$snippets['head_tags']['opengraph'] = '<meta property="og:url" content="'. document::href_ilink('') .'" />' . PHP_EOL
                                                . '<meta property="og:type" content="website" />' . PHP_EOL
                                                . '<meta property="og:image" content="'. document::href_link(WS_DIR_STORAGE . 'images/logotype.png') .'" />';

  $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/index.inc.php');

  echo $_page;
