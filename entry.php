<?php
/*
include_once 'database.php';
$db = new GradesDatabase();

if (isset($_POST["name"]) && isset($_POST["grade"]) && isset($_POST["phone"])) {
  $name = $_POST["name"];
  $grade = strtoupper($_POST["grade"]);
  $phone = $db::extractPhone($_POST["phone"]);
  $res = $db->addRow(["name" => $name, "grade" => $grade, "phone" => $phone]);
  if ($res !== true)
    die(print_r($db->errorInfo(), 1));
}
 */
?>
<!DOCTYPE html>
<html>
<head>
  <link href="common.css" rel="stylesheet">
</head>
<body>
  <?php include 'navigation.inc' ?>
  <main>
    <h1>Data Entry <small>(deprecated)</small></h1>
    <form method="post">
      <label for="name">Name: </label><br>
      <input type="text" name="name" required><br>
      <label for="name">Letter Grade: </label><br>
      <input type="text" name="grade" maxlength="1" pattern="[A-Fa-f]" required><br>
      <label for="name">Phone Number: </label><br>
      <input type="text" name="phone" maxlength="15" pattern="\d{3}.*?\d{3}.*?\d{4}" required><br>
      <button action="submit">Submit</button>
    </form>
  </main>
</body>
</html>
