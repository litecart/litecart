<div id="box-account" class="box">
  <div class="heading"><h3><?php echo language::translate('title_account', 'Account'); ?></h3></div>
  <div class="content">
    <ul class="list-vertical">
      <li><a href="<?php echo document::href_ilink('customer_service'); ?>"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></a></li>
      <li><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
      <li><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
      <li><a href="javascript:logout();"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
    </ul>
    <script>
      function logout() {
        var form = $('<?php
          echo str_replace(array("\r", "\n"), '', functions::form_draw_form_begin('logout_form', 'post')
                                                . functions::form_draw_hidden_field('logout', 'true')
                                                . functions::form_draw_form_end()
          );
        ?>');
        $(document.body).append(form);
        form.submit();
      }
    </script>
  </div>
</div>