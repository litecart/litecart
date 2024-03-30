
<section id="box-account" class="box">

  <h2 class="title"><?php echo language::translate('title_account', 'Account'); ?></h2>

  <nav class="nav nav-stacked nav-pills">
    <?php if (!empty(customer::$data['id'])) { ?>
    <a class="nav-link<?php if (route::$selected['route'] == 'f:edit_account') echo ' active'; ?>" href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a>
    <a class="nav-link<?php if (route::$selected['route'] == 'f:addresses') echo ' active'; ?>" href="<?php echo document::href_ilink('addresses'); ?>"><?php echo language::translate('title_addresses', 'Addresses'); ?></a>
    <a class="nav-link<?php if (route::$selected['route'] == 'f:order_history') echo ' active'; ?>" href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a>
    <a class="nav-link" href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_sign_out', 'Sign Out'); ?></a>
    <?php } else { ?>
    <a class="nav-link" href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('title_create_account', 'Create Account'); ?></a>
    <a class="nav-link" href="<?php echo document::href_ilink('login'); ?>"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a>
    <a class="nav-link" href="<?php echo document::href_ilink('reset_password'); ?>"><?php echo language::translate('title_reset_password', 'Reset Password'); ?></a>
    <?php } ?>
  </nav>

</section>