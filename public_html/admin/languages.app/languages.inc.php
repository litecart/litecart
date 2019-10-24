<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['languages'])) throw new Exception(language::translate('error_must_select_languages', 'You must select languages'));

      foreach (array_keys($_POST['languages']) as $language_code) {

        if (!empty($_POST['disable']) && $language_code == settings::get('default_language_code')) {
          throw new Exception(language::translate('error_cannot_disable_default_language', 'You cannot disable the default language'));
        }

        if (!empty($_POST['disable']) && $language_code == settings::get('store_language_code')) {
          throw new Exception(language::translate('error_cannot_disable_store_language', 'You cannot disable the store language'));
        }

        $language = new ent_language($_POST['languages'][$language_code]);
        $language->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $language->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $languages = array();

  $languages_query = database::query(
    "select * from ". DB_TABLE_LANGUAGES ."
    order by field(status, 1, -1, 0), priority, name;"
  );

  if ($_GET['page'] > 1) database::seek($languages_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

  $page_items = 0;
  while ($language = database::fetch($languages_query)) {
    switch ($language['status']) {
      case '1': $language['status_color'] = '#88cc44'; break;
      case '-1': $language['status_color'] = '#ded90f'; break;
      case '0': $language['status_color'] = '#ff6644'; break;
    }

    $languages[] = $language;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($languages_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo language::translate('title_languages', 'Languages'); ?>
  </div>

  <div class="panel-action">
    <ul class="list-inline">
      <li><?php echo functions::form_draw_link_button(document::link(WS_DIR_ADMIN, array('doc' => 'edit_language'), true), language::translate('title_add_new_language', 'Add New Language'), '', 'add'); ?></li>
    </ul>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('languages_form', 'post'); ?>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
            <th></th>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th><?php echo language::translate('title_code', 'Code'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_default_language', 'Default Language'); ?></th>
            <th><?php echo language::translate('title_store_language', 'Store Language'); ?></th>
            <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>

        <tbody>
        <?php foreach ($languages as $language) { ?>
          <tr class="<?php echo empty($language['status']) ? 'semi-transparent' : null; ?>">
            <td><?php echo functions::form_draw_checkbox('languages['. $language['code'] .']', $language['code']); ?></td>
            <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. $language['status_color'] .';"'); ?></td>
            <td><?php echo $language['id']; ?></td>
            <td><?php echo $language['code']; ?></td>
            <td><a href="<?php echo document::href_link('', array('doc' => 'edit_language', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>"><?php echo $language['name']; ?></a></td>
            <td class="text-center"><?php echo ($language['code'] == settings::get('default_language_code')) ? functions::draw_fonticon('fa-check') : ''; ?></td>
            <td class="text-center"><?php echo ($language['code'] == settings::get('store_language_code')) ? functions::draw_fonticon('fa-check') : ''; ?></td>
            <td class="text-center"><?php echo $language['priority']; ?></td>
            <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_language', 'language_code' => $language['code'], 'page' => $_GET['page']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="9"><?php echo language::translate('title_languages', 'Languages'); ?>: <?php echo $num_rows; ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="btn-group">
        <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
        <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
</div>
