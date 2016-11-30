<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<link href="{snippet:template_path}styles/loader.css" rel="stylesheet" media="print, screen" />
<link href="{snippet:template_path}styles/printable.css" rel="stylesheet" media="print, screen" />
<!--[if IE]><link rel="stylesheet" href="{snippet:template_path}styles/ie.css" media="all" /><![endif]-->
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv-printshiv.js"></script><![endif]-->
<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:style-->
</head>
<body>

<!--snippet:content-->

<!--snippet:foot_tags-->
<!--snippet:javascript-->
<?php if (isset($_GET['media']) && $_GET['media'] == 'print') { ?>
<script>
  window.onload = function() {
    window.print();
  }
</script>
<?php } ?>
</body>
</html>