<?php

use \Glial\Synapse\FactoryController;
use Glial\Html\Form\Form;
?>
<div class="well">
    <?php
    
        echo '<div>';
        echo '<form style="display:inline" action="" method="post">';
    echo  __("Client") . " : ";
    
    echo Form::select("client", "libelle", $data['client'], "", array("data-live-search" => "true", "class" => "selectpicker"));


    echo " - " . __("Environment") . " : ";
    
    echo Form::select("client", "libelle", $data['environment'], "", array("data-live-search" => "true", "class" => "selectpicker"));
    
    echo ' <button type="submit" class="btn btn-primary">' . __("Filter") . '</button>';
    echo '</form>';
    echo '</div>';
    echo '<br />';
    
    
    
    
    echo '<div>';
    echo ' <div class="btn-group" role="group" aria-label="Default button group">';


    unset($data['menu']['logs']);

    foreach ($data['menu'] as $key => $elem) {
        if ($_GET['path'] == $elem['path']) {
            $color = "btn-primary";
        } else {
            $color = "btn-default";
        }

        echo '<a href="' . $elem['path'] . '" type="button" class="btn ' . $color . '" style="font-size:12px">'
        . ' ' . $elem['icone'] . ' ' . __($elem['name']) . '</a>';
    }
    echo '</div>';

    

    echo '<div style="float:right" class="btn-group" role="group" aria-label="Default button group">';
    echo ' <a href="/pmacontrol/en/Cleaner/add/" class="btn btn-primary" style="font-size:12px"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a MySQL server</a> ';
    echo '</div>';



    echo ' <div style="float:right" class="btn-group" role="group" aria-label="Default button group">';

    
    

    
    echo '&nbsp;<a href="' . LINK . 'Agent/stop/' . $data['pid'] . '" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:12px"></span> Stop Daemon</a>';
    echo '<a href="' . LINK . 'Agent/start" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:12px"></span> Start Daemon</a>';

    if (empty($data['pid'])) {
        echo '<a href="' . LINK . 'Server/listing/logs" type="button" class="btn btn-warning" style="font-size:12px"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style="font-size:13px"></span> Stopped</a>';
    } else {
        $cmd = "ps -p " . $data['pid'];
        $alive = shell_exec($cmd);

        if (strpos($alive, $data['pid']) !== false) {
            echo '<a href="' . LINK . 'Server/listing/logs" type="button" class="btn btn-success" style="font-size:12px"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="font-size:12px"></span> Running (PID : ' . $data['pid'] . ')</a>';
        } else {
            echo '<a href="' . LINK . 'Server/listing/logs" type="button" class="btn btn-danger" style="font-size:12px"><span class="glyphicon glyphicon-remove" aria-hidden="true" style="font-size:12px"></span> Error</a>';
        }
    }
    echo '</div>';

    echo '</div>';
    
echo '</div>';




    $elems = explode('/', $_GET['path']);
    $method = end($elems);



    \Glial\Synapse\FactoryController::addNode("Server", $method, array());
    