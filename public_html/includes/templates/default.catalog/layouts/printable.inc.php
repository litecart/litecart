<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
{snippet:head_tags}
<link rel="stylesheet" href="{snippet:template_path}css/framework.min.css" />
<link rel="stylesheet" href="{snippet:template_path}css/printable.min.css" />
{snippet:style}
</head>
<body>

<?php if (isset($_GET['media']) && $_GET['media'] == 'print') { ?>
<button name="print"><?php echo functions::draw_fonticon('fa-print'); ?> <?php echo language::translate('title_print', 'Print'); ?></button>
<?php } ?>

{snippet:content}

{snippet:foot_tags}
{snippet:javascript}
<?php if (isset($_GET['media']) && $_GET['media'] == 'print') { ?>
<script>
  $('button[name="print"]').click(function(){
    window.print();
  });
</script>
<?php } ?>
</body>
</html>