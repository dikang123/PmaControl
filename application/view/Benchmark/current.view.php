<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table class="table table-condensed table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Date launched").'</th>';
echo '<th>'.__("Name server").'</th>';
echo '<th>'.__("Date started").'</th>';
echo '<th>'.__("Date ended").'</th>';
echo '<th>'.__("Threads").'</th>';
echo '<th>'.__("Tables_count").'</th>';
echo '<th>'.__("Max time").'</th>';
echo '<th>'.__("Mode").'</th>';
echo '<th>'.__("Read only").'</th>';
echo '<th>'.__("Status").'</th>';


echo '</tr>';


$i = 1;
foreach ($data['current'] as $line) {

    $class = "";
    if ($line['status'] === "RUNNING") {
        $class = 'class="progress-bar-striped" style="background:#ccc;  border-bottom:#333 1px solid"';
    } elseif ($line['status'] === "NOT STARTED") {
        $class = 'class="progress-bar-striped" style="background:#ddd;  border-bottom:#999 1px solid"';
    }

    if ($line['date_start'] === "0000-00-00 00:00:00") {
        $line['date_start'] = "N/A";
    }

    if ($line['date_end'] === "0000-00-00 00:00:00") {
        $line['date_end'] = "N/A";
    }

    echo '<tr>';
    echo '<td '.$class.'>'.$i.'</td>';
    echo '<td '.$class.'>'.$line['id_benchmark_main'].'</td>';
    echo '<td '.$class.'>'.$line['date'].'</td>';
    echo '<td '.$class.'>'.$line['name'].'</td>';
    echo '<td '.$class.'>'.$line['date_start'].'</td>';
    echo '<td '.$class.'>'.$line['date_end'].'</td>';
    echo '<td '.$class.'>'.$line['threads'].'</td>';
    echo '<td '.$class.'>'.$line['tables_count'].'</td>';
    echo '<td '.$class.'>'.$line['max_time'].'</td>';
    echo '<td '.$class.'>'.$line['mode'].'</td>';
    echo '<td '.$class.'>'.$line['read_only'].'</td>';
    echo '<td '.$class.'>';

    if ($line['status'] === "RUNNING") {
        echo '<i class="fa fa-cog fa-spin" aria-hidden="true"></i> ';
    }

    echo $line['status'];

    if ($line['status'] === "RUNNING") {
        echo ' (50%)';
    }
    echo '</td>';

    echo '</tr>';

    $i++;
}


echo '<table>';
