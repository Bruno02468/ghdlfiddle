<?php

$cxn = new SQLite3("../server/database.db");
$hint = bin2hex(random_bytes(16));
$ip = $_SERVER["REMOTE_ADDR"];
$tb_id = $_POST["testbench"];
$code = $_POST["code"];
$stmt = $cxn->prepare("INSERT INTO jobs (hint, ip, testbench_id, code, status) "
  . "VALUES (?, ?, ?, ?, ?);");
$stmt->bindValue(1, $hint);
$stmt->bindValue(2, $ip);
$stmt->bindValue(3, $tb_id);
$stmt->bindValue(4, $code);
$stmt->bindValue(5, 0);
$stmt->execute();
$cxn->close();

header("Location: results.php?h=$hint");

?>
