<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['slides'])) throw new Exception(language::translate('error_must_select_slides', 'You must select slides'));

      foreach ($_POST['slides'] as $slide_id) {
        $slide = new ctrl_slide($slide_id);
        $slide->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $slide->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_slide'), true), language::translate('title_add_new_slide', 'Add New Slide'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_slides', 'Slides'); ?></h1>

<?php echo functions::form_draw_form_begin('slides_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_languages', 'Languages'); ?></th>
        <th class="text-center"><?php echo language::translate('title_valid_from', 'Valid From'); ?></th>
        <th class="text-center"><?php echo language::translate('title_valid_to', 'Valid To'); ?></th>
        <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $slides_query = database::query(
    "select * from ". DB_TABLE_SLIDES ."
    order by priority, name;"
  );

  if (database::num_rows($slides_query) > 0) {

    if ($_GET['page'] > 1) database::seek($slides_query, (settings::get('data_table_rows_per_page') * ($_GET['page']-1)));

    $page_items = 0;
    while ($slide = database::fetch($slides_query)) {
?>
      <tr class="<?php echo empty($slide['status']) ? 'semi-transparent' : null; ?>">
        <td><?php echo functions::form_draw_checkbox('slides['. $slide['id'] .']', $slide['id']); ?></td>
        <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($slide['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
        <td><?php echo $slide['id']; ?></td>
        <td><a href="<?php echo document::href_link('', array('doc' => 'edit_slide', 'slide_id' => $slide['id']), true); ?>"><?php echo $slide['name']; ?></a></td>
        <td class="text-right"><?php echo !empty($slide['languages']) ? str_replace(',', ', ', $slide['languages']) : language::translate('title_all', 'All'); ?></td>
        <td class="text-right"><?php echo (date('Y', strtotime($slide['date_valid_from'])) > '1970') ? language::strftime(language::$selected['format_datetime'], strtotime($slide['date_valid_from'])) : '-'; ?></td>
        <td class="text-right"><?php echo (date('Y', strtotime($slide['date_valid_to'])) > '1970') ? language::strftime(language::$selected['format_datetime'], strtotime($slide['date_valid_to'])) : '-'; ?></td>
        <td class="text-right"><?php echo $slide['priority']; ?></td>
        <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_slide', 'slide_id' => $slide['id']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
      </tr>
<?php
      if (++$page_items == settings::get('data_table_rows_per_page')) break;
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="9"><?php echo language::translate('title_slides', 'Slides'); ?>: <?php echo database::num_rows($slides_query); ?></td>
      </tr>
    </tfoot>
  </table>

  <p class="btn-group">
    <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
    <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<?php echo functions::draw_pagination(ceil(database::num_rows($slides_query)/settings::get('data_table_rows_per_page'))); ?>