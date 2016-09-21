<?php

use \Glial\Synapse\Controller;
use \Glial\Utility\Inflector;

class Architecture extends Controller {

    public function index() {

        $this->title = '<i class="fa fa-object-group"></i> ' . __("Architecture");
        $this->ariane = ' > <a href⁼"">' . '<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '
                . __("Dashboard") . '</a> > <i class="fa fa-object-group" style="font-size:14px"></i> ' . __("Architecture");


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT c.id,c.display,c.height FROM mysql_server a
            INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
            INNER JOIN architecture c ON c.id = b.id_architecture
            WHERE 1 ".$this->getFilter()." AND c.height > 8 GROUP BY c.id ORDER BY c.height DESC,c.width DESC ";

        //@TODO c.height > 8   => to fix on register table architecture on Dot

        /***
         * SELECT c.id,c.display FROM mysql_server a
            INNER JOIN link__architecture__mysql_server b ON a.id = b.id_mysql_server
            INNER JOIN architecture c ON c.id = b.id_architecture
            WHERE 1 GROUP BY c.id
         */
        
        $data['graphs'] = $db->sql_fetch_yield($sql);
        
        
        $this->set('data', $data);
    }

    //to mutualize
    private function getFilter() {

        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (" . implode(',',json_decode($environment, true)) . ")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (" . implode(',',  json_decode($client, true)) . ")";
        }
        

        return $where;
    }

}
