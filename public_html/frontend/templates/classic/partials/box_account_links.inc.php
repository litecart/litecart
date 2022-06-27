
<section id="box-account" class="box">

  <h2 class="title"><?php echo language::translate('title_account', 'Account'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <?php if (!empty(customer::$data['id'])) { ?>
    <li<?php echo (route::$selected['route'] == 'f:order_history') ? ' class="active"' : ''; ?>><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
    <li<?php echo (route::$selected['route'] == 'f:edit_account') ? ' class="active"' : ''; ?>><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
    <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_sign_out', 'Sign Out'); ?></a></li>
    <?php } else { ?>
    <li><a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('title_create_account', 'Create Account'); ?></a></li>
    <li><a href="<?php echo document::href_ilink('login'); ?>"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a></li>
    <li><a href="<?php echo document::href_ilink('reset_password'); ?>"><?php echo language::translate('title_reset_password', 'Reset Password'); ?></a></li>
    <?php } ?>
  </ul>

</section>