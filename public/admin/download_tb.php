<?php

require_once("funcs.php");

if (!isset($_GET["id"])) die("no id, my dude");

$id = $_GET["id"];
$cxn = dbcxn();
$stmt = $cxn->prepare("SELECT name, contents FROM testbenches WHERE "
  . "testbench_id=?;");
$stmt->bindValue(1, $id);
$res = $stmt->execute();
$tb = $res->fetchArray();

if (!$tb) die("id not found, my dude");

$fn = sanitize_filename($tb["name"]) . ".zip";
$fc = base64_decode($tb["contents"]);
header("Content-Type: application/zip");
header("Content-Length: " . strlen($fc));
header("Content-Disposition: attachment; filename=\"$fn\"");
echo $fc;

?>
