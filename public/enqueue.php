<?php

$cxn = new SQLite3("../server/database.db");
	$cxn->busyTimeout(3000);

// check if google recaptcha is a thing
$sitekey = $cxn->querySingle("SELECT value FROM config WHERE "
  . "key=\"grecaptcha_sitekey\";");
$secretkey = $cxn->querySingle("SELECT value FROM config WHERE "
  . "key=\"grecaptcha_secretkey\";");
$grecaptcha_enabled = $sitekey && $secretkey;
$ip = $_SERVER["REMOTE_ADDR"];

if ($grecaptcha_enabled) {
	if (!isset($_POST["grecaptcha_token"])) die("no captcha token?");
	$token = $_POST["grecaptcha_token"];
	$url = "https://www.google.com/recaptcha/api/siteverify";
	$params = array(
		"secret" => $secretkey,
		"response" => $token,
		"remoteip" => $ip
	);
	$options = array(
		"http" => array(
			"header" => "Content-type: application/x-www-form-urlencoded\n",
			"method" => "POST",
			"content" => http_build_query($params)
		)
	);
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if (!$result) die("bad captcha response");
	$response = json_decode($result, true);
	if (!$response["success"]) die("very bad captcha");
	if ($response["action"] != "enqueue") die("bad captcha origin");
	if ($response["score"] < 0.5) die("lol no");
}

// put it in the database
$hint = bin2hex(random_bytes(16));
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
