<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_users', 'Users');

  breadcrumbs::add(language::translate('title_users', 'Users'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['users'])) throw new Exception(language::translate('error_must_select_users', 'You must select users'));

      foreach ($_POST['users'] as $user_id) {

        $user = new ent_user($user_id);
        $user->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $user->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows, Total Number of Rows, Total Number of Pages
  $users = database::query(
    "select * from ". DB_TABLE_PREFIX ."users
    order by username;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_users', 'Users'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_link_button(document::ilink(__APP__.'/edit_user'), language::translate('title_create_new_user', 'Create New User'), '', 'add'); ?>
  </div>

  <?php echo functions::form_begin('users_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_username', 'Username'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($users as $user) { ?>
        <tr class="<?php echo empty($user['status']) ? 'semi-transparent' : ''; ?>">
          <td><?php echo functions::form_checkbox('users[]', $user['id']); ?></td>
          <td><?php echo functions::draw_fonticon($user['status'] ? 'on' : 'off'); ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_user', ['user_id' => $user['id']]); ?>"><?php echo $user['username']; ?></a></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_user', ['user_id' => $user['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php }?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="4"><?php echo language::translate('title_users', 'Users'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions">
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <div class="btn-group">
          <?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
          <?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
        </div>
      </fieldset>
    </div>

  <?php echo functions::form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>
</div>

<script>
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>