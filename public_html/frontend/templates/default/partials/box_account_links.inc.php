<section id="box-account">

	<h2 class="title"><?php echo language::translate('title_account', 'Account'); ?></h2>

	<nav class="nav nav-stacked nav-pills">
		<?php if (!empty(customer::$data['id'])) { ?>
		<a class="nav-item<?php if (route::$selected['route'] == 'f:edit_account') echo ' active'; ?>" href="<?php echo document::href_ilink('account/edit'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a>
		<a class="nav-item<?php if (route::$selected['route'] == 'f:order_history') echo ' active'; ?>" href="<?php echo document::href_ilink('account/order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a>
		<a class="nav-item" href="<?php echo document::href_ilink('account/sign_out'); ?>"><?php echo language::translate('title_sign_out', 'Sign Out'); ?></a>
		<?php } else { ?>
		<a class="nav-item" href="<?php echo document::href_ilink('account/sign_in'); ?>"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a>
		<a class="nav-item" href="<?php echo document::href_ilink('account/sign_up'); ?>"><?php echo language::translate('title_sign_up', 'Sign Up'); ?></a>
		<a class="nav-item" href="<?php echo document::href_ilink('account/reset_password'); ?>"><?php echo language::translate('title_reset_password', 'Reset Password'); ?></a>
		<?php } ?>
	</nav>

</section>