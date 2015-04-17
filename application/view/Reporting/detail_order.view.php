<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Form\Form;


echo '<div class="well">';


echo '<form action="" method="POST">';


$nb_elem_by_col = ceil(count($data['server']) / 6);

$i = 0;
foreach ($data['server'] as $db_name) {



    if ($i == 0) {
        echo '<ul class="col">';
    } elseif ($i % $nb_elem_by_col == 0) {
        echo '</ul><ul  class="col">';
    }

    $i++;


    echo '<li>';
    
    echo '<table><tr><td>';
    echo Form::radio("none", "server", $db_name['name'], 'bi_stage_sa');
    
    echo '</td><td>';
    //<input type="radio" id="' . $db_name['name'] . '" name="server" value="' . $db_name['name'] . '" />'
    echo '<label for="' . $db_name['name'] . '">&nbsp;' . str_replace('_', '-', $db_name['name']) . "</label></td></tr></table></li>";
}

echo '</ul>';
echo '<div style="clear:both"></div>';

echo '<br />';
echo 'ID_COMMANDE : (one ID by line, or separated with coma)<br/>';

//echo '<textarea name="" style="width:100%; height:300px"></textarea>';

echo Form::textarea('PROD_TRACES', 'ID_COMMANDE', 'photobox');



echo '<input type="submit" name="" class="btn btn-primary" value="Execute" />';

echo '</form>';
echo '<br />';
echo '<br />';
if (!empty($data['ret'])) {
    echo "<b>Script to play : </b><br />";
    echo $data['ret'];
}



if (!empty($data['commandes'])) {

    echo '<table class="table">';
    echo '<tr>';
    echo '<th rowspan="2">Top</th>';
    echo '<th rowspan="2">ID_COMMANDE</th>';
    echo '<th colspan="3">Arvato</th>';
    echo '<th colspan="3">Autologos</th>';


    echo '<tr>';
    echo '<th>DATE_PASSAGE</th>';
    echo '<th>LIGNE_PROD</th>';
    echo '<th>COMMENTAIRES</th>';
    echo '<th>DATE_PASSAGE</th>';
    echo '<th>LIGNE_PROD</th>';
    echo '<th>COMMENTAIRES</th>';
    echo '</tr>';


    $id_commande = '';
    $i=0;

    foreach ($data['commandes'] as $command) {
        if ($id_commande != $command['ID_COMMANDE']) {

            
            if ($id_commande != '') {
                echo '</tr>';
            }
            
            $i++;
            echo '<tr>';
            echo '<td>' . $i . '</td>';
            echo '<td>' . $command['ID_COMMANDE'] . '</td>';
        }

        echo '<td>' . $command['DATE_PASSAGE'] . '</td>';
        echo '<td>' . $command['LIGNE_PROD'] . '</td>';
        echo '<td>' . $command['COMMENTAIRES'] . '</td>';

        $id_commande = $command['ID_COMMANDE'];
    }

    echo '</table>';
}



echo '</div>';

