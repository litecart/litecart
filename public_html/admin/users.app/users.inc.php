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
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $users = [];

  $users_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."users
    order by username;"
  );

  if ($_GET['page'] > 1) database::seek($users_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($user = database::fetch($users_query)) {

    try {
      $user['warning'] = null;

      if ($user['date_valid_from'] > date('Y-m-d H:i:s')) {
        throw new Exception(strtr(language::translate('text_acount_cannot_be_used_until_x', 'The account cannot be used until %datetime'), ['%datetime' => language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_from']))]));
      }

      if ($user['date_valid_to'] > 1970 && $user['date_valid_to'] < date('Y-m-d H:i:s')) {
        throw new Exception(strtr(language::translate('text_account_expired_at_x', 'The account expired at %datetime and can no longer be used'), ['%datetime' => language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_to']))]));
      }

    } catch (Exception $e) {
      $user['warning'] = $e->getMessage();
    }

    $users[] = $user;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($users_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<style>
.warning {
  color: #f00;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_users', 'Users'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, ['doc' => 'edit_user'], true), language::translate('title_create_new_user', 'Create New User'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_draw_form_begin('users_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th></th>
          <th style="min-width: 200px;"><?php echo language::translate('title_username', 'Username'); ?></th>
          <th class="main"></th>
          <th style="min-width: 200px;"><?php echo language::translate('title_valid_from', 'Valid From'); ?></th>
          <th style="min-width: 200px;"><?php echo language::translate('title_valid_to', 'Valid To'); ?></th>
          <th style="min-width: 200px;"><?php echo language::translate('title_access', 'Access'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($users as $user) { ?>
        <tr class="<?php echo empty($user['status']) ? 'semi-transparent' : null; ?>">
          <td><?php echo functions::form_draw_checkbox('users[]', $user['id']); ?></td>
          <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($user['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
          <td class="warning"><?php echo !empty($user['warning']) ? functions::draw_fonticon('fa-exclamation-triangle', 'title="'. functions::escape_html($user['warning']) .'"') : ''; ?></td>
          <td><a class="link" href="<?php echo document::href_link('', ['doc' => 'edit_user', 'user_id' => $user['id']], true); ?>"><?php echo $user['username']; ?></a></td>
          <td><?php echo $user['email']; ?></td>
          <td><?php echo ($user['date_valid_from'] > 1970) ? language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_from'])) : '-'; ?></td>
          <td><?php echo ($user['date_valid_to'] > 1970) ? language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_to'])) : '-'; ?></td>
          <td><?php echo (json_decode($user['apps'], true)) ? language::translate('title_restricted', 'Restricted') : language::translate('title_full_access', 'Full Access'); ?></td>
          <td><a class="btn btn-default btn-sm" href="<?php echo document::href_link('', ['doc' => 'edit_user', 'user_id' => $user['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php }?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="9"><?php echo language::translate('title_users', 'Users'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions" disabled>
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

        <div class="btn-group">
          <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
          <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
        </div>
      </fieldset>
    </div>

  <?php echo functions::form_draw_form_end(); ?>

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