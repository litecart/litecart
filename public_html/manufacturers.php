<?php
  require_once('includes/config.inc.php');
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
  
  breadcrumbs::add(language::translate('title_manufacturers', 'Manufacturers'), document::link(basename(__FILE__)));
  
  document::$snippets['title'][] = language::translate('manufacturers.php:head_title', '');
  document::$snippets['keywords'] = language::translate('manufacturers.php:meta_keywords', '');
  document::$snippets['description'] = language::translate('manufacturers.php:meta_description', '');
  
  include(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
  
?>
  <div class="box" style="margin-top: 0px" id="box-manufacturers">
    <div class="heading"><h1><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1></div>
    <div class="content">
      <ul class="listing-wrapper" class="manufacturers">
<?php
    $manufacturers_query = database::query(
      "select m.id, m.name, m.image, mi.short_description, mi.link
      from ". DB_TABLE_MANUFACTURERS ." m
      left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (mi.manufacturer_id = m.id and mi.language_code = '". language::$selected['code'] ."')
      where status
      order by name;"
    );
    while($manufacturer = database::fetch($manufacturers_query)) {
      echo functions::draw_listing_manufacturer($manufacturer);
    }
?>
      </ul>
    </div>
  </div>
<?php  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>