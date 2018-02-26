<div id="box-account" class="box">

  <h2 class="title"><?php echo language::translate('title_account', 'Account'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <li><a href="<?php echo document::href_ilink('order_history'); ?>"<?php echo (route::$route['page'] == 'order_history') ? ' class="active"' : ''; ?>><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
    <li><a href="<?php echo document::href_ilink('edit_account'); ?>"<?php echo (route::$route['page'] == 'edit_account') ? ' class="active"' : ''; ?>><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
    <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
  </ul>

</div>