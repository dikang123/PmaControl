<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \Glial\Synapse\FactoryController;
?>



    <?php 
    
    
    if ($data['auth'] !== 1) {
        FactoryController::addNode("Menu", "show",array("2"));
    }
    ?>

</body>