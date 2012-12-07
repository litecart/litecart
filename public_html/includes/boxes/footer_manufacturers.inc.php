<ul class="navigation-vertical">
<?php  
    $manufacturers_query = $system->database->query(
      "select m.id, m.image, m.name
      from ". DB_TABLE_MANUFACTURERS ." m
      where status
      order by m.name asc;"
    );
    while ($manufacturer = $system->database->fetch($manufacturers_query)) {
      echo '  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'">'. $manufacturer['name'] .'</a>' . PHP_EOL;
    }
?>
</ul>