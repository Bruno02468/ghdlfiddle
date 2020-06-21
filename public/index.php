<?php
$cxn = new SQLite3("../server/database.db");
$cxn->busyTimeout(3000);

// get all testbenches to fill select tag
$tbs = $cxn->query("SELECT testbench_id, name, description FROM testbenches "
  . "ORDER BY testbench_id DESC;");
$jsin = "";
$sc = $cxn->prepare("SELECT COUNT(*) AS count FROM jobs WHERE status=2;");
$c = $sc->execute()->fetchArray()["count"];

// check if google recaptcha is a thing
$sitekey = $cxn->querySingle("SELECT value FROM config WHERE "
  . "key=\"grecaptcha_sitekey\";");
$secretkey = $cxn->querySingle("SELECT value FROM config WHERE "
  . "key=\"grecaptcha_secretkey\";");
$grecaptcha_enabled = $sitekey && $secretkey;

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-22780529-9"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-22780529-9');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="open-source vhdl judge">
    <meta name="author" content="Bruno Borges Paschoalinoto">
    <title>ghdlfiddle</title>
    <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600"
    rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
    <link rel="stylesheet" href="ghdlfiddle.css">
  </head>
  <body>
    <div class="container center">
      <h1>ghdlfiddle</h1>
      <h5>open-source vhdl judge</h5>
      Submissions run so far:
      <b><?php echo $c; ?></b> =)
      <br>
      <br>
      <br>
      <form method="POST" action="enqueue.php">
        <div class="row">
          <div class="twelve columns">Paste your code down here:</div>
          <textarea name="code" id="code"></textarea>
        </div>
        <br>
        <br>
        Select a testbench to test against:
        <select id="testbench" name="testbench" oninput="update_description()">
<?php while ($tb = $tbs->fetchArray()) { ?>
            <option value="<?php echo $tb["testbench_id"]; ?>">
<?php echo htmlspecialchars($tb["name"]) ?>
            </option>
<?php
$jsin .= "descriptions[" . $tb["testbench_id"] . "] = ("
  . json_encode($tb["description"]) . ");"; }
?>
        </select>
        <br>
        <i id="description"></i>
        <br>
<?php if ($grecaptcha_enabled) { ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $sitekey; ?>"></script>
        <script>
        grecaptcha.ready(function() {
          grecaptcha.execute("<?php echo $sitekey; ?>", {action: "enqueue"}).then(function(token) {
            document.getElementById("gv3_token").value = token;
          });
        });
        </script>
        <input type="hidden" value="" name="grecaptcha_token" id="gv3_token">
<?php } ?>
        <br>
        <br>
        <input type="submit" value="Queue!">
      </form>
      <br>
      <br>
      <br>
      <i>
        © 2019-2020
        <a href="//oisumida.rs" target="_blank">Bruno Borges Paschoalinoto</a>
        <br>
        Some rights reserved under MIT License.
        <a href="//github.com/Bruno02468/ghdlfiddle">Check out the code!</a>
      </i>
      <br>
      <br>
    </div>
    <script>
    let descriptions = {};
<?php echo $jsin . file_get_contents("homepage.js"); ?>
    </script>
  </body>
</html>
