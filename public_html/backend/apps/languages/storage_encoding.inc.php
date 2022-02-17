<?php

  document::$snippets['title'][] = language::translate('title_storage_encoding', 'Storage Encoding');

  breadcrumbs::add(language::translate('title_languages', 'Languages'), document::ilink(__APP__.'/languages'));
  breadcrumbs::add(language::translate('title_storage_encoding', 'Storage Encoding'));

// Table Rows
  $tables_query = database::query(
    "SELECT * FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '". DB_DATABASE ."'
    order by TABLE_NAME;"
  );

  $tables = [];
  $defined_tables = [];

  while ($table = database::fetch($tables_query)) {
    $tables[] = $table;
    if (preg_match('#^'.preg_quote(DB_TABLE_PREFIX, '#').'#', $table['TABLE_NAME'])) {
      $defined_tables[] = $table['TABLE_NAME'];
    }
  }

  if (!$_POST) {
    $_POST['tables'] = $defined_tables;
  }

  if (isset($_POST['convert'])) {

    try {
      if (empty($_POST['tables'])) throw new Exception(language::translate('error_must_select_tables', 'You must select tables'));
      if (empty($_POST['collation']) && empty($_POST['engine'])) throw new Exception(language::translate('error_must_select_action_to_perform', 'You must select an action to perform'));

      $table_names = array_column($tables, 'TABLE_NAME');

      foreach ($_POST['tables'] as $table) {
        if (!in_array($table, $table_names)) throw new Exception(strtr(language::translate('error_unknown_table_x', 'Unknown table (%table)')), ['%table' => $table]);
      }

      $_POST['collation'] = preg_replace('#[^a-z0-9_]#', '', $_POST['collation']);

      if (!empty($_POST['set_database_default'])) {
        database::query(
          "alter database `". DB_DATABASE ."`
          default character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ." collate ". database::input($_POST['collation']) .";"
        );
      }

      if (!empty($_POST['collation'])) {
        foreach ($_POST['tables'] as $table) {
          database::query(
            "alter table `". DB_DATABASE ."`.`". $table ."`
            convert to character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ." collate ". database::input($_POST['collation']) .";"
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

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Number of Rows
  $num_rows = count($tables);

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_storage_encoding', 'Storage Encoding'); ?>
    </div>
  </div>

  <?php echo functions::form_draw_form_begin('mysql_collation_form', 'post'); ?>
    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th class="main"><?php echo language::translate('title_table', 'Table'); ?></th>
          <th><?php echo language::translate('title_collation', 'Collation'); ?></th>
          <th><?php echo language::translate('title_engine', 'Engine'); ?></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($tables as $table) { ?>
        <tr>
          <td><?php echo functions::form_draw_checkbox('tables[]', $table['TABLE_NAME'], true); ?></td>
          <td><?php echo $table['TABLE_NAME']; ?></td>
          <td><?php echo $table['TABLE_COLLATION']; ?></td>
          <td><?php echo $table['ENGINE']; ?></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="12"><?php echo language::translate('title_tables', 'Tables'); ?>: <?php echo language::number_format($num_rows); ?></td>
        </tr>
      </tfoot>
    </table>

    <div class="card-body">
      <div style="width: 640px;">
        <div class="form-group">
          <label><?php echo language::translate('title_collation', 'Collation'); ?></label>
          <?php echo functions::form_draw_mysql_collations_list('collation'); ?>
        </div>

        <div class="form-group">
          <?php echo functions::form_draw_checkbox('set_database_default', ['1', language::translate('text_also_set_as_database_default', 'Also set as database default (when new tables are created)')], true); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_engine', 'Engine'); ?></label>
          <?php echo functions::form_draw_mysql_engines_list('engine'); ?>
        </div>
      </div>

      <p><?php echo language::translate('description_set_mysql_collation', 'This will recursively convert the charset and collation for all selected database tables and belonging columns.'); ?></p>

      <div class="btn-group">
        <?php echo functions::form_draw_button('convert', language::translate('title_convert', 'Convert'), 'submit'); ?>
      </div>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
</div>
