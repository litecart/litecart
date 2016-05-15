<?php
  if (!isset($_GET['page'])) $_GET['page'] = 1;

  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['users'])) {

      foreach ($_POST['users'] as $user_id) {

        $user = new ctrl_user($user_id);
        $user->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $user->save();
      }

      header('Location: '. document::link());
      exit;
    }
  }
?>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_user'), true), language::translate('title_create_new_user', 'Create New User'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_users', 'Users'); ?></h1>

<?php echo functions::form_draw_form_begin('users_form', 'post'); ?>

  <table width="100%" align="center" class="dataTable">
    <tr class="header">
      <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
      <th></th>
      <th style="width: 100%;"><?php echo language::translate('title_username', 'Username'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  $users_query = database::query(
    "select * from ". DB_TABLE_USERS ."
    order by username;"
  );

  if (database::num_rows($users_query) > 0) {

    if ($_GET['page'] > 1) database::seek($users_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($user = database::fetch($users_query)) {
?>
    <tr class="row<?php echo !$user['status'] ? ' semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('users['. $user['id'] .']', $user['id']); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($user['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_user', 'user_id' => $user['id']), true); ?>"><?php echo $user['username']; ?></a></td>
      <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_user', 'user_id' => $user['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    <tr class="footer">
      <td colspan="4"><?php echo language::translate('title_users', 'Users'); ?>: <?php echo database::num_rows($users_query); ?></td>
    </tr>
  </table>

  <script>
    $(".dataTable .checkbox-toggle").click(function() {
      $(this).closest("form").find(":checkbox").each(function() {
        $(this).attr('checked', !$(this).attr('checked'));
      });
      $(".dataTable .checkbox-toggle").attr("checked", true);
    });

    $('.dataTable tr').click(function(event) {
      if ($(event.target).is('input:checkbox')) return;
      if ($(event.target).is('a, a *')) return;
      if ($(event.target).is('th')) return;
      $(this).find('input:checkbox').trigger('click');
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span></p>

<?php
  echo functions::form_draw_form_end();

// Display page links
  echo functions::draw_pagination(ceil(database::num_rows($users_query)/settings::get('data_table_rows_per_page')));

?>