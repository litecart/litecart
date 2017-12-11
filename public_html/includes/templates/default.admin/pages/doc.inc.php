<style>
#main {
  background: linear-gradient(135deg, rgba(<?php echo implode(', ', sscanf($theme['color'], "#%02x%02x%02x")); ?>, 1) 0px, rgba(255,255,255,1) 100px);
}
</style>

<span class="pull-right" style="margin-left: 15px;"><a href="<?php echo htmlspecialchars($help_link); ?>" target="_blank" title="<?php echo language::translate('title_help', 'Help'); ?>"><?php echo functions::draw_fonticon('fa-question-circle', 'style="font-size: 40px; color: #0099cc;"'); ?></a></span>

{snippet:doc}