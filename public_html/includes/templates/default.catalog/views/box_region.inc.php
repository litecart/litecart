<div id="region">
  <div class="language"><?php echo language::$selected['name']; ?></div>
  <div class="currency" title="<?php echo currency::$selected['name']; ?>"><span><?php echo currency::$selected['code']; ?></span></div>
  <div class="country"><img src="<?php echo WS_DIR_IMAGES .'countries/'. strtolower(customer::$data['country_code']) .'.png'; ?>" style="vertical-align: baseline;" alt="<?php echo reference::country(customer::$data['country_code'])->name; ?>" title="<?php echo reference::country(customer::$data['country_code'])->name; ?>" /></div>
  <div class="change"><a class="lightbox" href="<?php echo document::href_ilink('regional_settings'); ?>"><?php echo language::translate('title_change', 'Change'); ?></a></div>
</div>
