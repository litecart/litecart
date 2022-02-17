<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_slides', 'Slides');

  breadcrumbs::add(language::translate('title_slides', 'Slides'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['slides'])) throw new Exception(language::translate('error_must_select_slides', 'You must select slides'));

      foreach ($_POST['slides'] as $slide_id) {
        $slide = new ent_slide($slide_id);
        $slide->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $slide->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $slides = [];

  $slides_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."slides
    order by priority, name;"
  );

  if ($_GET['page'] > 1) database::seek($slides_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($slide = database::fetch($slides_query)) {
    $slides[] = $slide;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($slides_query);

// Pagination
  $num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_slides', 'Slides'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_slide'), language::translate('title_create_new_slide', 'Create New Slide'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('slides_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th></th>
          <th><?php echo language::translate('title_id', 'ID'); ?></th>
          <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
          <th><?php echo language::translate('title_languages', 'Languages'); ?></th>
          <th class="text-center"><?php echo language::translate('title_valid_from', 'Valid From'); ?></th>
          <th class="text-center"><?php echo language::translate('title_valid_to', 'Valid To'); ?></th>
          <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($slides as $slide) { ?>
        <tr class="<?php echo empty($slide['status']) ? 'semi-transparent' : ''; ?>">
          <td><?php echo functions::form_draw_checkbox('slides[]', $slide['id']); ?></td>
          <td><?php echo functions::draw_fonticon($slide['status'] ? 'on' : 'off'); ?></td>
          <td><?php echo $slide['id']; ?></td>
          <td><a href="<?php echo document::href_ilink(__APP__.'/edit_slide', ['slide_id' => $slide['id']]); ?>"><?php echo $slide['name']; ?></a></td>
          <td class="text-end"><?php echo !empty($slide['languages']) ? str_replace(',', ', ', $slide['languages']) : language::translate('title_all', 'All'); ?></td>
          <td class="text-end"><?php echo (date('Y', strtotime($slide['date_valid_from'])) > '1970') ? language::strftime(language::$selected['format_datetime'], strtotime($slide['date_valid_from'])) : '-'; ?></td>
          <td class="text-end"><?php echo (date('Y', strtotime($slide['date_valid_to'])) > '1970') ? language::strftime(language::$selected['format_datetime'], strtotime($slide['date_valid_to'])) : '-'; ?></td>
          <td class="text-end"><?php echo $slide['priority']; ?></td>
          <td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_slide', ['slide_id' => $slide['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="9"><?php echo language::translate('title_slides', 'Slides'); ?>: <?php echo $num_rows; ?></td>
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
  $('.data-table input[type="checkbox"]').change(function() {
    $('#actions').prop('disabled', !$('.data-table [type="checkbox"]:checked').length);
  }).first().trigger('change');
</script>