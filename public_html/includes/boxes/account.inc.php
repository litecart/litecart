<?php if (empty(customer::$data['id'])) return; ?>
<div class="box">
  <div class="heading"><h3><?php echo language::translate('title_account', 'Account'); ?></h3></div>
  <div class="content">
    <div><?php echo sprintf(language::translate('text_logged_in_as_s', 'Logged in as %s'), customer::$data['firstname'] .' '. customer::$data['lastname']); ?></div>
    <ul>
      <li><a href="<?php echo document::href_link('order_history.php'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
      <li><a href="<?php echo document::href_link('edit_account.php'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
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