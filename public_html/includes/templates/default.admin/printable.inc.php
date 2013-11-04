<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<link href="<!--snippet:template_path-->styles/printable.css" rel="stylesheet" type="text/css" media="print, screen" />
<link href="<!--snippet:template_path-->styles/loader.css" rel="stylesheet" type="text/css" media="print, screen" />
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