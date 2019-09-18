<?php

if (!isset($_GET["h"])) {
  header("Location: ./");
  die("Forgot to set the h variable, huh?");
}

// basic setup

$hint = $_GET["h"];
$cxn = new SQLite3("../server/database.db");
$stmt = $cxn->prepare("SELECT job_id, status, vcd FROM jobs WHERE hint=?;");
$stmt->bindValue(1, $hint);
$job = $stmt->execute()->fetchArray();

if (!$job) {
  header("Location: ./");
  die("Job does not exist. How?");
}

if ($job["status"] < 2) {
  // job is enqueued or runninng, prepare to inform user
  $mode = "bluish";
  if ($job["status"]) {
    // running
    $status = "RUNNING, please refresh shortly.";
  } else {
    // enqueued
    $ahc = $cxn->prepare("SELECT COUNT(*) AS count FROM jobs WHERE status<2 AND"
      . " job_id < ?;");
    $ahc->bindValue(1, $job["job_id"]);
    $ahead = $ahc->execute()->fetchArray()["count"];
    $status = "IN QUEUE, with $ahead jobs ahead of it.<br>"
      . "Refresh to see if it's gone up!";
  }
} else {
  // job is finished, we better get some results going on
  // first, fetch the report
  $rhc = $cxn->prepare("SELECT * FROM reports WHERE job_id=?;");
  $rhc->bindValue(1, $job["job_id"]);
  $report = $rhc->execute()->fetchArray();
  if (!$report) {
    die("Job is FINISHED but no report exists. Report this!");
  }
	$status = "DONE RUNNING.";
  if ($report["code"] < 0) {
    $rcode = "NOT GOOD";
    $mode = "reddish";
  } elseif ($report["code"] > 0) {
    $rcode = "GOOD";
    $mode = "greenish";
  } else {
    $rcode = "NOT SURE";
    $mode = "yellowish";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ghdlfiddle">
    <meta name="author" content="Bruno Borges Paschoalinoto">
    <title>ghdlfiddle - results</title>
    <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600"
    rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" type="text/css"
    href="//cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
    <link rel="stylesheet" href="ghdlfiddle.css">
  </head>
  <body class="<?php echo $mode; ?>">
    <div class="container center">
      <h1>ghldfiddle</h1>
      <h5>test ghdl code on the fly</h5>
			<br>
			<a href="./">Back to main page</a>
			<br>
			<br>
      <b>
        Save 
        <a href="results.php?h=<?php echo $hint; ?>">this URL</a>,
        it's the only way to access this job's results.
      </b><br>
      <br>
      <br>
      <h4 class="status"> Your job is <?php echo $status; ?></h4>
<?php if (isset($rcode)) { ?>
			<br>
      <h5 class="results">
        <div id="rcode">General result: <?php echo $rcode; ?></div>
        <br>
        <br>
				<?php if ($job["vcd"]) { ?>
				A VCD file is
				<a target="_blank" download href="vcd/<?php echo $hint ?>.vcd"
				>available!</a>
				<?php } ?>
        Here's the outputs:<br>
        <br>
        <div class="row">
          <div class="six columns">
            Job manager says:<br>
            <div class="output"><?php echo $report["meta"]; ?></div>
          </div>
          <div class="six columns">
            Analysis (ghdl -a) says:<br>
            <div class="output"><?php echo $report["analysis"]; ?></div>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="six columns">
            Compilation (ghdl -e) says:<br>
            <div class="output"><?php echo $report["compilation"]; ?></div>
          </div>
          <div class="six columns">
            Execution (ghdl -r) says:<br>
            <div class="output"><?php echo $report["execution"]; ?></div>
          </div>
        </div>
      </h5>
<?php } ?>
      <br>
      <br>
      <br>
      <br>
      <br>
      <br>
      <br>
      <i>
        © 2019
        <a href="//oisumida.rs" target="_blank">Bruno Borges Paschoalinoto</a>
        <br>
				Some rights reserved under the MIT License.
				<a href="//github.com/Bruno02468/ghdlfiddle">Check out the code!</a>
      </i>
      <br>
      <br>
		</div>
  </body>
</html>
