<?php
  $manufacturers_query = $system->database->query(
    "select id, image, name from ". DB_TABLE_MANUFACTURERS ."
    where status
    and image != ''
    order by name asc;"
  );
  
  if ($system->database->num_rows($manufacturers_query) == 0) return;
  
  $system->document->snippets['head_tags']['jquery-marquee'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.marquee.js"></script>';
  
?>
<div id="manufacturer-logotypes" style="margin-bottom: 10px; max-width: 980px; height: 30px; overflow: hidden; position: relative; text-align: center;">
  <span>
<?php
  while($manufacturer = $system->database->fetch($manufacturers_query)) {
    for ($i=0; $i<15; $i++)
    echo '<a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'manufacturer.php', array('manufacturer_id' => $manufacturer['id'])) .'"><img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 0, 30, 'FIT') .'" style="margin: 0px 15px;" border="0"></a>';
  }
?>
  </span>
</div>

<script type="text/javascript">
  $(function(){
    $('#manufacturer-logotypes').each(function(){
      var $span = $(this).find('span');
      if ($(this).height() < $span.height()) {
        $span.css('white-space', 'nowrap');
        $(this).html('<marquee behavior="alternate" direction="right" scrollamount="1" onmouseover="this.scrollAmount=0" onmouseout="this.scrollAmount=1">'+ $(this).html() +'</marquee>');
      }
    })
  });
</script>
