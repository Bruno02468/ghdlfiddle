<?php

$cxn = new SQLite3("../server/database.db");
$tbs = $cxn->query("SELECT testbench_id, name, description FROM testbenches;");
$jsin = "const descriptions = [];";

?>

<!DOCTYPE html>
<html lang="pt-br">
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
    <link rel="stylesheet" href="errado.css">
  </head>
  <body>
    <div class="container center">
      <div class="row">
        <h1>ghldfiddle</h1>
				<h2>test ghdl code on the fly</h2>
      </div>
      <br>
			Paste your code: <br>
			<textarea id="code"></textarea>
      <br>
			<br>
			Select a testbench to test against:
			<select id="testbench">
				<?php while ($tb = $tbs->fetchArray()) { ?>
					<option value="<?php echo $tb["testbench_id"]; ?>">
						<?php echo htmlspecialchars($tb["name"]) ?>
					</option>
					<?php $jsin .= "descriptions.push(" . json_encode($tb["description"])
					. ")"; } ?>
			</select>
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
        Alguns direitos reservados!
      </i>
      <br>
      <br>
    </div>
  </body>
</html>
