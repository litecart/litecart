<?php

  if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {

      if (empty($_POST['third_parties'])) {
        throw new Exception(language::translate('error_must_select_third_parties', 'You must select third_parties'));
      }

      foreach ($_POST['third_parties'] as $third_party_id) {
        $third_party = new ent_third_party($third_party_id);
        $third_party->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $third_party->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $third_parties = database::query(
    "select * from ". DB_TABLE_PREFIX ."third_parties
    order by name asc;"
  )->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

  $privacy_classes = [
    'necessary' => language::translate('title_necessary', 'Necessary'),
    'functionality' => language::translate('title_functionality', 'Functionality'),
    'personalization' => language::translate('title_personalization', 'Personalization'),
    'measurement' => language::translate('title_measurement', 'Measurement'),
    'marketing' => language::translate('title_marketing', 'Marketing'),
    'security' => language::translate('title_security', 'Security'),
  ];
?>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_third_parties', 'Third Parties'); ?>
    </div>
  </div>

  <div class="card-action">
    <ul class="list-inline">
      <li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_third_party'), language::translate('title_create_new_third_party', 'Create New Third Party'), '', 'add'); ?></li>
    </ul>
  </div>

  <?php echo functions::form_begin('third_parties_form', 'post'); ?>

    <table class="data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_privacy_classes', 'Privacy Classes'); ?></th>
          <th><?php echo language::translate('title_country', 'Country'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($third_parties as $third_party) { ?>
        <tr>
          <td><?php echo functions::form_checkbox('third_parties[]', $third_party['id']); ?></td>
          <td><?php echo functions::draw_fonticon(!empty($third_party['status']) ? 'on' : 'off'); ?></td>
          <td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_third_party', ['third_party_id' => $third_party['id']]); ?>"><?php echo $third_party['name']; ?></a></td>
          <td><?php echo implode(', ', array_map(function($class) use ($privacy_classes){ return $privacy_classes[$class]; }, preg_split('#\s*,\s*#', $third_party['privacy_classes'], -1, PREG_SPLIT_NO_EMPTY))); ?></td>
          <td class="text-center"><?php echo $third_party['country_code']; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_third_party', ['third_party_id' => $third_party['id']], true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php }?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="7"><?php echo language::translate('title_third_parties', 'Third Parties'); ?>: <?php echo $num_rows; ?></td>
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
  $('.data-table :checkbox').change(function() {
    $('#actions').prop('disabled', !$('.data-table :checked').length);
  }).first().trigger('change');
</script>