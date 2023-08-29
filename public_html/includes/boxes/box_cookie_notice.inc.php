<?php
  if (!settings::get('cookie_policy')) return;

  $box_cookie_notice = new ent_view();
  echo $box_cookie_notice->stitch('views/box_cookie_notice');
