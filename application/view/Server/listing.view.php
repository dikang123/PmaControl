<?php

use \Glial\Synapse\FactoryController;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
echo '<table class="table" id="table">';


echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Available").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("IP").'</th>';
echo '<th>'.__("Hostname").'</th>';
echo '<th>'.__("version").'</th>';

echo '<th>'.__("Operations system").'</th>';
echo '<th>'.__("Product name").'</th>';
echo '<th>'.__("Arch").'</th>';
echo '<th>'.__("Kernel").'</th>';
echo '<th>'.__("Processor").'</th>';
echo '<th>'."Mhz".'</th>';

echo '<th>'.__("Memory").'</th>';
echo '<th title="0.75*CPU*GHZ + 0.5 Memory Go">'.__("Indice").'</th>';
echo '</tr>';


$i = 0;
foreach ($data['servers'] as $server) {
    $i++;

    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$server['id'].'</td>';
    echo '<td>';
    echo '<span class="glyphicon '.($server['is_available'] == 1 ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden="true"></span>';
    echo '</td>';
    echo '<td>'.str_replace('_', '-', $server['name']).'</td>';
    echo '<td>'.$server['ip'].'</td>';
    echo '<td>'.$server['hostname'].'</td>';



    echo '<td class="">'.$server['version'].'</td>';

    echo '<td>'.$server['operating_system'].'</td>';

    echo '<td>'.$server['product_name'].'</td>';


    $class = ("i686" == $server['arch']) ? "error" : "";
    echo '<td class="'.$class.'">'.$server['arch'].'</td>';
    echo '<td>'.$server['kernel'].'</td>';

    echo '<td>'.$server['processor'].'</td>';
    echo '<td>'.$server['cpu_mhz'].'</td>';
    echo '<td>'.round($server['memory_kb'] / 1024 / 1024, 2).' Go</td>';
    echo '<td>'.round(0.75 * $server['processor'] * ($server['cpu_mhz'] / 1024) + 0.5 * ($server['memory_kb'] / 1024 / 1024), 2).'</td>';

    echo '</tr>';
}

echo '</table>';



//FactoryController::addNode("Agent", "index", array());