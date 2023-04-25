<style>
#box-about table + table {
  margin-top: 2em;
}
#box-about tbody th {
  text-align: left;
}
#box-about tr > *:first-child {
  width: 250px;
}
</style>

<div id="box-about" class="card">
  <div class="card-header">
    <div class="card-title">
      <?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?>
    </div>
  </div>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th colspan="2">Machine</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Operating System</th>
        <td><?php echo php_uname('s'); ?> <?php echo php_uname('r'); ?></td>
      </tr>
      <tr>
        <th>Operating System Version</th>
        <td><?php echo php_uname('v'); ?></td>
      </tr>
      <tr>
        <th>System Architecture</th>
        <td><?php echo php_uname('m'); ?></td>
      </tr>
      <tr>
        <th>Machine Name</th>
        <td><?php echo php_uname('n'); ?></td>
      </tr>
      <tr>
        <th>Machine IP</th>
        <td><?php echo $_SERVER['SERVER_ADDR']; ?></td>
      </tr>
      <tr>
        <th>Machine Hostname</th>
        <td><?php echo gethostbyaddr($_SERVER['SERVER_ADDR']); ?></td>
      </tr>
    </tbody>
  </table>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th colspan="2">Web Server</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Daemon</th>
        <td><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '<em>Unknown</em>'; ?></td>
      </tr>
      <tr>
        <th>SAPI</th>
        <td><?php echo php_sapi_name(); ?></td>
      </tr>
      <tr>
        <th>Current User</th>
        <td><?php echo get_current_user(); ?></td>
      </tr>
    </tbody>
  </table>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th colspan="2">PHP</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>PHP Version</th>
        <td><?php echo PHP_VERSION; ?> <?php echo (PHP_INT_SIZE === 8) ? '64-bit' : '32-bit'; ?></td>
      </tr>
      <tr>
        <th>Whoami</th>
        <td><?php echo (function_exists('exec') && !in_array('exec', preg_split('#\s*,\s*#', ini_get('disabled_functions')))) ? exec('whoami') : '<em>Unknown</em>'; ?></td>
      </tr>
      <tr>
        <th>PHP Extensions</th>
        <td style="columns: 100px auto;"><div><?php echo implode('</div><div>', get_loaded_extensions()); ?></div></td>
      </tr>
      <tr>
        <th>Disabled PHP Functions</th>
        <td><?php echo ini_get('disabled_functions') ? ini_get('disabled_functions') : '<em>None</em>'; ?></td>
      </tr>
      <tr>
        <th>Memory Limit</th>
        <td><?php echo ini_get('memory_limit') ? ini_get('memory_limit') : '<em>N/A</em>'; ?></td>
      </tr>
    </tbody>
  </table>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th colspan="2">Database</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <th>Server Name</th>
        <td><?php echo database::server_info(); ?></td>
      </tr>
      <tr>
        <th>Client Library</th>
        <td><?php echo mysqli_get_client_info(); ?></td>
      </tr>
      <tr>
        <th>Hostname</th>
        <td><?php echo DB_SERVER; ?></td>
      </tr>
      <tr>
        <th>User</th>
        <td><?php echo DB_USERNAME; ?></td>
      </tr>
      <tr>
        <th>Database</th>
        <td><?php echo DB_DATABASE; ?></td>
      </tr>
    </tbody>
  </table>
</div>