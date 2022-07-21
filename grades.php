<?php
include_once 'database.php';

$db = new GradesDatabase();
$sortBy = NULL;
$sortDir = NULL;
const ARROW = ["ASC" => "▲", "DESC" => "▼"];
$cols = $db->getCols()->fetchAll(PDO::FETCH_COLUMN, 0);

if (isset($_GET["sortBy"]))
  $sortBy = $_GET["sortBy"];

if (isset($_GET["sortDir"]) && $_GET["sortDir"] == "ASC")
  $sortDir = GradesDatabase::Ascending;
else
  $sortDir = GradesDatabase::Descending;

$sth = $db->selectAll($sortBy, $sortDir);

$types = [];
foreach (get_declared_classes() as $c)
  if (is_subclass_of($c, 'AbstractRowType'))
    $types[] = $c;
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
    <table>
      <tr>
        <th>#</th>
<?php foreach ($cols as $col): ?>
          <th <?= $col == $sortBy ? 'class="sortIndex"' : '' ?>  onclick="sortby(this)">
            <span onauxclick="changeClass(this, 'delete')"><?= htmlspecialchars($col) ?></span><?= $col == $sortBy ? ARROW[$sortDir] : '' ?>
            <form action="col_handler.php" method="POST">
              <input type="hidden" name="verb" value="DELETE">
              <input type="hidden" name="data[name]" value="<?= htmlspecialchars($col) ?>">
              <button type="Submit" onclick="confirm('Are you sure you want to delete this?') || event.preventDefault()">Delete</button>
            </form>
        </th>
<?php endforeach ?>
        <th>
          <button onclick="changeClass(this, 'write')">New Col</button>
          <form action="col_handler.php" method="POST">
            <input type="hidden" name="verb" value="CREATE">
            <input name="data[name]" placeholder="name" required>
            <select name="data[type]">
<?php foreach ($types as $t): ?>
              <option><?= $t ?></option>
<?php endforeach ?>
            </select>
            <button type="submit">Submit</button>
          </form>
        </th>
      </tr>
<?php $i = 0; while ($row = $sth->fetch()): ?>
      <tr class="read">
        <form action="row_handler.php" method="POST" id="update-<?= ++$i ?>">
          <input type="hidden" name="verb" value="UPDATE">
          <input type="hidden" name="data[unique]" value="<?= htmlspecialchars($row["name"]) ?>">
          <td><?= $i ?></td>
  <?php foreach ($cols as $col): ?>
          <td ondblclick="changeClass(this, 'write')">
            <span>
    <?= $db->getRowTypeByName($col)->displayData($row[$col]) ?>
            </span>
    <?= $db->getRowTypeByName($col)->getHTMLInput("data[$col]", $row[$col] ? $row[$col] : "") ?>
          </td>
  <?php endforeach ?>
        </form>
          <td>
            <form action="row_handler.php" method="POST">
              <input type="hidden" name="verb" value="DELETE">
              <input type="hidden" name="data[name]" value="<?= htmlspecialchars($row["name"]) ?>">
              <button type="submit" class="delete" onclick="confirm('Are you sure you want to delete this?') || event.preventDefault()">Delete</button>
            </form>
            <button type="submit" class="update" form="update-<?= $i ?>">Submit</button>
          </td>
      </tr>
<?php endwhile ?>
      <tr>
        <td><?= ++$i ?></td>
        <form action="row_handler.php" method="POST">
          <input type="hidden" name="verb" value="CREATE">
<?php foreach ($cols as $col): ?>
          <td><?= $db->getRowTypeByName($col)->getHTMLInput("data[$col]") ?></td>
<?php endforeach ?>
          <td><button type="submit">Submit</button></td>
        </form>
      </tr>
    </table>
  </main>
  <script>
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
var sortDir = urlParams.get('sortDir');
var sortBy = urlParams.get('sortBy');

function sortdir() {
  if (sortDir == "ASC")
window.location = "grades.php?sortBy=" + sortBy + "&sortDir=DESC"
  else
window.location = "grades.php?sortBy=" + sortBy + "&sortDir=ASC"
}

function sortby(el) { // el is th
  let field = el.querySelector('span').innerHTML
  if (field == sortBy && String(window.location).search("sortBy") != -1)
    return sortdir()
  else
    window.location = "grades.php?sortBy=" + field
}

function changeClass(el, name) { // el is td
  el.parentElement.className = name
}

document.addEventListener('submit', (e) => {
  const form = e.target;

  fetch(form.action, {
    method: form.method || "GET",
    body:   new FormData(form),
  }).then((res) => res.status != 200)
    .then((err) => err ? alert("There was an error saving your changes") : location.reload())

  e.preventDefault()
})

  </script>
</body>
</html>
