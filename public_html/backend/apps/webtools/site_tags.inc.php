<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
    $_GET['page'] = 1;
  }

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {

      if (empty($_POST['site_tags'])) {
        throw new Exception(language::translate('error_must_select_site_tags', 'You must select site_tags'));
      }

      foreach ($_POST['site_tags'] as $site_tag_id) {
        $site_tag = new ent_site_tag($site_tag_id);
        $site_tag->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $site_tag->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $site_tags = database::query(
    "select * from ". DB_TABLE_PREFIX ."site_tags
    order by status desc, position asc, priority asc, description asc;"
  )->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_site_tags', 'Site Tags'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_site_tag'), language::translate('title_create_new_site_tag', 'Create New Site Tag'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_begin('site_tags_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('icon-square-check fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_description', 'Description'); ?></th>
          <th><?php echo language::translate('title_cookie', 'Cookie'); ?></th>
          <th><?php echo language::translate('title_position', 'Position'); ?></th>
          <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($site_tags as $site_tag) { ?>
        <tr class="<?php echo empty($site_tag['status']) ? 'semi-transparent' : null; ?>">
          <td><?php echo functions::form_checkbox('site_tags[]', $site_tag['id']); ?></td>
          <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($site_tag['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
          <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_site_tag', ['site_tag_id' => $site_tag['id']]); ?>"><?php echo $site_tag['description']; ?></a></td>
          <td class="text-center"><?php echo $site_tag['require_consent'] ? functions::draw_fonticon('icon-check') : ''; ?></td>
          <td class="text-center"><?php echo $site_tag['position']; ?></td>
          <td class="text-center"><?php echo (int)$site_tag['priority']; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_site_tag', ['site_tag_id' => $site_tag['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php }?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="7"><?php echo language::translate('title_site_tags', 'Site Tags'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions" disabled>
        <legend><?php echo language::translate('text_with_selected', 'With selected'); ?></legend>

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
  $('.data-table input[name^="site_tags["]').change(function() {
    if ($('.data-table input[name^="site_tags["]:checked').length > 0) {
      $('fieldset').prop('disabled', false);
    } else {
      $('fieldset').prop('disabled', true);
    }
  }).trigger('change');
</script>
