<?php
const ARROW = ["ASC" => "▲", "DESC" => "▼"];
const COLS = ['ip', 'hostname', 'os', 'state', 'services'];
$sortBy = 'ip';
$sortDir = 'ASC';
if (isset($_GET['sortBy']) && in_array($_GET['sortBy'], COLS))
  $sortBy = $_GET['sortBy'];
if (in_array(isset($_GET['sortDir']) && $_GET['sortDir'], ['ASC', 'DESC']))
  $sortDir = $_GET['sortDir'];
function comparator($a, $b) {
  global $sortBy, $sortDir;
  if (is_array($a[$sortBy]) && is_array($b[$sortBy]))
    return comparator(reset($a[$sortBy]), reset($b[$sortBy]));
  if ($sortDir === 'ASC')
    return strnatcmp($a[$sortBy], $b[$sortBy]);
  return strnatcmp($b[$sortBy], $a[$sortBy]);
}
include_once 'nmap_view.inc';
$files = glob("./nmap_results/*.xml");
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="common.css">
</head>
<body>
  <?php include 'navigation.inc'; ?>
  <main>
<?php
if (isset($_GET['view'])) {
  $hosts = nmap_view();
  uasort($hosts, 'comparator');
}
?>
    <span id="buttons"></span>
    <p>
      <form method="POST" action="nmap_run.php" sync="true">
        <input type="text" name="subject" placeholder="0.0.0.0/0">
        <label title="Only scan top 100 ports"><input type="checkbox" name="fast" checked>Fast</label>
        <label title="Try to guess which OS version is running (takes much longer)"><input type="checkbox" name="detectos" checked>OS Detection</label>
        <label title="Try to guess which software versions are running (takes longer)"><input type="checkbox" name="detectsoft" checked>Software Detection</label>
        <button style="width: auto">Start Run</button>
      </form>
    </p>
    <table>
      <tr>
        <th>#</th>
        <th>Previous Run</th>
        <th>Action</th>
      </tr>
<?php foreach ($files as $i => $file): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= basename($file) ?></td>
        <td>
          <button class="btn-view">View</button>
          <form method="POST" action="unlinksafe.php">
            <input type="hidden" name="file" value="<?= htmlspecialchars(basename($file)) ?>">
            <button class="btn-delete" onclick="confirm('Are you sure you want to delete this?') || event.preventDefault()">Delete</button>
          </form>
        </td>
      </tr>
<?php endforeach ?>
    </table>
    <br>
    <table>
      <tr>
        <th>#</th>
<?php foreach (COLS as $col): ?>
        <th <?= $col == $sortBy ? 'class="sortIndex"' : '' ?>  onclick="sortby(this)">
          <span><?= $col ?></span>
  <?= $col == $sortBy ? ARROW[$sortDir] : '' ?>
        </th>
<?php endforeach ?>
      </tr>
<?php $i = 0; if (isset($_GET['view'])) foreach ($hosts as $host): ?>
      <tr>
        <td><?= ++$i ?></td>
        <td><?= $host['ip'] ?></td>
        <td><?= $host['hostname'] ?></td>
        <td><?= $host['os'] ?></td>
        <td><?= $host['state'] ?></td>
        <td><?= $host['services'] ?></td>
      </tr>
<?php endforeach ?>
    </table>
  </main>
  <script>
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
var sortDir = urlParams.get('sortDir');
var sortBy = urlParams.get('sortBy');
let vbtns = document.getElementsByClassName("btn-view");

function sortdir() {
  if (sortDir == "ASC")
    urlParams.set('sortDir', "DESC");
  else
    urlParams.set('sortDir', "ASC");
  window.location.search = urlParams;
}

function sortby(el) { // el is th
  let field = el.querySelector('span').innerText
  if (field == sortBy && String(window.location).search("sortBy") != -1)
    return sortdir()
  urlParams.set('sortBy', field);
  urlParams.set('sortDir', "ASC");
  window.location.search = urlParams;
}

function changeClass(el, name) { // el is td
  el.parentElement.className = name
}


for (let i = 0; i < vbtns.length; ++i)
  vbtns.item(i).addEventListener("click", (e) => window.location.search = "?view=" + e.target.parentElement.parentElement.children[1].innerText);

document.addEventListener('submit', (e) => {
  const form = e.target
  if (form.attributes?.sync?.value === "true")
    return

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
