<?php

require_once("funcs.php");
require_login();

$cxn = dbcxn();
$id = $_POST["id"];
$name = $_POST["name"];
$description = $_POST["description"];

$stmt = $cxn->prepare("SELECT name, contents FROM testbenches WHERE "
  . "testbench_id=?;");
$stmt->bindValue(1, $id);
$res = $stmt->execute();
$tb = $res->fetchArray();

if (!$tb) die("id not found, my dude");

if (!isset($_FILES["zipfile"])) die("File?");

$file_name = $_FILES["zipfile"]["name"];
$file_size = $_FILES["zipfile"]["size"];
$file_tmp = $_FILES["zipfile"]["tmp_name"];
$file_type = $_FILES["zipfile"]["type"];

$encoded = base64_encode(file_get_contents($file_tmp));

$stmt = $cxn->prepare("UPDATE testbenches SET name=?, description=?, contents=?"
  . "WHERE testbench_id=?");
$stmt->bindValue(1, $name);
$stmt->bindValue(2, $description);
$stmt->bindValue(3, $encoded);
$stmt->bindValue(4, $id);
$stmt->execute();
$cxn->close();

header("Location: ./");
die("Done.");

?>
