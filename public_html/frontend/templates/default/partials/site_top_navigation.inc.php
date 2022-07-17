<div id="site-top-navigation">
  <div class="wrapper">
    <ul class="nav">
      <li class="dropdown">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>">
          <span class="code"><?php echo language::$selected['code']; ?></span> <?php echo language::$selected['name']; ?>
        </a>
      </li>
      <li class="dropdown">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>">
          <span class="code"><?php echo currency::$selected['code']; ?></span> <?php echo currency::$selected['name']; ?>
        </a>
      </li>
      <li class="dropdown">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>" data-toggle="dropdown">
          <span class="code"><?php echo customer::$data['country_code']; ?></span> <?php echo reference::country(customer::$data['country_code'])->name; ?>
        </a>
      </li>
    </ul>
  </div>
</div>