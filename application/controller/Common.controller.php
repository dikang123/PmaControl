<?php

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;
use \Glial\Net\Ssh;
use \Glial\Security\Crypt\Crypt;

class Common extends Controller {

    //dba_source

    public function index() {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_database";
    }

    /*
      @author: Aurélien LEQUOY
      Obtenir la liste dans un select des server MySQL operationels
     */

    public function getSelectServerAvailable() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where error='' ORDER by name";

        $res = $db->sql_query($sql);

        $data['list_servers'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name . " (" . $ob->ip . ")";

            $data['list_server'][] = $tmp;
        }


        $this->set('data', $data);
    }

    public function displayClientEnvironment($param) {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $db = $this->di['db']->sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['client']['libelle']) || !empty($_POST['environment']['libelle'])) {

                /* header("location: ".LINK."".\Glial\Synapse\FactoryController::$controller."/".\Glial\Synapse\FactoryController::$method."/client:libelle:"
                  .$_POST['client']['libelle']."/environment:libelle:".$_POST['environment']['libelle']); */


                $ret = "";
                if (!empty($_POST['client']['libelle'])) {
                    $_SESSION['client']['libelle'] = $_POST['client']['libelle'];
                    $ret .= "/client:libelle:" . $_POST['client']['libelle'];
                }

                if (!empty($_POST['environment']['libelle'])) {
                    $_SESSION['environment']['libelle'] = $_POST['environment']['libelle'];
                    $ret .= "/environment:libelle:" . $_POST['environment']['libelle'];
                }

                header("location: " . LINK . "" . $this->remove(array("client:libelle", "environment:libelle")) . $ret);
            }
        }


        if (empty($_GET['environment']['libelle']) && !empty($_SESSION['environment']['libelle'])) {
            $_GET['environment']['libelle'] = $_SESSION['environment']['libelle'];
        }

        if (empty($_GET['client']['libelle']) && !empty($_SESSION['client']['libelle'])) {
            $_GET['client']['libelle'] = $_SESSION['client']['libelle'];
        }


        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);


        $data['client'] = array();

/*
        $tmp = [];
        $tmp['id'] = "";
        $tmp['libelle'] = __("All");
        $data['environment'][] = $tmp;
*/

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);


        $data['environment'] = array();

        /*
        $tmp = [];
        $tmp['id'] = "";
        $tmp['libelle'] = __("All");
        $data['environment'][] = $tmp;
*/
        
        
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public function remove($array) {

        $params = explode("/", $_GET['url']);
        foreach ($params as $key => $param) {
            foreach ($array as $var) {
                if (strstr($param, $var)) {
                    unset($params[$key]);
                }
            }
        }

        $ret = implode('/', $params);

        return $ret;
    }

    static function getFilter() {
        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $where .= " AND a.id_environment = '" . $_GET['environment']['libelle'] . "'";
        }

        if (!empty($_GET['client']['libelle'])) {
            $where .= " AND a.id_client = '" . $_GET['client']['libelle'] . "'";
        }

        return $where;
    }

}
