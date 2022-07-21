<?php
$output = [];
$return = 0;
$response = [];
usleep(500000);
exec("sudo systemctl status --output=cat -n 50 nmap-run.service", $output, $return);

if ($return != 0)
  die("[\"done\"]");

echo json_encode($output);
