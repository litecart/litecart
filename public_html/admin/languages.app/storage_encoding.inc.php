<?php
  breadcrumbs::add(language::translate('title_storage_encoding', 'Storage Encoding'));

  $defined_tables = array();
  foreach (get_defined_constants() as $constant) {
    if (preg_match('#^`'. preg_quote(DB_DATABASE, '#') .'`\.`(.*)`$#', $constant, $matches)) {
      $defined_tables[] = $matches[1];
    }
  }

  if (isset($_POST['convert'])) {

    try {
      if (empty($_POST['tables'])) throw new Exception(language::translate('error_must_select_tables', 'You must select tables'));
      if (empty($_POST['collation']) && empty($_POST['engine'])) throw new Exception(language::translate('error_must_select_action_to_perform', 'You must select an action to perform'));

      $_POST['collation'] = preg_replace('#[^a-z0-9_]#', '', $_POST['collation']);

      if (!empty($_POST['set_database_default'])) {
        database::query(
          "alter database `". DB_DATABASE ."`
          default character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ."
          collate = ". database::input($_POST['collation']) .";"
        );
      }

      if (!empty($_POST['collation'])) {
        foreach ($_POST['tables'] as $table) {
          database::query(
            "alter table `". DB_DATABASE ."`.`". $table ."`
            convert to character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ."
            collate ". database::input($_POST['collation']) .";"
          );
        }
      }

      if (!empty($_POST['engine'])) {
        foreach ($_POST['tables'] as $table) {
          database::query(
            "alter table `". DB_DATABASE ."`.`". $table ."`
            engine=". database::input($_POST['engine']) .";"
          );
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (empty($_POST)) $_POST['tables'] = $defined_tables;
?>
<h1><?php echo $app_icon; ?> <?php echo language::translate('title_storage_encoding', 'Storage Encoding'); ?></h1>

<p><?php echo language::translate('description_set_mysql_collation', 'This will recursively convert the charset and collation for all selected database tables and belonging columns.'); ?></p>

<?php echo functions::form_draw_form_begin('mysql_collation_form', 'post', false, false, 'style="width: 640px;"'); ?>

  <h2><?php echo language::translate('title_database_tables', 'Database Tables'); ?></h2>

  <table class="table table-striped table-hover data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th class="main"><?php echo language::translate('title_table', 'Table'); ?></th>
        <th><?php echo language::translate('title_collation', 'Collation'); ?></th>
        <th><?php echo language::translate('title_engine', 'Engine'); ?></th>
      </tr>
    </thead>
    <tbody>
<?php
  $tables_query = database::query(
    "select * from `information_schema`.`TABLES`
    where TABLE_SCHEMA = '". DB_DATABASE ."';"
  );

  while ($table = database::fetch($tables_query)) {
    if (in_array($table['TABLE_NAME'], $defined_tables)) {
?>
      <tr>
        <td><?php echo functions::form_draw_checkbox('tables[]', $table['TABLE_NAME'], true); ?></td>
        <td><?php echo $table['TABLE_NAME']; ?></td>
        <td><?php echo $table['TABLE_COLLATION']; ?></td>
        <td><?php echo $table['ENGINE']; ?></td>
      </tr>
      <?php
    }
  }
?>
    </tbody>
  </table>

  <div class="form-group">
    <label><?php echo language::translate('title_collation', 'Collation'); ?></label>
    <?php echo functions::form_draw_mysql_collations_list('collation'); ?>
  </div>

  <div class="form-group">
    <div class="checkbox">
      <label><?php echo functions::form_draw_checkbox('set_database_default', 'true'); ?> <?php echo language::translate('text_also_set_as_database_default', 'Also set as database default (when new tables are created)'); ?></label>
    </div>
  </div>

  <div class="form-group">
    <label><?php echo language::translate('title_engine', 'Engine'); ?></label>
    <?php echo functions::form_draw_mysql_engines_list('engine'); ?>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('convert', language::translate('title_convert', 'Convert'), 'submit'); ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>