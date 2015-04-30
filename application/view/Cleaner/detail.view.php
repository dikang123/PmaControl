<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="row">';

/*
echo '<div class="col-md-6">';


echo '<div class="row">';
echo '<div class="col-md-4">' . __('Top') . '</div>';
echo '<div class="col-md-4">' . __('Table') . '</div>';
echo '<div class="col-md-4">' . __('Rows') . '</div>';
echo '</div>';

$i = 0;

foreach ($data['detail'] as $detail) {
    $i++;

    echo '<div class="row">';
    echo '<div class="col-md-4">' . $i . '</div>';
    echo '<div class="col-md-4">' . $detail['table'] . '</div>';
    echo '<div class="col-md-4">' . $detail['row'] . '</div>';
    echo '</div>';





    //var_dump($details);
}echo '</div>';*/


echo '<div class="col-md-6">';
echo '<table class="table">';
echo '<tr>';
echo '<th>' . __('Top') . '</th>';
echo '<th>' . __('Table') . '</th>';
echo '<th>' . __('Rows') . '</th>';

echo '</tr>';
$i = 0;
foreach ($data['detail'] as $detail) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td>' . $detail['table'] . '</td>';
    echo '<td>' . $detail['row'] . '</td>';

    echo '</tr>';


    //var_dump($details);
}
echo '</table>';
echo '</div>';

echo '<div class="col-md-6">';
echo '<table class="table">';
echo '<tr>';
echo '<th>' . __('Top') . '</th>';
echo '<th>' . __('Table') . '</th>';
echo '<th>' . __('Rows') . '</th>';

echo '</tr>';
$i = 0;
foreach ($data['avg'] as $detail) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td>' . $detail['table'] . '</td>';
    echo '<td>' . round($detail['row'],2) . '</td>';
    echo '</tr>';

    //var_dump($details);
}
echo '</table>';
echo '</div>';
echo '</div>';