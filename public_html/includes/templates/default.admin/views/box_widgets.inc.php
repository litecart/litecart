<div id="box-widgets-wrapper">
  <ul id="box-widgets">
    <?php foreach ($widgets as $widget) { ?>
    <li id="widget-<?php echo $widget['code']; ?>">
      <?php echo $widget['content']; ?>
    </li>
    <?php } ?>
  </ul>
</div>