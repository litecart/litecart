<?php
  require_once('includes/config.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
  
  $system->breadcrumbs->add($system->language->translate('title_manufacturers', 'Manufacturers'), $system->document->link(basename(__FILE__)));
  
?>
  <div class="box" style="margin-top: 0px" id="box-manufacturers">
    <div class="heading"><h1><?php echo $system->language->translate('title_manufacturers', 'Manufacturers'); ?></h1></div>
    <div class="content">
<?php
    $manufacturers_query = $system->database->query(
      "select m.id, m.name, m.image, mi.short_description, mi.link
      from ". DB_TABLE_MANUFACTURERS ." m
      left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (mi.manufacturer_id = m.id and mi.language_code = '". $system->language->selected['code'] ."')
      where status
      order by name;"
    );
    while($manufacturer = $system->database->fetch($manufacturers_query)) {
      echo $system->functions->draw_listing_manufacturer($manufacturer);
    }
?>
    </div>
  </div>
<?php  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>