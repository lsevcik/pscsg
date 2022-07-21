<?php
if (empty($_POST['file']))
  die("no file");
$file = basename($_POST['file']);
unlink("nmap_results/$file");
