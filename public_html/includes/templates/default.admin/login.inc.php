<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<link href="<!--snippet:template_path-->styles/loader.css" rel="stylesheet" media="print, screen" />
<!--[if IE]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie.css" /><![endif]-->
<!--[if IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie9.css" /><![endif]-->
<!--[if lt IE 9]><link rel="stylesheet" href="<!--snippet:template_path-->styles/ie8.css" /><![endif]-->
<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<!--snippet:head_tags-->
<!--snippet:javascript-->
<style>
html, body, body > table, body > table {
  width: 100%;
  height: 100%;
  background: rgb(255,255,255); /* Old browsers */
  /* IE9 SVG, needs conditional override of 'filter' to 'none' */
  background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZmZmZmZiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjUwJSIgc3RvcC1jb2xvcj0iI2YxZjFmMSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjUxJSIgc3RvcC1jb2xvcj0iI2UxZTFlMSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNmNmY2ZjYiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
  background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(241,241,241,1) 50%, rgba(225,225,225,1) 50%, rgba(246,246,246,1) 100%); /* FF3.6+ */
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(50%,rgba(241,241,241,1)), color-stop(50%,rgba(225,225,225,1)), color-stop(100%,rgba(246,246,246,1))); /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 50%,rgba(246,246,246,1) 100%); /* Chrome10+,Safari5.1+ */
  background: -o-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 50%,rgba(246,246,246,1) 100%); /* Opera 11.10+ */
  background: -ms-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 50%,rgba(246,246,246,1) 100%); /* IE10+ */
  background: linear-gradient(to bottom,  rgba(255,255,255,1) 0%,rgba(241,241,241,1) 50%,rgba(225,225,225,1) 50%,rgba(246,246,246,1) 100%); /* W3C */
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=0 ); /* IE6-8 */
}
#content-wrapper {
  width: 300px;
  margin: auto;
  background: rgba(240,240,240,0.9);
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
  padding: 20px;
}
#box-login {
  width: 200px;
  margin: auto;
}
</style>
</head>
<body>

<table style="width: 100%;">
  <tr>
    <td style="text-align: center;">
    <div id="content-wrapper">
      <div id="box-login-wrapper">
        <!--snippet:notices-->
        <!--snippet:content-->
      </div>
    </div>
    </td>
  </tr>
</table>

<script>
  $("form[name='login_form']").submit(function() {
    $('#content-wrapper').slideUp('fast');
  });
</script>

<!--snippet:foot_tags-->
</body>
</html>