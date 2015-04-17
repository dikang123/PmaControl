<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Html\Form\Form;

echo '<div class="well well-sm">';

echo '<form action="" method="post">';
echo '<table class="table" id="table">';

echo '<tr>';
echo '<th colspan="2">' . __("Server") . '</th>';
echo '<th colspan="2">' . __("Database") . '</th>';
echo '<th rowspan="2">' . __("Storage area") . '</th>';
echo '<th rowspan="2">' . __("Tools") . '</th>';
echo '<th colspan="5">' . __("Shedule") . '</th>';
echo '<th rowspan="2" colspan="2">' . __("Actions") . '</th>';
echo '</tr>';


echo '<tr>';
echo '<th>' . __("ID") . '</th>';
echo '<th>' . __("Name") . '</th>';
echo '<th>' . __("IP") . '</th>';
echo '<th>' . __("Name") . '</th>';
//echo '<th>' . __("Size") . '</th>';

echo '<th>' . __("Minutes") . '</th>';
echo '<th>' . __("Hours") . '</th>';
echo '<th>' . __("Day of month") . '</th>';
echo '<th>' . __("Month") . '</th>';
echo '<th>' . __("Day of week") . '</th>';
echo '</tr>';

Form::setIndice(true);


$i = 0;
foreach ($data['backup_list'] as $backup_list) {
    echo '<tr class="edit">';
    echo '<td>' . $backup_list['id_backup_database'] . '</td>';
    echo '<td>' . str_replace("_", "-", $backup_list['server_name']) . '</td>';
    echo '<td>' . $backup_list['ip'] . '</td>';
    echo '<td>' . $backup_list['name'] . '</td>';
    //echo '<td>' . 0 . '</td>';
    echo '<td>' . $backup_list['nas'] . '</td>';
    echo '<td>' . $backup_list['backup_type'] . '</td>';
    echo '<td class="input">' . $backup_list['minutes'] . '</td>';
    echo '<td class="input">' . $backup_list['hours'] . '</td>';
    echo '<td class="input">' . $backup_list['day_of_month'] . '</td>';
    echo '<td class="input">' . $backup_list['month'] . '</td>';
    echo '<td class="input">' . $backup_list['day_of_week'] . '</td>';

    echo '<td>';

    if ($backup_list['is_active'] == "0") {
        $class = 'btn-warning';
        $icon = 'glyphicon-stop';
        $text = __("Desactived");
    } else {
        $class = 'btn-success';
        $icon = 'glyphicon-play';
         $text = __("Actived");
    }

    echo ' <a href="' . LINK . 'backup/toggleShedule/' . $backup_list['id_backup_database'] . '" class="btn '.$class.' delete-item"><span class="glyphicon '.$icon.'" style="font-size:12px"></span> ' . $text . '</a>';


    echo '</td>';
    echo '<td><a href="' . LINK . '/backup/deleteShedule/' . $backup_list['id_backup_database'] . '" class="btn btn-danger delete-item"><span class="glyphicon glyphicon-trash" style="font-size:12px"></span> ' . __("Delete") . '</a>';
    echo '</td>';
    echo '</tr>';
}


echo '<tr id="tr-' . $i . '" class="blah">';
echo '<td>#</td>';
echo '<td>' . Form::autocomplete("backup_database", "id_mysql_server", array("class" => "server form-control")) . '</td>';
echo '<td>' . Form::autocomplete("backup_database", "id_mysql_server_2", array("class" => "ip form-control", "style" => "width:150px")) . '</td>';
echo '<td>' . Form::select("backup_database", "id_mysql_database", $data['databases'], "", array("class" => "form-control")) . '</td>';
//echo '<td>' . 0 . '</td>';
echo '<td>' . Form::select("backup_database", "id_backup_storage_area", $data['storage_area'], "",array("class" => "form-control")) . '</td>';
echo '<td>' . Form::select("backup_database", "id_backup_type", $data['type_backup'], "",array("class" => "form-control")) . '</td>';
echo '<td>' . Form::input("crontab", "minutes", array("class" => "form-control", "style" => "width:40px")) . '</td>';
echo '<td>' . Form::input("crontab", "hours", array("class" => "form-control","style" => "width:40px")) . '</td>';
echo '<td>' . Form::input("crontab", "day_of_month", array("class" => "form-control", "style" => "width:40px")) . '</td>';
echo '<td>' . Form::input("crontab", "month", array("class" => "form-control", "style" => "width:40px")) . '</td>';
echo '<td>' . Form::input("crontab", "day_of_week", array("class" => "form-control","style" => "width:40px")) . '</td>';
echo '<td>';
echo '<td><a href="#" class="btn btn-danger delete-line"><span class="glyphicon glyphicon-trash" style="font-size:12px"></span> ' . __("Delete") . '</a>';
echo '</td>';
echo '</tr>';

Form::setIndice(false);

echo '</table>';

echo '<a href="#" id="add" class="btn btn-primary"><span class="glyphicon glyphicon glyphicon-plus" style="font-size:12px"></span> ' . __("Add a backup") . '</a>'
 . ' - <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> ' . __("Save") . '</button>';


echo '</form>';
echo '<div>';
