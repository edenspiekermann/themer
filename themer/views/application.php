<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- Themer v<?php echo \Themer::VERSION; ?> -->

<head>
  <title>Themer</title>
  <meta name="Copyright" content="Copyright (c) 2011 Braden Schaeffer" />
  
  <link rel="stylesheet" href="/themer_asset/css/reset.css" type="text/css">
  <link rel="stylesheet" href="/themer_asset/css/themer.css" type="text/css">
  <link rel="stylesheet" href="/themer_asset/farbtastic/farbtastic.css" type="text/css">
  
  <script type="text/javascript" src="/themer_asset/js/jquery.min.js"></script>
  <script type="text/javascript" src="/themer_asset/js/jquery-ui.min.js"></script>
  <script type="text/javascript" src="/themer_asset/farbtastic/farbtastic.js"></script>
  <script type="text/javascript" src="/themer_asset/js/themer.js"></script>

</head>
<body>
  <div id="nav">
    <a href="#about" id="about">Themer</a>

    <a href="#" name="info" class="option button">Info</a>
    <a href="#" name="appearance" class="option button">Appearance</a>
    <a href="#" name="pages" class="option button">Pages</a>
    <a href="#" name="community" class="option button">Community</a>
    <a href="#" name="advanced" class="option button">Advanced</a>

    <a href="#" name="export" class="action button">Export</a>

    <?php \Themer\View::load('menus', $data); ?>
  </div>
  
  <div id="theme-container">
    <iframe id="theme-frame" scrolling="auto" frameborder="0" src="/?theme"></iframe>
  </div>
</body>
</html>