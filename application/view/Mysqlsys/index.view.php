<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

echo '<div class="well">';


\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

function remove($array) {
    $params = explode("/", trim($_GET['url'], "/"));

    //print_r($params);


    foreach ($params as $key => $param) {
        foreach ($array as $var) {
            if (strstr($param, $var . ':')) {
                unset($params[$key]);
            }
        }
    }
    $ret = implode('/', $params);

    return $ret;
}

echo '<br />';
echo '<form action="" method="POST">';
echo __("Server") . " : ";
echo Form::select("mysql_server", "id", $data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
echo ' ';


echo '<button type="submit" class="btn btn-primary">Filter</button>';
echo '</form>';
echo '</div>';



if (count($data['view_available']) > 0) {
    ?>
    <div class="row">
        <div class="col-md-2">

            <?php
            echo '<table class="table table-condensed table-bordered table-striped">';

            echo '<tr>';
            echo '<th>' . __("Reporting") . '</th>';
            echo '</tr>';


            echo '<tr>';
            echo '<td>';
            foreach ($data['view_available'] as $view) {
                //$url = $_GET['url'];

                $url = remove(array("mysqlsys"));

                if (!empty($_GET['mysqlsys']) && $view == $_GET['mysqlsys']) {
                    echo '<a href="' . LINK . $url . '/mysqlsys:' . $view . '"><b>' . $view . '</b></a><br/>';
                } else {
                    echo '<a href="' . LINK . $url . '/mysqlsys:' . $view . '">' . $view . '</a><br/>';
                }
            }

            echo '</td>';
            echo '</tr>';
            echo '</table>';
            ?>

        </div>
        <div class="col-md-10">
    <?php
    $i = 0;


    if (!empty($data['table'])) {

        echo '<table class="table table-condensed table-bordered table-striped">';
        foreach ($data['table'] as $key => $line) {
            $i++;

            if ($i === 1) {
                echo '<tr>';

                echo '<th>' . __("Top") . '</th>';
                foreach ($line as $var => $val) {
                    echo '<th>' . $var . '</th>';
                }
                echo '</tr>';
            }

            echo '<tr>';
            echo '<td>' . $i . '</td>';
            foreach ($line as $var => $val) {
                echo '<td>' . $val . '</td>';
            }

            echo '</tr>';

            //print_r($val);
        }
        echo "</table>";
    } else {
        echo "<b>No data</b>";
    }
    ?>

        </div>
    </div>
    <?php
} elseif (version_compare($data['variables'], "5.5", "<=")) {

    echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

    echo "This version of MySQL / MariaDB / Percona Server is not compatible with mysql-sys !<br />"
    . " mysql-sys require version of MySQL / MariaDB / Percona Server 5.5 at minimum.";

    echo '</div>';
} else {


    echo 'mysql-sys is not yet installed on this server, do you want to install it ? ';
    echo '<button type="link" class="btn btn-primary">Install MySQL-sys</button>';
}