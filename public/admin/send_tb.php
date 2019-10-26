<?php

require_once("funcs.php");
require_login();


$cxn = dbcxn();
$name = $_POST["name"];
$description = $_POST["description"];

if (!isset($_FILES["zipfile"])) die("File?");

$file_name = $_FILES["zipfile"]["name"];
$file_size = $_FILES["zipfile"]["size"];
$file_tmp = $_FILES["zipfile"]["tmp_name"];
$file_type = $_FILES["zipfile"]["type"];

$encoded = base64_encode(file_get_contents($file_tmp));

$stmt = $cxn->prepare("INSERT INTO testbenches (name, description, contents) "
  . "VALUES (?, ?, ?);");
$stmt->bindValue(1, $name);
$stmt->bindValue(2, $description);
$stmt->bindValue(3, $encoded);
$stmt->execute();
$cxn->close();

header("Location: ./");
die("Done.");

?>
