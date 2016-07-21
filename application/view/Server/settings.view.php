<?php

use Glial\Html\Form\Form;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<form action="" method="POST">';


echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('MySQL').'</th>';
echo '<th>'.__('SSH').'</th>';
echo '<th><input id="checkAll" type="checkbox" onClick="toggle(this)" /> '.__("Monitored").'</th>';

//echo '<th>'.__('Monitored').'</th>';
echo '<th>'.__('Client').'</th>';
echo '<th>'.__('Environment').'</th>';
echo '<th>'.__('Tags').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('Display name').'</th>';
echo '<th>'.__('IP').'</th>';
echo '<th>'.__('Port').'</th>';

echo '</tr>';

$i = 0;
$style = '';

Form::setIndice(true);

foreach ($data['servers'] as $server) {
    
    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$server['id'].'</td>';

    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.(empty($server['error']) ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden="true"></span>';
    echo '</td>';

    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.(empty($server['ssh_available']) ? "glyphicon-remove" : "glyphicon-ok").'" aria-hidden="true"></span>';
    echo '</td>';

    echo '<td style="'.$style.'">'.'<input type="checkbox" name="monitored['.$server['id'].']" '.($server['is_monitored'] == 1 ? 'checked="checked"'
            : '').'" />'.'</td>';
    
    echo '<td>';
    echo Form::select("client", "libelle", $data['clients'], $server['id_client'], array());
    echo '</td>';


    echo '<td>';
    echo Form::select("environment", "libelle", $data['environments'], $server['id_environment'], array());
    echo '</td>';
    echo '<td>'.__('Tags').'</td>';
    echo '<td>'.$server['name'].'</td>';

    echo '<td>'.Form::input("mysql_server", "display_name").'</td>';
    echo '<td>'.$server['ip'].'</td>';
    echo '<td>'.$server['port'].'</td>';
    echo '</tr>'."\n";
}

Form::setIndice(false);

echo '</table>';
echo '<button type="submit" class="btn btn-primary">'.__("Update").'</button>';
echo '<form>';
