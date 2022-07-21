<?php
if (!isset($_GET["key"]) || !isset($_GET["value"]) || !isset($_GET["unique"]))
  die("One of ?key ?value or ?unique not set");

$key = $_GET["key"];
$value = $_GET["value"];
$unique = $_GET["unique"];

$gradesDatabase = new PDO('mysql:dbname=school;host=localhost', 'root', 'fat cat');
$changeSTH = $gradesDatabase->prepare("UPDATE student_grades SET `$key` = :value WHERE `name` = :unique;");
$changeSTH->bindParam(":value", $value);
$changeSTH->bindParam(":unique", $unique);
if (!$changeSTH->execute() && $gradesDatabase->errorCode() != "00000")
    print_r($gradesDatabase->errorInfo());
else
    header("Location: grades.php");
