<?php
enum DOW: int {
  case Sunday = 0;
  case Monday = 1;
  case Tuesday = 2;
  case Wednesday = 3;
  case Thursday = 4;
  case Friday = 5;
  case Saturday = 6;
}

$offsetw = new DateTime();
if (isset($_GET['offsetw']))
  $offsetw->sub(new DateInterval('P' . intval($_GET['offsetw']) . 'W'));

$now = DateTimeImmutable::createFromMutable($offsetw);

$last = [];
$lastb = [];

for ($i = 0; $i < 7; $i++) {
  $day = $now->sub(new DateInterval('P' . $i .'D'));
  $dow = DOW::from(intval($day->format("w")));
  $since = $day->format("Y-m-d");
  $until = $day->add(new DateInterval("P1D"))->format("Y-m-d");
  exec("last -Rw --since $since --until $until", $last[$dow->value]);
  exec("sudo lastb -w --since $since --until $until", $lastb[$dow->value]);
}

?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="common.css">
</head>
<body>
  <?php include 'navigation.inc'; ?>
  <main>
    <span id="buttons"></span>
    <table>
      <tr>
<?php foreach (DOW::cases() as $dow): ?>
        <th>
  <?= $dow->name ?>
        </th>
<?php endforeach ?>
      </tr>
      <tr>
<?php foreach (DOW::cases() as $dow): ?>
        <td style="vertical-align: top">
          <table>
            <tr>
              <th>#</th>
              <th>Log</th>
            </tr>
  <?php foreach ($last[$dow->value] as $k => $line): ?>
    <?php if ($k === sizeof($last[$dow->value]) - 2) break; ?>
            <tr>
              <th>
    <?= $k + 1 ?>
              </th>
              <td>
    <?= $line ?>
              </td>
            </tr>
  <?php endforeach ?>
  <?php foreach ($lastb[$dow->value] as $k => $line): ?>
    <?php if ($k === sizeof($lastb[$dow->value]) - 2) break; ?>
            <tr style="color:red">
              <th>
    <?= $k + 1 ?>
              </th>
            <td>
    <?= $line ?>
            </td>
          </tr>
  <?php endforeach ?>
          </table>
        </td>
<?php endforeach ?>
      </tr>
    </table>
  </main>
  <script>
let params = new URLSearchParams(window.location.search)
let offsetw = parseInt(params.get("offsetw") || 0)
let buttons = document.getElementById("buttons")

buttons.append(document.createElement("button").appendChild(document.createTextNode("<<")).parentElement)

if (offsetw > 0)
  buttons.append(document.createElement("button").appendChild(document.createTextNode(">>")).parentElement)

Array.from(buttons.children).forEach((e) => {
  e.addEventListener('click', (event) => {
    if (event.target.innerText == ">>")
      --offsetw
    else
      ++offsetw
    params.set("offsetw", offsetw)
    window.location.search = params
    event.preventDefault()
  })

})
  </script>
</body>
</html>
