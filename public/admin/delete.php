<?php
require_once("funcs.php");
require_login();

$cxn = dbcxn();

if (!isset($_GET["id"])) die("No ID?!");

$stmt = $cxn->prepare("DELETE FROM testbenches WHERE testbench_id=?;");
$stmt->bindValue(1, $_GET["id"]);
$stmt->execute();
header("Location: ./");

?>
