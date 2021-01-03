<style>
:root {
  --app-color: <?php echo $theme['color']; ?>;
}

#content {
  background: linear-gradient(135deg, var(--app-color) 0px, var(--page-background-color) 100px);
}
#content > .panel-app > .panel-heading {
  border-color: <?php echo $theme['color']; ?>;
}
</style>

{snippet:doc}