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
    
    //print_r($_GET);
    //echo $data['sql'];
    
    echo '<form class="form-inline" action="" method="post">';
    echo ' <div class="form-group" role="group" aria-label="Default button group">';
        
    echo __("Server : ");
    echo ' ';
    echo Form::select("mysql_server","id",$data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"auto"));
    echo ' ';
    
    echo Form::select("mysql_status_name","id",$data['status'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"auto"));
    echo ' ';
    
    echo Form::select("mysql_status_value_int","date",$data['interval'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"auto"));
    
    echo ' Derivate : ';
    
    echo Form::select("mysql_status_value_int","derivate",$data['derivate'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"auto"));
    
    
    echo ' <button type="submit" class="btn btn-primary">' . __("Filter") . '</button>';
    
    echo '</div>';
    echo '</form>';
    ?>  
</div>


<!--
<div style="height:600px; width:1600px">
<canvas id="myChart" height="500" width="1600"></canvas>
</div>
-->

<canvas style="width: 100%; height: 450px;" id="myChart" height="450" width="1600"></canvas>
