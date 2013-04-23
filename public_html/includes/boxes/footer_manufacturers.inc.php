<nav>
  <p><strong><?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?></strong></p>
  <ul class="list-vertical">
<?php  
  $manufacturers_query = $system->database->query(
    "select m.id, m.image, m.name
    from ". DB_TABLE_MANUFACTURERS ." m
    where status
    order by m.name asc;"
  );
  $i = 0;
  while ($manufacturer = $system->database->fetch($manufacturers_query)) {
    if (++$i == 10) {
      echo '  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'manufacturers.php') .'">'. $system->language->translate('title_more', 'More') .'...</a></li>' . PHP_EOL;
      break;
    }
    echo '  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'">'. $manufacturer['name'] .'</a>' . PHP_EOL;
  }
?>
  </ul>
</nav>