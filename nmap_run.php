<?php
$now = new DateTimeImmutable();
if (empty($_POST['subject']))
  die("no subject");
$subject = $_POST['subject'];
if (preg_match("/(\d{3})\\.?(\d{3})\\.?(\d{3})\\.?(\d{3})\\/?(\d{2})/", $subject))
  die("subject did not match expected format: 0.0.0.0/0");
$isonow = $now->format(DateTimeInterface::ISO8601);
$output = [];
$return = 0;
$command = "sudo systemd-run --unit=nmap-run -d -G nmap -v -R -T5 --script-timeout 3m --host-timeout 1m -oX nmap_results/${isonow}.xml ${subject}";
if (isset($_POST['fast']))
  $command .= " -F";
if (isset($_POST['detectos']))
  $command .= " -O";
if (isset($_POST['detectsoft']))
  $command .= " -sV";
exec($command, $output, $return);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Logan's Page</title>
  <link rel="stylesheet" href="common.css">
</head>
<body>
  <?php include 'navigation.inc' ?>
  <main>
<?php if ($return != 0): ?>
    <p>Error starting. There is probably already a scan going on. Please look at the output below</p>
<?php endif ?>
    <div id="viewport" style="max-height: 80vh">
      <pre id="output">
      </pre>
      <span id="bottom"></span>
    </div>
  </main>
  <script>
function getStuff() {
  fetch("nmap_wait.php")
  .then((res) => res.json())
  .then((body) => {
    document.querySelector('pre').innerText = ""
    body.forEach((e, i) => {
      if (e == "done")
        window.location = "nmap.php?view=<?= $isonow ?>.xml"
      document.querySelector('pre').innerText += e + "\n"
      document.getElementById('bottom').scrollIntoView()
    }
  )})
}
getStuff();
setInterval(getStuff, 10 * 1000);
  </script>
</body>
</html>
