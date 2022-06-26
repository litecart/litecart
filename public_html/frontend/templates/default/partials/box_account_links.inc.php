<section id="box-account">

  <h2 class="title"><?php echo language::translate('title_account', 'Account'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <li<?php echo (route::$selected['route'] == 'f:order_history') ? ' class="active"' : ''; ?>><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
    <li<?php echo (route::$selected['route'] == 'f:edit_account') ? ' class="active"' : ''; ?>><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
    <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
  </ul>

</section>