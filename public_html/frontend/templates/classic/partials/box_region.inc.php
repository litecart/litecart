<section id="region">
  <div class="language"><?php echo language::$selected['name']; ?></div>
  <div class="currency" title="<?php echo currency::$selected['name']; ?>"><span><?php echo currency::$selected['code']; ?></span></div>
  <div class="country"><?php echo customer::$data['country_code']; ?></div>
  <div class="change"><a href="<?php echo document::href_ilink('regional_settings'); ?>" data-toggle="lightbox"><?php echo language::translate('title_change', 'Change'); ?></a></div>
</section>
