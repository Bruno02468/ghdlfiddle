<?php
require_once("funcs.php");
require_login();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ghdlfiddle - upload testbench">
    <meta name="author" content="Bruno Borges Paschoalinoto">
    <title>ghdlfiddle - upload testbench</title>
    <link rel="stylesheet" href="../ghdlfiddle.css">
    <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600"
    rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
  </head>
  <body>
    <div class="container center">
			<h1>ghdlfiddle</h1>
			<h5>open-source vhdl judge</h5>
      <br>
			<form method="POST" action="send_tb.php" enctype="multipart/form-data">
				Testbench name:
				<input type="text" name="name"><br>
				Testbench description and instructions:<br>
        <textarea name="description"></textarea>
        <br>
        <br>
        ZIP file: <input type="file" name="zipfile"><br>
        <br>
        <input type="submit" value="Upload testbench!">
			</form>
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
  </body>
</html>
