<?php

  header('X-Robots-Tag: noindex');
  header('Content-Type: application/json; charset=UTF-8');
  header('Cache-Control: no-store');

  echo json_encode(['status' => 'ok']);

  exit;
