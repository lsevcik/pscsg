<?php
include 'database.php';
$db = new GradesDatabase();
const ACTIONS = ["CREATE", "DELETE"];
$action = $_POST['verb'];
$data = $_POST['data'];

if (!in_array($action, ACTIONS))
  throw new Exception("No such action {$_POST['action']}");
$res = false;
try {
  switch ($action) {
    case "CREATE":
      $res = $db->addCol($data['name'], $data['type']);
      break;
    case "DELETE":
      $res = $db->deleteCol($data);
      break;
  }
} catch (\Throwable $e) {
  json_encode(["success" => false, "error" => $e->getMessage()]);
}
if ($res !== true)
  print(json_encode(["success" => false, "error" => $res]));
else
  print(json_encode(["success" => true]));
