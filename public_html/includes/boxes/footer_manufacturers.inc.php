<nav>
  <h4><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h4>
  <ul class="list-vertical">
<?php  
  $manufacturers_query = database::query(
    "select m.id, m.image, m.name
    from ". DB_TABLE_MANUFACTURERS ." m
    where status
    order by m.name asc;"
  );
  $i = 0;
  while ($manufacturer = database::fetch($manufacturers_query)) {
    if (++$i == 10) {
      echo '  <li><a href="'. document::href_link(WS_DIR_HTTP_HOME . 'manufacturers.php') .'">'. language::translate('title_more', 'More') .'...</a></li>' . PHP_EOL;
      break;
    }
    echo '  <li><a href="'. document::href_link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'">'. $manufacturer['name'] .'</a>' . PHP_EOL;
  }
?>
  </ul>
</nav>