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
#box-about meter {
  width: 500px;
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
        <th colspan="2">Application</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Web Path</th>
        <td><?php echo WS_DIR_APP; ?></td>
      </tr>
      <tr>
        <th>System Path</th>
        <td><?php echo FS_DIR_APP; ?></td>
      </tr>
      <tr>
        <th>Document Root</th>
        <td><?php echo DOCUMENT_ROOT; ?></td>
      </tr>
    </tbody>
  </table>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th colspan="2">Machine</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Name</th>
        <td><?php echo $machine['name']; ?></td>
      </tr>
      <tr>
        <th>IP Address</th>
        <td><?php echo $machine['ip_address']; ?></td>
      </tr>
      <tr>
        <th>Hostname</th>
        <td><?php echo $machine['hostname']; ?></td>
      </tr>
      <tr>
        <th>Operating System</th>
        <td><?php echo $machine['os']['name']; ?></td>
      </tr>
      <tr>
        <th>Operating System Version</th>
        <td><?php echo $machine['os']['version']; ?></td>
      </tr>
      <tr>
        <th>System Architecture</th>
        <td><?php echo $machine['architecture']; ?></td>
      </tr>
      <tr>
        <th>CPU Usage</th>
        <td><?php echo !empty($machine['cpu_usage']) ? '<meter class="memory-usage" value="'. (float)$machine['cpu_usage'] .'" max="100" min="0" high="30" low="10" optimum="5"></meter>' : '<em>n/a</em>'; ?></td>
      </tr>
      <tr>
        <th>Memory Usage</th>
        <td><?php echo !empty($machine['memory_usage']) ? '<meter class="memory-usage" value="'. (float)$machine['memory_usage'] .'" max="100" min="0" high="30" low="10" optimum="5"></meter>' : '<em>n/a</em>'; ?></td>
      </tr>
      <tr>
        <th>Uptime</th>
        <td><?php echo !empty($uptime) ? $uptime : '<em>n/a</em>'; ?></td>
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
        <td><?php echo !empty($web_server['name']) ? $web_server['name'] : '<em>Unknown</em>'; ?></td>
      </tr>
      <tr>
        <th>SAPI</th>
        <td><?php echo $web_server['sapi']; ?></td>
      </tr>
      <tr>
        <th>Current User</th>
        <td><?php echo $web_server['current_user']; ?></td>
      </tr>
      <tr>
        <th>Enabled Modules</th>
        <td><?php echo !empty($web_server['loaded_modules']) ? implode(', ', $web_server['loaded_modules']) : '<em>n/a</em>'; ?></td>
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
        <th>Version</th>
        <td><?php echo $php['version']; ?></td>
      </tr>
      <tr>
        <th>Whoami</th>
        <td><?php echo !empty($php['whoami']) ? $php['whoami'] : '<em>Unknown</em>'; ?></td>
      </tr>
      <tr>
        <th>PHP Extensions</th>
        <td style="columns: 100px auto;"><div><?php echo !empty($php['loaded_extensions']) ? implode('</div><div>', $php['loaded_extensions']) : '<em>None</em>'; ?></div></td>
      </tr>
      <tr>
        <th>Disabled PHP Functions</th>
        <td><?php echo !empty($php['disabled_functions']) ? implode(', ', $php['disabled_functions']) : '<em>None</em>'; ?></td>
      </tr>
      <tr>
        <th>Memory Limit</th>
        <td><?php echo !empty($php['memory_limit']) ? $php['memory_limit'] : '<em>n/a</em>'; ?></td>
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
        <td><?php echo $database['name']; ?></td>
      </tr>
      <tr>
        <th>Client Library</th>
        <td><?php echo $database['library']; ?></td>
      </tr>
      <tr>
        <th>Hostname</th>
        <td><?php echo $database['hostname']; ?></td>
      </tr>
      <tr>
        <th>User</th>
        <td><?php echo $database['user']; ?></td>
      </tr>
      <tr>
        <th>Database</th>
        <td><?php echo $database['database']; ?></td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  <?php if (!empty($machine['cpu_usage']) || !empty($machine['memory_usage'])) { ?>
  setInterval(function(){
    $.ajax({
      cache: false,
      dataType: 'html',
      success: function(result){
        var $cpu_usage = $('meter.cpu-usage', result);
        var $memory_usage = $('meter.memory-usage', result);
        $('meter.cpu-usage').replaceWith($cpu_usage)
        $('meter.memory-usage').replaceWith($memory_usage)
      },
    });
  }, 3000);
  <?php } ?>
</script>