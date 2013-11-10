<?php
  
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once('../../../app_header.inc.php');
    header('Content-type: text/css');
  }
  
  $settings = unserialize(settings::get('store_template_catalog_settings'));
?>

<?php if (empty($settings['fixed_header'])) { ?>
#header-wrapper {
  position: absolute !important;
}
<?php } else { ?>
<?php }?>
