<div id="site-top-navigation" class="hidden-xs">
  <div class="wrapper">
    <ul class="nav">
      <li>
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
          <span class="code"><?php echo language::$selected['code']; ?></span> <?php echo language::$selected['name']; ?>
        </a>
      </li>
      <li>
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
          <span class="code"><?php echo currency::$selected['code']; ?></span> <?php echo currency::$selected['name']; ?>
        </a>
      </li>
      <li>
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
          <span class="code"><?php echo customer::$data['country_code']; ?></span> <?php echo reference::country(customer::$data['country_code'])->name; ?>
        </a>
      </li>
    </ul>
  </div>
</div>