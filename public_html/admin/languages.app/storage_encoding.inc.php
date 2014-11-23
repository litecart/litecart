<?php
  $defined_tables = array();
  foreach (get_defined_constants() as $constant) {
    if (preg_match('#^`'. preg_quote(DB_DATABASE, '#') .'`\.`(.*)`$#', $constant, $matches)) {
      $defined_tables[] = $matches[1];
    }
  }
  
  if (!empty($_POST['convert'])) {
    
    if (empty($_POST['tables'])) notices::$data['errors'][] = language::translate('error_must_select_tables', 'You must select at least one table');
    
    if (empty(notices::$data['errors'])) {
      
      if (!empty($_POST['set_database_default'])) {
        database::query("alter database `". DB_DATABASE ."` default character set '". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ."' collate = '". database::input($_POST['collation']) ."';");
      }
      
      foreach ($_POST['tables'] as $table) {
        database::query("alter table `". DB_DATABASE ."`.`". $table ."` default character set ". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ." collate ". database::input($_POST['collation']) .";");
        
        $columns_query = database::query("select * from `information_schema`.`COLUMNS` where `TABLE_SCHEMA` = '". DB_DATABASE ."' and `TABLE_NAME` = '". database::input($table) ."' and COLLATION_NAME != '". database::input($_POST['collation']) ."';");
        while($column = database::fetch($columns_query)) {
          database::query(
            "alter table `". DB_DATABASE ."`.`". $table. "`
            modify `". $column['COLUMN_NAME'] ."`
            character set '". database::input(preg_replace('#^([^_]+).*$#', '$1', $_POST['collation'])) ."'
            collate '". database::input($_POST['collation']) ."';"
          );
        }
      }
      
      notices::$data['success'][] = language::translate('success_changes_saved', 'Changes saved');
      
      header('Location: '. document::ilink());
      exit;
    }
  }
  
  if (empty($_POST)) $_POST['tables'] = $defined_tables;
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_storage_encoding', 'Storage Encoding'); ?></h1>

<p><?php echo language::translate('description_set_mysql_collation', 'This will recursively convert the charset and collation for all selected database tables and belonging columns.'); ?></p>

<?php echo functions::form_draw_form_begin('mysql_collation_form', 'post'); ?>

  <table>
    <tr>
      <td><?php echo language::translate('title_database_tables', 'Database Tables'); ?><br />
<?php
  $options = array();
  
  $tables_query = database::query(
    "select * from `information_schema`.`TABLES`
    where TABLE_SCHEMA = '". DB_DATABASE ."';"
  );
  while ($table = database::fetch($tables_query)) {
    if (in_array($table['TABLE_NAME'], $defined_tables)) {
      $options[] = array($table['TABLE_NAME'] .' -- '. $table['TABLE_COLLATION'], $table['TABLE_NAME']);
    }
  }
  
  echo functions::form_draw_select_field('tables[]', $options, true, true, 'data-size="large" style="height: 200px;"');
?>
      </td>
    </tr>
    <tr>
      <td><?php echo language::translate('title_collation', 'Collation'); ?><br />
        <?php echo functions::form_draw_mysql_collations_list('collation'); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo functions::form_draw_checkbox('set_database_default', 'true'); ?> <?php echo language::translate('text_also_set_as_database_default', 'Also set as database default (when new tables are created)'); ?>
      </td>
    </tr>
    <tr>
      <td><?php echo functions::form_draw_button('convert', language::translate('title_convert', 'Convert'), 'submit'); ?><br /></td>
      <td></td>
    </tr>
  </table>

<?php echo functions::form_draw_form_end(); ?>