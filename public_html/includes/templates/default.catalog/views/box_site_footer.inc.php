<footer id="footer" class="hidden-print">

  <div class="fourteen-forty container">

    <div class="columns">
      <section class="categories hidden-xs">
        <h3 class="title"><?php echo language::translate('title_categories', 'Categories'); ?></h3>
        <ul class="list-unstyled">
          <?php foreach ($categories as $category) echo '<li><a href="'. functions::escape_html($category['link']) .'">'. $category['name'] .'</a></li>' . PHP_EOL; ?>
        </ul>
      </section>

      <?php if ($manufacturers) { ?>
      <section class="manufacturers hidden-xs hidden-sm">
        <h3 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h3>
        <ul class="list-unstyled">
        <?php foreach ($manufacturers as $manufacturer) echo '<li><a href="'. functions::escape_html($manufacturer['link']) .'">'. $manufacturer['name'] .'</a></li>' . PHP_EOL; ?>
        </ul>
      </section>
      <?php } ?>

      <?php if (settings::get('accounts_enabled')) { ?>
      <section class="account">
        <h3 class="title"><?php echo language::translate('title_account', 'Account'); ?></h3>
        <ul class="list-unstyled">
          <li><a href="<?php echo document::href_ilink('customer_service'); ?>"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></a></li>
          <li><a href="<?php echo document::href_ilink('regional_settings'); ?>"><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></a></li>
          <?php if (empty(customer::$data['id'])) { ?>
          <li><a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('title_create_account', 'Create Account'); ?></a></li>
          <li><a href="<?php echo document::href_ilink('login'); ?>"><?php echo language::translate('title_login', 'Login'); ?></a></li>
          <?php } else { ?>
          <li><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
          <li><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
          <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
          <?php } ?>
        </ul>
      </section>
      <?php } ?>

      <section class="information">
        <h3 class="title"><?php echo language::translate('title_information', 'Information'); ?></h3>
        <ul class="list-unstyled">
          <?php foreach ($pages as $page) echo '<li><a href="'. functions::escape_html($page['link']) .'">'. $page['title'] .'</a></li>' . PHP_EOL; ?>
        </ul>
      </section>

      <section class="contact hidden-xs">
        <h3 class="title"><?php echo language::translate('title_contact', 'Contact'); ?></h3>

        <ul class="list-unstyled">
          <li><?php echo nl2br(settings::get('store_postal_address')); ?></li>

          <?php if (settings::get('store_phone')) { ?>
          <li><?php echo functions::draw_fonticon('fa-phone'); ?> <a href="tel:<?php echo settings::get('store_phone'); ?>"><?php echo settings::get('store_phone'); ?></a></li>
          <?php } ?>

          <li><?php echo functions::draw_fonticon('fa-envelope'); ?> <a href="mailto:<?php echo settings::get('store_email'); ?>"><?php echo settings::get('store_email'); ?></a></li>
        </ul>
      </section>
    </div>
  </div>
</footer>

<section id="copyright">
  <div class="fourteen-forty container notice">
    <!-- LiteCart is provided free under license CC BY-ND 4.0 - https://creativecommons.org/licenses/by-nd/4.0/. Removing the link back to litecart.net without permission is a violation - https://www.litecart.net/addons/172/removal-of-attribution-link -->
    Copyright &copy; <?php echo date('Y'); ?> <?php echo settings::get('store_name'); ?>. All rights reserved &middot; Powered by <a href="https://www.litecart.net" target="_blank" title="Free e-commerce platform">LiteCart®</a>
  </div>
</section>