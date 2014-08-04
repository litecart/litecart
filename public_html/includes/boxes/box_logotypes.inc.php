<?php
  $manufacturers_query = database::query(
    "select id, image, name from ". DB_TABLE_MANUFACTURERS ."
    where status
    and image != ''
    order by rand();"
  );
  
  if (database::num_rows($manufacturers_query) == 0) return;
  
?>
<div id="logotypes-wrapper">
  <div id="logotypes">
    <ul class="list-horizontal">
<?php
  while($manufacturer = database::fetch($manufacturers_query)) {
    echo '      <li><a href="'. document::href_link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'"><img src="'. functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 0, 30, 'FIT') .'" alt="" title="'. $manufacturer['name'] .'" style="margin: 0px 15px;"></a></li>' . PHP_EOL;
  }
?>
    </ul>
  </div>
</div>
