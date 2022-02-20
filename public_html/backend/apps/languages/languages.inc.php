<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_languages', 'Languages');

  breadcrumbs::add(language::translate('title_languages', 'Languages'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['languages'])) throw new Exception(language::translate('error_must_select_languages', 'You must select languages'));

      foreach (array_keys($_POST['languages']) as $language_code) {

        if (!empty($_POST['disable']) && $language_code == settings::get('default_language_code')) {
          throw new Exception(language::translate('error_cannot_disable_default_language', 'You cannot disable the default language'));
        }

        if (!empty($_POST['disable']) && $language_code == settings::get('site_language_code')) {
          throw new Exception(language::translate('error_cannot_disable_store_language', 'You cannot disable the store language'));
        }

        $language = new ent_language($_POST['languages'][$language_code]);
        $language->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $language->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $languages = [];

  $languages_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."languages
    order by field(status, 1, -1, 0), priority, name;"
  );

  if ($_GET['page'] > 1) database::seek($languages_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($language = database::fetch($languages_query)) {
    $languages[] = $language;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($languages_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_languages', 'Languages'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_language'), language::translate('title_create_new_language', 'Create New Language'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('languages_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th>ISO 639-1</th>
          <th>ISO 639-2</th>
          <th><?php echo language::translate('title_url_type', 'URL Type'); ?></th>
          <th><?php echo language::translate('title_default_language', 'Default Language'); ?></th>
          <th><?php echo language::translate('title_store_language', 'Store Language'); ?></th>
          <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
      <?php foreach ($languages as $language) { ?>
        <tr class="<?php echo empty($language['status']) ? 'semi-transparent' : ''; ?>">
          <td><?php echo functions::form_draw_checkbox('languages[]', $language['code']); ?></td>
          <td><?php echo functions::draw_fonticon(($language['status'] == 1) ? 'on' : (($language['status'] == -1) ? 'semi-off' : 'off')); ?></td>
          <td><?php echo $language['id']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_language', ['language_code' => $language['code']]); ?>"><?php echo $language['name']; ?></a></td>
          <td><?php echo $language['code']; ?></td>
          <td><?php echo $language['code2']; ?></td>
          <td><?php echo $language['url_type']; ?></td>
          <td class="text-center"><?php echo ($language['code'] == settings::get('default_language_code')) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo ($language['code'] == settings::get('site_language_code')) ? functions::draw_fonticon('fa-check') : ''; ?></td>
          <td class="text-center"><?php echo $language['priority']; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_language', ['language_code' => $language['code']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="11"><?php echo language::translate('title_languages', 'Languages'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <fieldset id="actions">
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