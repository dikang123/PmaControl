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

        echo '<a href="'.$elem['path'].'" type="button" class="btn '.$color.'" style="font-size:12px">'
        .' '.$elem['icone'].' '.__($elem['name']).'</a>';
    }
    echo '</div>';
    ?>
</div>

<?php


$elems = explode('/',$_GET['path']);
$method = end($elems);

\Glial\Synapse\FactoryController::addNode("Server", $method, array());
