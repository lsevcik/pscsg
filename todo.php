<?php
const FILE = "todo.txt";
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  file_put_contents(FILE, $_POST["textarea"]);
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Logan's Page</title>
  <link rel="stylesheet" href="common.css">
  <style>
textarea {
  margin: 0;
  border: 0;
  padding: 0;
  width: calc(100vw - 24px);
  height: calc(100vh - 55px - 12px);
  resize: none;
}

  </style>
</head>
<body>
  <?php include 'navigation.inc' ?>
  <main>
  <form id="form" method="post" action="todo.php">
    <textarea autofocus="true" name="textarea" spellcheck="true" autocorrect="true"><?= htmlspecialchars(file_get_contents(FILE)) ?></textarea>
  </form>
  </main>
  <script type="text/javascript">
document.addEventListener('input', (e) => {
  const form = document.getElementById("form");

  fetch(form.action, {
    method: form.method,
    body:   new FormData(form),
  }).then((res) => res.status != 200)
    .then((err) => err && alert("There was an error saving your changes"))

  e.preventDefault()
})
  </script>
</body>
</html>
