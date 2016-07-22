<?php

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Synapse\FactoryController;
use \Glial\Security\Crypt\Crypt;

$converter = new AnsiToHtmlConverter();

echo '<form action="" method="POST">';

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';

echo '<th>' . __("Top") . '</th>';
echo '<th>' . __("ID") . '</th>';
echo '<th>' . __("Available") . '</th>';
echo '<th><input id="checkAll" type="checkbox" onClick="toggle(this)" /> ' . __("Monitored") . '</th>';
echo '<th>' . __("Client") . '</th>';
echo '<th>' . __("Environment") . '</th>';

echo '<th>' . __("Name") . '</th>';
echo '<th>' . __("IP") . '</th>';
echo '<th>' . __("Port") . '</th>';
echo '<th>' . __("User") . '</th>';
echo '<th>' . __("Password") . '</th>';
//echo '<th>'.__("Hostname").'</th>';
echo '<th>' . __("Version") . '</th>';
echo '<th>' . __("Date refresh") . '</th>';



echo '<th style="max-width:500px">' . __("Error") . '</th>';
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
    echo '<td style="' . $style . '">' . '<input type="checkbox" name="monitored['.$server['id'].']" '.($server['is_monitored'] == 1 ? 'checked="checked"' : '').'" />' . '</td>';
    echo '<td style="' . $style . '">' . $server['client'] . '</td>';
    echo '<td style="' . $style . '">' . $server['environment'] . '</td>';
    echo '<td style="' . $style . '"><a href="'.LINK.'server/listing/id/'.$server['id'].'">' . str_replace('_', '-', $server['name']) . '</a></td>';
    echo '<td style="' . $style . '">' . $server['ip'] . '</td>';
    echo '<td style="' . $style . '">' . $server['port'] . '</td>';
    echo '<td style="' . $style . '">' . $server['login'] . '</td>';

    Crypt::$key = CRYPT_KEY;


    

    $passwd = Crypt::decrypt($server['passwd']);

    //echo '<td style="' . $style . '">' . $server['passwd'] . '</td>';
    echo '<td style="' . $style . '">' . $passwd . '</td>';
    //echo '<td style="' . $style . '">' . '***' . '</td>';
    //echo '<td style="'.$style.'">'.$server['hostname'].'</td>';



    echo '<td style="' . $style . '">' . $server['version'] . '</td>';
    echo '<td style="' . $style . '">' . $server['date_refresh'] . '</td>';

    echo '<td style="max-width:600px;' . $style . '" class="">';

    if (strstr($server['error'], '[0m') || strstr($server['error'], 'Call Stack:')) {
        $converter = new AnsiToHtmlConverter();
        $html = $converter->convert($server['error']);



        echo '<pre style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;">' . $html . '</pre>';
//$server['error'];
    } else {
        echo str_replace("\n", '<br>', trim($server['error']));
    }
    echo '</td>';
    echo '</tr>';
}

echo '</table>';


echo '<input type="hidden" name="is_monitored" value="1" />';
echo '<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Update</button>';

echo '</form>';