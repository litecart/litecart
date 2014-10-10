<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<link href="{snippet:template_path}styles/loader.css" rel="stylesheet" media="print, screen" />
<link href="{snippet:template_path}styles/printable.css" rel="stylesheet" media="print, screen" />
<!--[if IE]><link rel="stylesheet" href="{snippet:template_path}styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="{snippet:template_path}styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="{snippet:template_path}styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:javascript-->
<?php if (isset($_GET['media']) && $_GET['media'] == 'print') { ?>
<script>
  window.onload = function() {
    window.print();
  }
</script>
<?php } ?>
</head>
<body>

<!--snippet:content-->

<!--snippet:foot_tags-->
</body>
</html>