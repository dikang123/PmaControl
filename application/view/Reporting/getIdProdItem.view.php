<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Html\Form\Form;

use Glial\Html\Form\Upload;

echo '<div class="well">';

echo '<form action="" method="post" class="form-inline">';
echo '<div class="form-group">';
echo Form::input("PROD_TRACES", "IPROD_ITEM_ID", array("placeholder" => "IPROD_ITEM_ID", "class" => "form-control"));
echo Form::input("PROD_TRACES", "IPROD_INSTANCE_ID", array("placeholder" => "IPROD_INSTANCE_ID", "class" => "form-control"));
echo '<button type="submit" class="btn btn-primary">Search</button>';
echo '</div>';
echo '</form>';
echo '</div>';


if (!empty($data['id_prod_item'])) {
    foreach ($data['id_prod_item'] as $id_prod_items) {
        echo "<b>ID_PROD_ITEM : </b>" . $id_prod_items['ID_PROD_ITEM'] . "<br />";
    }

    echo '<br />';
}


echo '<div class="well">';

echo "<b>".__("200 lines maximum, if more the lines will be truncated !")."</b><br />";

echo '<form action="" method="post">';


$value = '';
if (!empty($_GET['excelidprod'])) {
    $value = str_replace("|", "\n", $_GET['excelidprod']);
}

echo '<textarea name="excelidprod" class="form-control" rows="10" placeholder="'. "IPROD_ITEM_ID    IPROD_INSTANCE_ID\nor\nIPROD_ITEM_ID,IPROD_INSTANCE_ID". '">' . $value . '</textarea>';
echo '<button type="submit" class="btn btn-primary">Search</button>';

echo '</form>';
echo '</div>';



if (!empty($data['excelidprod'])) {
    echo '<div class="well">';
    echo '<table class="table">';
    echo '<tr>';
    echo '<th>Top</th>';
    echo '<th>ID_PROD_ITEM</th>';
    echo '<th>IPROD_ITEM_ID</th>';
    echo '<th>IPROD_INSTANCE_ID</th>';
    echo '</tr>';
    
    $i=1;
    foreach ($data['excelidprod'] as $id_prod_items) {
        
        
        preg_match_all("/insertion depuis Iways, iprodItemid=([0-9]+), iprodIntsanceId=([0-9]+)/", $id_prod_items['COMMENTAIRES'], $output_array);

        echo '<tr>';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $id_prod_items['ID_PROD_ITEM'] . '</td>';
        echo '<td>'.$output_array[1][0].'</td>';
        echo '<td>'.$output_array[2][0].'</td>';

        echo '</tr>';
        
        $i++;
    }
    echo '</table>';

    echo '</div>';
}



echo '<div class="well">';

echo '<form action="" method="POST" enctype="multipart/form-data">';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="41943040" />';
echo "<b>".__("Send a CSV file formated as follow :")."</b><br /><br />";


echo "IPROD_ITEM_ID,IPROD_INSTANCE_ID<br />";
echo "IPROD_ITEM_ID,IPROD_INSTANCE_ID<br />";
echo "IPROD_ITEM_ID,IPROD_INSTANCE_ID<br /><br />";



echo '(Max size : '.Upload::formatBytes(Upload::getMaxUploadSize()).') <input type="file" name="filecsv" />';
echo '<button type="submit" class="btn btn-primary">Search</button>';

echo '</form>';


echo '</div>';