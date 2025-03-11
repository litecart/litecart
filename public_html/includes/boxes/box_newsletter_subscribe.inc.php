<?php

  functions::draw_lightbox();

  $box_newsletter_subscribe = new ent_view();
  echo $box_newsletter_subscribe->stitch('views/box_newsletter_subscribe');
