<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\FactoryController;
use Glial\Html\Form\Form;
?>
<div class="well">
    
    <?php
    
    echo '<form class="form-inline" action="" method="post">';
    echo ' <div class="form-group" role="group" aria-label="Default button group">';
        
    echo __("Server : ");
    echo ' ';
    echo Form::select("mysql_server","id",$data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"auto"));
    echo ' ';
    
    echo Form::select("mysql_status_name","id",$data['status'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"auto"));
    
    
    echo ' <button type="submit" class="btn btn-primary">' . __("Filter") . '</button>';
    
    echo '</div>';
    echo '</form>';
    ?>
</div>





<canvas id="buyers" width="600" height="400"></canvas>

<canvas id="rice" width="400" height="400"></canvas>

<canvas id="canvas"></canvas>
<canvas id="skills"></canvas>



<?php


