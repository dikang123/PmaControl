<?php

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Synapse\FactoryController;
use \Glial\Security\Crypt\Crypt;

$converter = new AnsiToHtmlConverter();



echo '<table class="table table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>' . __("Top") . '</th>';
echo '<th>' . __("ID") . '</th>';
echo '<th>' . __("Available") . '</th>';
echo '<th>' . __("Name") . '</th>';
echo '<th>' . __("IP") . '</th>';
echo '<th>' . __("Port") . '</th>';
echo '<th>' . __("User") . '</th>';
echo '<th>' . __("Password") . '</th>';
//echo '<th>'.__("Hostname").'</th>';
echo '<th>' . __("Version") . '</th>';
echo '<th>' . __("Date refresh") . '</th>';

/*
  echo '<th>'.__("Operations system").'</th>';
  echo '<th>'.__("Product name").'</th>';
  echo '<th>'.__("Arch").'</th>';
  echo '<th>'.__("Kernel").'</th>';
  echo '<th>'.__("Processor").'</th>';
  echo '<th>'."Mhz".'</th>';

  echo '<th>'.__("Memory").'</th>';
  echo '<th title="0.75*CPU*GHZ + 0.5 Memory Go">'.__("Indice").'</th>';
 */

echo '<th>' . __("Error") . '</th>';
echo '</tr>';


$i = 0;
foreach ($data['servers'] as $server) {
    $i++;

    $style = "";
    if (empty($server['is_available'])) {
        $style = 'background-color:#d9534f; color:#FFFFFF';
    }


    echo '<tr>';
    echo '<td style="' . $style . '">' . $i . '</td>';
    echo '<td style="' . $style . '">' . $server['id'] . '</td>';
    echo '<td style="' . $style . '">';
    echo '<span class="glyphicon ' . ($server['is_available'] == 1 ? "glyphicon-ok" : "glyphicon-remove") . '" aria-hidden="true"></span>';
    echo '</td>';
    echo '<td style="' . $style . '">' . str_replace('_', '-', $server['name']) . '</td>';
    echo '<td style="' . $style . '">' . $server['ip'] . '</td>';
    echo '<td style="' . $style . '">' . $server['port'] . '</td>';
    echo '<td style="' . $style . '">' . $server['login'] . '</td>';

    Crypt::$key = CRYPT_KEY;
    $passwd = Crypt::decrypt($server['passwd']);

    echo '<td style="' . $style . '">' . $passwd . '</td>';
    //echo '<td style="'.$style.'">'.$server['hostname'].'</td>';



    echo '<td style="' . $style . '">' . $server['version'] . '</td>';
    echo '<td style="' . $style . '">' . $server['date_refresh'] . '</td>';

    echo '<td style="' . $style . '" class="">';

    if (strstr($server['error'], '[0m') || strstr($server['error'], 'Call Stack:')) {
        $converter = new AnsiToHtmlConverter();
        $html = $converter->convert($server['error']);



        echo '<pre style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;">' . $html . '</pre>';
//$server['error'];
    } else {
        echo str_replace("\n", '<br>', trim($server['error']));
    }
    echo '</td>';


    /*
      echo '<td style="'.$style.'">'.$server['operating_system'].'</td>';
      echo '<td style="'.$style.'">'.$server['product_name'].'</td>';
      $class = ("i686" == $server['arch']) ? "error" : "";
      echo '<td style="'.$style.'" class="'.$class.'">'.$server['arch'].'</td>';
      echo '<td style="'.$style.'">'.$server['kernel'].'</td>';
      echo '<td style="'.$style.'">'.$server['processor'].'</td>';
      echo '<td style="'.$style.'">'.$server['cpu_mhz'].'</td>';
      echo '<td style="'.$style.'">'.round($server['memory_kb'] / 1024 / 1024, 2).' Go</td>';
      echo '<td style="'.$style.'">'.round(0.75 * $server['processor'] * ($server['cpu_mhz'] / 1024) + 0.5 * ($server['memory_kb'] / 1024 / 1024), 2).'</td>';
     */

    echo '</tr>';
}

echo '</table>';

