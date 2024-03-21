<div id="site-top-navigation" class="hidden-xs">
  <div class="wrapper">
    <div style="display: flex; flex-direction: row; justify-content: space-between">

      <?php if ($important_notice = settings::get('important_notice')) { ?>
      <div id="important-message">
        <?php echo $important_notice; ?>
      </div>
      <?php } ?>

      <nav class="nav">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
          <span class="code"><?php echo language::$selected['code']; ?></span> <?php echo language::$selected['name']; ?>
        </a>

        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
          <span class="code"><?php echo currency::$selected['code']; ?></span> <?php echo currency::$selected['name']; ?>
        </a>

        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
          <span class="code"><?php echo customer::$data['country_code']; ?></span> <?php echo reference::country(customer::$data['country_code'])->name; ?>
        </a>
      </nav>
    </div>
  </div>
</div>