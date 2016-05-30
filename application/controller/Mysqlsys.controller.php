<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sql\Mysql\MasterSlave;
use \Glial\Sgbd\Sgbd;
use \Glial\Security\Crypt\Crypt;
use \Glial\Synapse\FactoryController;

class Mysqlsys extends Controller {

    public function index() {

        $this->title = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> ' . "MySQL-sys";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > ' . $this->title;

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $sql = "SELECT * FROM mysql_server where id='" . $_POST['mysql_server']['id'] . "'";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_mysql_server = $ob->id;
                header('location: ' . LINK . __CLASS__ . '/index/mysql_server:id:' . $id_mysql_server);
            }
        } else {

            $data = [];

            // get server available
            $sql = "SELECT * FROM mysql_server a WHERE error = '' " . $this->getFilter() . " order by a.name ASC";
            $res = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob = $db->sql_fetch_object($res)) {
                $tmp = [];
                $tmp['id'] = $ob->id;
                $tmp['libelle'] = $ob->name . " (" . $ob->ip . ")";
                $data['servers'][] = $tmp;

                if (!empty($_GET['mysql_server']['id']) && $ob->id == $_GET['mysql_server']['id']) {
                    $link_name = $ob->name;
                }
            }


            if (empty($link_name)) {
                $link_name = DB_DEFAULT;

                $sql = "SELECT * FROM mysql_server WHERE name='" . DB_DEFAULT . "'";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    $_GET['mysql_server']['id'] = $ob->id;
                }
            }

            $remote = $this->di['db']->sql($link_name);
            $sql = "select table_name from information_schema.tables WHERE table_schema = 'sys' and table_name not like 'x$%' and table_name !='version';";
            $res = $remote->sql_query($sql);
            $data['view_available'] = [];
            while ($ob = $remote->sql_fetch_object($res)) {
                $data['view_available'][] = $ob->table_name;
            }

            if (!empty($_GET['mysqlsys']) && in_array($_GET['mysqlsys'], $data['view_available'])) {
                $sql = "SELECT * FROM `sys`.`" . $_GET['mysqlsys'] . "`";
                $data['table'] = $db->sql_fetch_yield($sql);
            }
            
            
            $data['variables'] = $db->getVersion();
            
        }
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
            $where .= " AND a.id_environment = '" . $environment . "'";
        }
        
        if (!empty($client)) {
            $where .= " AND a.id_client = '" . $client . "'";
        }
        return $where;
    }
}
