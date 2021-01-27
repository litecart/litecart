<style>
:root {
  --app-color: <?php echo $theme['color']; ?>;
}

#content {
  background: linear-gradient(180deg, var(--app-color) 5px, var(--page-background-color) 5px);
}
#content > .panel-app > .panel-heading {
  border-color: <?php echo $theme['color']; ?>;
}
</style>

{{doc}}