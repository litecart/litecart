<?php

  // Summary validation stub for checkout confirmation flow

  if (empty(session::$data['checkout']['order'])) return;

  $order = &session::$data['checkout']['order'];

  $order->refresh_total();
