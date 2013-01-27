<?php
  require_once('includes/app_header.inc.php');
  
  $system->breadcrumbs->add($system->language->translate('title_designers', 'Designers'), $system->document->link(basename(__FILE__)));
  
?>
  <div class="box" style="margin-top: 0px" id="box-designers">
    <div class="heading"><h1><?php echo $system->language->translate('title_designers', 'Designers'); ?></h1></div>
    <div class="content">
      <ul class="listing-wrapper designers">
<?php
    $designers_query = $system->database->query(
      "select d.id, d.name, d.image, di.short_description, di.link
      from ". DB_TABLE_DESIGNERS ." d
      left join ". DB_TABLE_DESIGNERS_INFO ." di on (di.designer_id = d.id and di.language_code = '". $system->language->selected['code'] ."')
      where status
      order by name;"
    );
    while($designer = $system->database->fetch($designers_query)) {
?>
      <li style="display: inline-block;" class="designer">
        <a href="<?php echo $system->document->href_link('designer.php', array('designer_id' => $designer['id'])); ?>"><img src="<?php echo $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $designer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 234, 60, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" width="234" height="60" title="<?php echo $designer['name']; ?>" /></a>
      </li>
<?php
    }
?>
      </ul>
    </div>
  </div>
<?php  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>