<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- Themer v<?php echo \Themer::VERSION; ?> -->

<head>
  <title>Themer - Error</title>
  <meta name="Copyright" content="Copyright (c) 2011 Braden Schaeffer" />  
  <style type="text/css" media="screen">
    body { font-family: Helvetica, Arial, sans-serf; }
    #page { width: 600px; margin: 50px auto; }
    h1 { font-size: 32px; margin: 0 0 15px 0; }
    h2 { margin: 5px 0 10px 0; }
    p { font-size: 14px; line-height: 20px; }
    #error { border: 1px solid #bbb; padding: 10px; background: #eee; }
  </style>
</head>
<body>
  <div id="page">
    <h1>THEMER Error</h1>
    <div id="error">
      <h2><?php echo $title; ?></h2>
      <p><?php echo $message; ?></p>
    </div>
  </div>
</body>
</html>