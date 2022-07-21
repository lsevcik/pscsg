<?php
include_once 'database.php';
$db = new GradesDatabase();
if (isset($_GET['action']) && $_GET['action'] == 'export') {
  header("Content-disposition: attachment; filename=export.csv");
  print($db->exportCSV());
  exit();
}
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
    <p><a href="?action=export">Export</a></p>
    <p><a href="?action=import">Import</a></p>
  </main>
</body>
</html>
