<?php

require_once("funcs.php");
$user = $_POST["username"];
$pass = $_POST["password"];
if (admin_cookie($user, $pass)) {
  header("Location: ./");
  die("Redirecting to admin page...");
} else {
  header("Location: ./login.php");
  die("Credentials incorrect.");
}


?>
