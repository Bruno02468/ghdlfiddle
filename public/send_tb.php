<?php

$cxn = new SQLite3("../server/database.db");
$user = $_POST["username"];
$pass = $_POST["password"];
$name = $_POST["name"];
$description = $_POST["description"];

$stmt = $cxn->prepare("SELECT salt, opaque FROM admins WHERE name=?;");
$stmt->bindValue(1, $user);
$theo = $stmt->execute()->fetchArray();

if (!$theo) die("No such user.");

$salt = $theo["salt"];
$opaque = $theo["opaque"];

if (hash("sha512", "$pass:$salt") != $opaque) die("Wrong pass.");

if (!isset($_FILES["zipfile"])) die("File?");

$file_name = $_FILES["zipfile"]["name"];
$file_size = $_FILES["zipfile"]["size"];
$file_tmp = $_FILES["zipfile"]["tmp_name"];
$file_type = $_FILES["zipfile"]["type"];
$file_ext = strtolower(end(explode(".",$_FILES["zipfile"]["name"])));

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
