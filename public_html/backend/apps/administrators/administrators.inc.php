<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  document::$title[] = language::translate('title_administrators', 'Administrators');

  breadcrumbs::add(language::translate('title_administrators', 'Administrators'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {

      if (empty($_POST['administrators'])) {
        throw new Exception(language::translate('error_must_select_administrators', 'You must select administrators'));
      }

      foreach ($_POST['administrators'] as $administrator_id) {

        $administrator = new ent_administrator($administrator_id);
        $administrator->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $administrator->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows, Total Number of Rows, Total Number of Pages
  $administrators = database::query(
    "select * from ". DB_TABLE_PREFIX ."administrators
    order by username;"
  )->fetch_page($_GET['page'], null, $num_rows, $num_pages);

?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_administrators', 'Administrators'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_button_link(document::ilink(__APP__.'/edit_administrator'), language::translate('title_create_new_administrator', 'Create New Administrator'), '', 'add'); ?>
  </div>

  <?php echo functions::form_begin('administrators_form', 'post'); ?>

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
        <?php foreach ($administrators as $administrator) { ?>
        <tr class="<?php if (empty($administrator['status'])) echo 'semi-transparent'; ?>">
          <td><?php echo functions::form_input_checkbox('administrators[]', $administrator['id']); ?></td>
          <td><?php echo functions::draw_fonticon($administrator['status'] ? 'on' : 'off'); ?></td>
          <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_administrator', ['administrator_id' => $administrator['id']]); ?>"><?php echo $administrator['username']; ?></a></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_administrator', ['administrator_id' => $administrator['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="4"><?php echo language::translate('title_administrators', 'Administrators'); ?>: <?php echo language::number_format($num_rows); ?></td>
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