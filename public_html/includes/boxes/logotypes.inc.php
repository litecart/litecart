<?php
  $manufacturers_query = $system->database->query(
    "select id, image, name from ". DB_TABLE_MANUFACTURERS ."
    where status
    and image != ''
    order by rand();"
  );
  
  if ($system->database->num_rows($manufacturers_query) == 0) return;
  
  $system->document->snippets['head_tags']['jquery-marquee'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.marquee.js"></script>';
?>
<div id="manufacturer-logotypes-wrapper">
  <ul id="manufacturer-logotypes" class="list-horizontal">
<?php
  while($manufacturer = $system->database->fetch($manufacturers_query)) {
    echo '  <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'"><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 0, 30, 'FIT') .'" alt="" style="margin: 0px 15px;"></a></li>' . PHP_EOL;
  }
?>
  </ul>
</div>
<script>
  $('#manufacturer-logotypes').each(function(){
    if($(this)[0].scrollWidth>$(this).outerWidth()){
      var scroll = $(this)[0].scrollWidth-$(this).outerWidth();
      $(this).scrollLeft(0);
      $(this).animate({scrollLeft:scroll},scroll*75,'linear');
    }
  });
</script>