<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="robots" content="noindex, nofollow" />
<link rel="stylesheet" href="{snippet:template_path}styles/loader.css" media="all" />
<link rel="stylesheet" href="{snippet:template_path}styles/printable.css" media="all" />
<!--snippet:head_tags-->
<!--snippet:styles-->
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