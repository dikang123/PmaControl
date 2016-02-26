<?php
use \Glial\Synapse\FactoryController;
?>
<div class="well">
    <?php
    echo ' <div class="btn-group" role="group" aria-label="Default button group">';

    foreach ($data['menu'] as $key => $elem) {




        if ($_GET['path'] == $elem['path']) {
            $color = "btn-info";
        } else {
            $color = "btn-primary";
        }

        echo '<a href="'.$elem['path'].'" type="button" class="btn '.$color.'" style="font-size:14px">'
        .' '.$elem['icone'].' '.__($elem['name']).'</a>';
    }



    echo '</div>';
    



echo ' <div class="btn-group" role="group" aria-label="Default button group">';
echo '<a href="'.LINK.'Agent/stop" type="button" class="btn btn-primary" style="font-size:14px"> <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:13px"></span> Stop Daemon</a>';
echo '<a href="'.LINK.'Agent/start" type="button" class="btn btn-primary" style="font-size:14px"> <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:13px"></span> Start Daemon</a>';

echo '<a href="#" type="button" class="btn btn-danger" style="font-size:14px"><span class="glyphicon glyphicon-remove" aria-hidden="true" style="font-size:13px"></span> Error</a>';
echo '<a href="#" type="button" class="btn btn-warning" style="font-size:14px"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style="font-size:13px"></span> Stopped</a>';
echo '<a href="#" type="button" class="btn btn-success" style="font-size:14px"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="font-size:13px"></span> Running (PID : 3227)</a>';

echo '</div>';


echo ' <div class="btn-group" role="group" aria-label="Default button group">';
echo '<a href="/pmacontrol/en/Cleaner/add/" class="btn btn-primary" style="font-size:14px"><span class="glyphicon glyphicon-plus" style="font-size:14px"></span> Add a MySQL server</a>';
echo '</div>';

echo '</div>';



$elems = explode('/',$_GET['path']);
$method = end($elems);

\Glial\Synapse\FactoryController::addNode("Server", $method, array());
