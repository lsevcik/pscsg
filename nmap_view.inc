<?php
function nmap_view() {
  $hosts = [];
  $file = basename($_GET['view']);
  if (!($document = @simplexml_load_file("./nmap_results/" . $file))) {
    echo "Run not completed or corrupted. Please refresh in a minute";
    return $hosts;
  }
  foreach ($document as $i => $e) {
    if ($i != "host")
      continue;

    $sIp = "";
    $sHostname = "";
    $sOs = "";
    $sState = "";
    $sServices = "";

    $sIp .= $e->address['addr'];

    if (isset($e->hostnames->hostname['name']) && isset($e->hostnames->hostname['type']))
      $sHostname .= '<span title="' . $e->hostnames->hostname['type'] . '">' . $e->hostnames->hostname['name'] . "</span>";

    if (isset($e->os->osmatch)) {
      $oscount = 0;
      foreach ($e->os->osmatch as $os) {
        if (!empty($os['accuracy']))
          $sOs .= '<div title="' . $os['accuracy'] . '% Confidence">';
        else
          $sOs .= '<div>';
        $sOs .= $os['name'] . '</div>';
        if (++$oscount > 5)
          break;
      }
    }

    $sState .= '<span title="' . $e->status['reason'] . '">' . $e->status['state'] . '</span>';

    if (isset($e->ports->port))
      foreach ($e->ports->port as $i => $p) {
        if (isset($p->service['version']))
          $sServices .= '<span title="' . $p->service['product'] . ' ' . $p->service['version'] . '">';
        else
          $sServices .= '<span>';
        $sServices .= $p['portid'] . '/' . $p->service['name'] . '</span> ';
      }


    $hosts[] = [
      "ip" => $sIp,
      "hostname" => $sHostname,
      "os" => $sOs,
      "state" => $sState,
      "services" => $sServices,
    ];
  }
  return $hosts;
}
