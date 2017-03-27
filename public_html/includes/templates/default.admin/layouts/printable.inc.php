<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv-printshiv.js"></script><![endif]-->
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
{snippet:head_tags}
<link rel="stylesheet"  href="{snippet:template_path}css/framework.min.css" />
<link rel="stylesheet" href="{snippet:template_path}css/printable.min.css" />
{snippet:style}
</head>
<body data-size="A4">

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