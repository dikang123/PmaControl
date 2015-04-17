<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$english_date = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December',' year',' years', ' month(s)');
$french_date = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',' an', ' ans', ' mois');



foreach ($data['mission'] as $mission) {
    
    
//    $mission = str_ireplace($french_date, $english_date, $mission);
    
    echo $mission;
}