<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once('../app_header.inc.php');
    header('Content-type: text/html; charset='. $system->language->selected['charset']);
    $system->document->layout = 'default';
    $system->document->viewport = 'ajax';
  }
?>