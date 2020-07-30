<style>
#content {
  background: linear-gradient(135deg, rgba(<?php echo implode(', ', sscanf($theme['color'], "#%02x%02x%02x")); ?>, 1) 0px, var(--page-background-color) 100px);
}

#content > .panel-app > .panel-heading {
  border-color: <?php echo $theme['color']; ?>;
}
</style>

{snippet:doc}