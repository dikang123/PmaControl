<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

echo '<div>';
echo '<form style="display:inline" action="" method="post">';
echo __("Client") . " : ";
echo Form::select("client", "libelle", $data['client'], "", array("data-live-search" => "true", "class" => "selectpicker", "multiple" => "multiple"));
echo " - " . __("Environment") . " : ";
echo Form::select("environment", "libelle", $data['environment'],"", array("data-live-search" => "true", "class" => "selectpicker", "multiple" => "multiple"));
echo ' <button type="submit" class="btn btn-primary">' . __("Filter") . '</button>';
echo '</form>';
echo '</div>';
