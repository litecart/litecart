<div id="region">
  <div class="language"><img src="<?php echo WS_DIR_IMAGES .'icons/languages/'. language::$selected['code'] .'.png'; ?>" alt="<?php echo language::$selected['name']; ?>" title="<?php echo language::$selected['name']; ?>" /></div>
  <div class="currency" title="<?php echo currency::$selected['name']; ?>"><span><?php echo currency::$selected['code']; ?></span></div>
  <div class="country" title="<?php echo functions::reference_get_country_name(customer::$data['country_code']); ?>"><?php echo functions::reference_get_country_name(customer::$data['country_code']); ?></div>
  <div class="change"><a class="lightbox" href="<?php echo document::href_ilink('regional_settings'); ?>"><?php echo language::translate('title_change', 'Change'); ?></a></div>
</div>
