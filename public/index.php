<?php

$cxn = new SQLite3("../server/database.db");
$tbs = $cxn->query("SELECT testbench_id, name, description FROM testbenches;");
$jsin = "";

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ghdlfiddle">
    <meta name="author" content="Bruno Borges Paschoalinoto">
    <title>ghdlfiddle</title>
    <link href="//fonts.googleapis.com/css?family=raleway:400,300,600"
    rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
    <link rel="stylesheet" href="ghdlfiddle.css">
  </head>
  <body>
    <div class="container center">
			<h1>ghldfiddle</h1>
			<h4>test ghdl code on the fly</h4>
      <br>
			<form method="POST" action="enqueue.php">
				Paste your code: <br>
				<textarea id="code"></textarea>
				<br>
				<br>
				Select a testbench to test against:
				<select id="testbench" oninput="update_description()">
<?php while ($tb = $tbs->fetchArray()) { ?>
						<option value="<?php echo $tb["testbench_id"]; ?>">
<?php echo htmlspecialchars($tb["name"]) ?>
						</option>
<?php
$jsin .= "descriptions.push(\"" . json_encode($tb["description"]) . "\");"; }
?>
				</select>
			</form>
			<br>
			Selected testbench description and instructions:<br>
			<br>
			<small><i id="description"></i></small>
      <br>
      <br>
      <i>
        © 2019
        <a href="//oisumida.rs" target="_blank">Bruno Borges Paschoalinoto</a>
        <br>
				Some rights reserved under MIT License.
				<a href="//github.com/Bruno02468/ghdlfiddle">Check out the code!</a>
      </i>
      <br>
      <br>
		</div>
		<script>
		let descriptions = [];
<?php echo $jsin . file_get_contents("index.js"); ?>
		</script>
  </body>
</html>
