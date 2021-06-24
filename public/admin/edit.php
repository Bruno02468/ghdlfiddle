<?php
require_once("funcs.php");
require_login();

require_once("funcs.php");
require_login();

if (!isset($_GET["id"])) die("no id, my dude");

$id = $_GET["id"];
$cxn = dbcxn();
$stmt = $cxn->prepare("SELECT name, description FROM testbenches WHERE "
  . "testbench_id=?;");
$stmt->bindValue(1, $id);
$res = $stmt->execute();
$tb = $res->fetchArray();

if (!$tb) die("id not found, my dude");

$hname = htmlspecialchars($tb["name"]);
$hdesc = htmlspecialchars($tb["description"]);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ghdlfiddle - edit testbench">
    <meta name="author" content="Bruno Borges Paschoalinoto">
    <title>ghdlfiddle - edit testbench</title>
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
      <form method="POST" action="update_tb.php" enctype="multipart/form-data">
        Testbench name:
        <input type="text" name="name" value="<?php echo $hname; ?>"><br>
        Testbench description and instructions:<br>
        <textarea name="description"><?php echo $hdesc; ?></textarea>
        <br>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <br>
        ZIP file: <input type="file" name="zipfile"><br>
        <br>
        <input type="submit" value="Update testbench!">
      </form>
      <br>
      <br>
      <i>
        <strike>©</strike> 2019-2021
        <a href="//oisumida.rs" target="_blank">Bruno Borges Paschoalinoto</a>
        <br>
        No rights reserved.
        <a href="//github.com/Bruno02468/ghdlfiddle">Check out the code!</a>
      </i>
      <br>
      <br>
    </div>
  </body>
</html>
