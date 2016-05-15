  <footer id="footer" class="twelve-eighty">

    <div id="breadcrumbs-wrapper">
      <!--snippet:breadcrumbs-->
    </div>

    <table>
      <tr>
        <td class="categories">
          <nav>
            <h4><?php echo language::translate('title_categories', 'Categories'); ?></h4>
            <ul class="list-vertical">
              <?php foreach ($categories as $category) echo '<li><a href="'. htmlspecialchars($category['link']) .'">'. $category['name'] .'</a></li>' . PHP_EOL; ?>
            </ul>
          </nav>
        </td>

        <?php if ($manufacturers) { ?>
        <td class="manufacturers">
          <nav>
            <h4><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h4>
            <ul class="list-vertical">
            <?php foreach ($manufacturers as $manufacturer) echo '<li><a href="'. htmlspecialchars($manufacturer['link']) .'">'. $manufacturer['name'] .'</a></li>' . PHP_EOL; ?>
            </ul>
          </nav>
        </td>
        <?php } ?>

        <td class="account">
          <nav>
            <h4><?php echo language::translate('title_account', 'Account'); ?></h4>
            <ul class="list-vertical">
              <li><a href="<?php echo document::ilink('customer_service'); ?>"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></a></li>
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
          </nav>
        </td>
        <td class="information">
          <nav>
            <h4><?php echo language::translate('title_information', 'Information'); ?></h4>
            <ul class="list-vertical">
              <?php foreach ($pages as $page) echo '<li><a href="'. htmlspecialchars($page['link']) .'">'. $page['title'] .'</a></li>' . PHP_EOL; ?>
            </ul>
          </nav>
        </td>
        <td class="contact">
          <h4><?php echo language::translate('title_contact', 'Contact'); ?></h4>
          <p><?php echo nl2br(settings::get('store_postal_address')); ?></p><br />
          <p><?php echo settings::get('store_phone'); ?><br />
            <?php list($account, $domain) = explode('@', settings::get('store_email')); echo "<script>document.write('<a href=\"mailto:". $account ."' + '@' + '". $domain ."\">". $account ."' + '@' + '". $domain ."</a>');</script>"; ?></p>
        </td>
      </tr>
    </table>
  </footer>

  <div id="copyright" class="twelve-eighty">
    <!-- LiteCart is provided free by End-User License Agreement (EULA). Removing the link back to LiteCart.net without written permission is a violation. -->
    <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo settings::get('store_name'); ?>. All rights reserved &middot; Powered by <a href="http://www.litecart.net" target="_blank" title="Free e-commerce platform">LiteCart®</a></p>
  </div>