<?php
require_once("funcs.php");

require_login();

$tbs = dbcxn()->query("SELECT testbench_id, name, description FROM testbenches "
  . "ORDER BY testbench_id DESC;");

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-22780529-9"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-22780529-9');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ghdlfiddle - admin page">
    <meta name="author" content="Bruno Borges Paschoalinoto">
    <title>ghdlfiddle - admin</title>
    <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600"
    rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
    <link rel="stylesheet" href="../ghdlfiddle.css">
  </head>
  <body>
    <div class="container center">
      <h1>ghdlfiddle</h1>
      <h5>open-source vhdl judge</h5>
      <br>
      <br>
      <h5>
        <a href="../">Back to main page...</a><br>
        <a href="logoff.php">Log off</a><br>
        <a href="upload.php">Upload new testbench</a>
      </h5>
        <br>
        Manage testbenches:
        <br>
        <br>
        <ul>
        <?php while ($tb = $tbs->fetchArray()) { ?>
          <li>
            <?php echo htmlspecialchars($tb["name"]); ?>:
            <a href="edit.php?id=<?php echo $tb["testbench_id"]; ?>">
            edit</a>
            |
            <a href="download_tb.php?id=<?php echo $tb["testbench_id"]; ?>">
            download</a>
            |
            <a href="delete.php?id=<?php echo $tb["testbench_id"]; ?>">
            delete</a>
          </li>
        <?php } ?>
        </ul>
      <br>
      <br>
      <i>
        © 2019-2020
        <a href="//oisumida.rs" target="_blank">Bruno Borges Paschoalinoto</a>
        <br>
        Some rights reserved under MIT License.
        <a href="//github.com/Bruno02468/ghdlfiddle">Check out the code!</a>
      </i>
      <br>
      <br>
    </div>
  </body>
</html>
