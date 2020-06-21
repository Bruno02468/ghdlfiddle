<?php

// functions used by admin pages to manage login tokens and such
$COOKIE_DURATION_DAYS = 60;
$COOKIE_NAME = "ghdlfiddle_token";


function dbcxn() {
  return new SQLite3("../../server/database.db");
}

function username() {
  global $COOKIE_NAME;
  if (!isset($_COOKIE["ghdlfiddle_token"])) return null;
  $code = $_COOKIE[$COOKIE_NAME];
  $cxn = dbcxn();
  $notexp = "expires >= " . ((string) time());
  $stmt = $cxn->prepare("SELECT username FROM tokens WHERE code=? AND expires "
    . ">= " . ((string) time()) . " ORDER BY token_id DESC;");
  $stmt->bindValue(1, $code);
  $all = $stmt->execute()->fetchArray();
  if ($all) {
    return $all["username"];
  } else {
    remove_cookie();
    return null;
  }
}

function require_login() {
  $u = username();
  if ($u !== null) {
    return $u;
  } else {
    header("Location: ./login.php");
    die("Redirecting to login form, for login is required.");
  }
}

function auth_correct($username, $password) {
  $cxn = dbcxn();
  $stmt = $cxn->prepare("SELECT name, salt, opaque FROM admins WHERE name=?;");
  $stmt->bindValue(1, $username);
  $theo = $stmt->execute()->fetchArray();
  if (!$theo) return false;
  $salt = $theo["salt"];
  $opaque = $theo["opaque"];
  if (hash("sha512", "$password:$salt") != $opaque) return false;
  return true;
}

function random_cookie() {
  return bin2hex(random_bytes(32));
}

function remove_cookie() {
  global $COOKIE_NAME;
  setcookie($COOKIE_NAME, "", time() - 360000);
}

function admin_cookie($username, $password) {
  global $COOKIE_NAME, $COOKIE_DURATION_DAYS;
  if (auth_correct($username, $password)) {
    $cxn = dbcxn();
    $stmt =  $cxn->prepare("INSERT INTO tokens (code, username, expires) VALUES"
      . "(?, ?, ?);");
    $cookiecode = random_cookie();
    $stmt->bindValue(1, $cookiecode);
    $stmt->bindValue(2, $username);
    $expires = time() + $COOKIE_DURATION_DAYS * 86400;
    $stmt->bindValue(3, $expires);
    $stmt->execute();
    $cxn->close();
    setcookie($COOKIE_NAME, $cookiecode, $expires);
    return true;
  } else {
    remove_cookie();
    return false;
  }
}

function logoff() {
  global $COOKIE_NAME;
  if (!isset($_COOKIE["ghdlfiddle_token"])) return null;
  $code = $_COOKIE[$COOKIE_NAME];
  $cxn = dbcxn();
  $stmt = $cxn->prepare("DELETE FROM tokens WHERE code=?;");
  $stmt->bindValue(1, $code);
  $stmt->execute();
  remove_cookie();
}

function sanitize_filename($fn) {
  $fn = preg_replace("/[.\/\\~$@&*\(\)\[\]\<\>,;^!%]/", "", $fn);
  $fn = str_replace(" - ", "_", $fn);
  $fn = str_replace(" ", "_", $fn);
  return $fn;
}

?>
