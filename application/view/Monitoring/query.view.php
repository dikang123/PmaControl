<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Html\Form\Form;

echo '<div class="well">';
echo '<form action="" method="post" class="form-inline">';
echo '<div class="form-group">';

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array());
//echo Form::select('mysql_server', 'id', $data['server_mysql'], "", array('style' => 'margin-bottom:0px', 'class' => 'form-control'));

//class="form-control"


if ($data['performance_schema']) {
    echo ' ';


    echo Form::select('database', 'id', $data['databases'], "", array('style' => 'margin-bottom:0px', 'class' => 'form-control'));
    echo ' ';
    echo Form::input('database', 'filter', array('style' => 'margin-bottom:0px', 'placeholder' => __("Filter"), 'class' => 'form-control'));
    echo '<b> ' . __("ORDER BY") . ' </b>';
    echo Form::select('field', 'id', $data['fields'], "", array('style' => 'margin-bottom:0px', 'class' => 'form-control'));

    echo ' ';
    echo Form::select('orderby', 'id', $data['orderby'], "", array('style' => 'margin-bottom:0px', 'class' => 'form-control'));
}
echo ' <button type="submit" class="btn btn-primary">Submit</button>';


echo '</div>';
echo '</form>';

if (!empty($data['pagination'])) {
    echo '<br />';

    echo $data['pagination'];
}
echo '</div>';





if ($data['performance_schema']) {

    echo '<div class="well">';

    echo __("Results found : ") . "<b>" . $data['count'] . "</b>";

    echo '</div>';


    echo '<table class="table table-condensed table-bordered table-striped">';


    echo '<tr>';

    echo '<th rowspan="2">Top</th>';
    echo '<th rowspan="2">Database</th>';

    //echo '<th>DIGEST</th>';
    echo '<th rowspan="2">Count</th>';
    echo '<th rowspan="2" style="max-width:200px;overflow:auto;"><span class="inner">Query</span></th>';
    echo '<th colspan="2">AVG rows</th>';

    echo '<th colspan="3">Execution time</th>';


    echo '<th rowspan="2">First seen</th>';
    echo '<th rowspan="2">Last seen</th>';


    echo '</tr>';
    
    echo '<tr>';

    echo '<th>affected/sent</th>';
    echo '<th>parsed</th>';
    echo '<th>AVG</th>';
    echo '<th>MIN</th>';
    echo '<th>MAX</th>';



    echo '</tr>';

    $i = 0;

    foreach ($data['event_by_digest'] as $key => $event) {


        $i++;

        $sql = $event['DIGEST_TEXT'];

        //$sql = preg_replace("/(\sinner\s|\sGROUP\sBY\s|\sjoin\s|\sINTO\s|SELECT\s|\sFROM\s|\sWHERE\s|\sOR\s|\sORDER\sBY\s)/i", "<br/><b style=\"color:#00F\">\$1</b>", $sql);
        //$sql = str_replace(',', ',<br/>', $sql);
        //$sql = preg_replace("#(/\*([^\*/]+)\*/)#i", "", $sql); //retire les commentaires



        /*
          $sql = preg_replace("/(CDIMPU)/i", "<b style=\"color:red\">\$1</b>", $sql);
          $sql = preg_replace("/(CHAPUISA)/i", "<b style=\"color:black\">\$1</b>", $sql);
          $sql = preg_replace("/(\sinner\s|\sGROUP\sBY\s|\sjoin\s|\sINTO\s|SELECT\s|\sFROM\s|\sWHERE\s|\sOR\s|\sORDER\sBY\s)/i", "<br/>&nbsp;&nbsp;&nbsp;&nbsp;<b style=\"color:#00F\">\$1</b>", $sql);
          $sql = preg_replace("/(\sAND\s|\sOR\s)/i", "<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style=\"color:#00F\">\$1</b>", $sql);

          $sql = preg_replace("/(\sIN\s|\sON\s)/i", "<b style=\"color:#00F\">\$1</b>", $sql);
          $sql = preg_replace("/(NULL)/i", "<b style=\"color:orange\">\$1</b>", $sql);
          $sql = preg_replace("/(\sUNION\sALL\s|\sUNION\s)/i", "<br/><b style=\"color:#00F\">\$1</b>", $sql);
          $sql = preg_replace("/(\s=\s|\s!=\s|\s\+\s|\s\-\s)/i", "<b style=\"color:#000\">\$1</b>", $sql);

         */

        echo '<tr>';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $event['SCHEMA_NAME'] . '</td>';
        //echo '<td>' . $event['DIGEST'] . '</td>';
        echo '<td>' . number_format($event['COUNT_STAR'], 0, '.', ' ') . '</td>';

        echo '<td>' . \SqlFormatter::format($sql) . '</td>';

        if (!empty($event['SUM_ROWS_AFFECTED'])) {
            echo '<td>' . number_format(round($event['SUM_ROWS_AFFECTED'] / $event['COUNT_STAR'], 2), 0, '.', ' ') . '</td>';
        } else {

            if ($event['COUNT_STAR'] == 0) {
                $event['COUNT_STAR'] = 1;
            }
            echo '<td>' . number_format(round($event['SUM_ROWS_SENT'] / $event['COUNT_STAR'], 2), 0, '.', ' ') . '</td>';
        }


        echo '<td>' . round($event['SUM_ROWS_EXAMINED']) . '</td>';
        echo '<td>' . round($event['AVG_TIMER_WAIT'] / 1000000000000, 3) . ' sec</td>';
        echo '<td>' . round($event['MIN_TIMER_WAIT'] / 1000000000000, 3) . ' sec</td>';
        echo '<td>' . round($event['MAX_TIMER_WAIT'] / 1000000000000, 3) . ' sec</td>';
        echo '<td>' . $event['FIRST_SEEN'] . '</td>';
        echo '<td>' . $event['LAST_SEEN'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo "performance_schema is not activated or not disponible, to activate add in my.cnf : <code>performance_schema = ON</code> and restart Mysql";
}


echo '<div class="well">';
if (!empty($data['pagination'])) {
    echo '<br />';

    echo $data['pagination'];
}
echo '</div>';
