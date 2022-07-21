<?php
include 'database.php';
$db = new GradesDatabase();
const ACTIONS = ["CREATE", "READ", "UPDATE", "DELETE"];
$action = $_POST['verb'];

if (isset($_FILES['data'])) {
  $files = array_keys($_FILES['data']['name']);
  foreach ($files as $file)
    if (file_exists($_FILES['data']['tmp_name'][$file]))
      $_POST['data'][$file] = file_get_contents($_FILES['data']['tmp_name'][$file]);
}

$data = $_POST['data'];

if (!in_array($action, ACTIONS))
  throw new Exception("No such action {$_POST['action']}");
$res = false;
try {
  switch ($action) {
    case "CREATE":
      $res = $db->createRow($data);
      break;
    case "READ":
      $res = $db->readRow($data);
      break;
    case "UPDATE":
      $name = $data["unique"];
      unset($data["unique"]);
      $res = $db->updateRow($name, $data);
      break;
    case "DELETE":
      $res = $db->deleteRow($data);
      break;
  }
} catch (\Throwable $e) {
  json_encode(["success" => false, "error" => $e->getMessage()]);
}
if ($res !== true)
  print(json_encode(["success" => false, "error" => $res]));
else
  print(json_encode(["success" => true]));
