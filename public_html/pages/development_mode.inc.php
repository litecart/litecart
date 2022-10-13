<?php
  document::$layout = 'blank';

  http_response_code(403);

  $_page = new ent_view();
  echo $_page->stitch('pages/development_mode');
