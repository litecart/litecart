<div id="box-logotypes">
  <div class="content">
    <ul class="list-horizontal">
      <?php foreach ($logotypes as $logotype) echo '<li><a href="'. htmlspecialchars($logotype['link']) .'"><img src="'. htmlspecialchars($logotype['image']) .'" alt="'. htmlspecialchars($logotype['title']) .'" title="'. htmlspecialchars($logotype['title']) .'" style="margin: 0px 15px;"></a></li>' . PHP_EOL; ?>
    </ul>
  </div>
</div>