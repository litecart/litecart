<style>
body {
  padding: 60px 15px;
}
#box-maintenance-mode {
  display: block;
  text-align: center;
  padding: 30px;
  border-radius: 0px 25px 0px 25px;
  background: #fff;
  box-shadow: 0px 0px 60px rgba(0,0,0,0.25);
  margin: 0 auto;
  max-width: 640px;
}
</style>

<div id="box-maintenance-mode">
  <img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" style="max-width: 250px; max-height: 60px;" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
  <hr />
  <h1><?php echo language::translate('maintenance_mode:title', 'Maintenance Mode'); ?></h1>
  <p><?php echo language::translate('maintenance_mode:description', 'The site is currently in maintenance mode and we will be back shortly.'); ?></p>
</div>