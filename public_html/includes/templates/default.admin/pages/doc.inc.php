<style>
#content {
  /*background: linear-gradient(135deg, rgba(<?php echo implode(', ', sscanf($theme['color'], "#%02x%02x%02x")); ?>, 1) 0px, rgba(255,255,255,1) 100px);*/
  /*border-top: 1px solid <?php echo $theme['color']; ?>;*/
}

#content > h1 {
  border-color: <?php echo $theme['color']; ?>;
}
</style>

{snippet:doc}