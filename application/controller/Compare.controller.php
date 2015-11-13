<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use Glial\I18n\I18n;

class Compare extends Controller
{

    function index($params)
    {
        /*
         * SHOW TABLES
         * SHOW COLUMNS FROM table_name
         *
         */
        $this->layout_name = 'pmacontrol';
        $db                = $this->di['db']->sql(DB_DEFAULT);
        $this->title       = __("Compare");
        $this->ariane      = "> ".'<a href="'.LINK.'Plugins/index/">'.__('Plugins')."</a> > ".$this->title;


        $redirect = false;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $id_server1 = empty($_POST['compare_main']['id_mysql_server__original']) ? "" : $_POST['compare_main']['id_mysql_server__original'];
            $id_server2 = empty($_POST['compare_main']['id_mysql_server__compare']) ? "" : $_POST['compare_main']['id_mysql_server__compare'];
            $db1        = empty($_POST['compare_main']['database__original']) ? "" : $_POST['compare_main']['database__original'];
            $db2        = empty($_POST['compare_main']['database__compare']) ? "" : $_POST['compare_main']['database__compare'];


            $out = $this->checkConfig($id_server1, $db1, $id_server2, $db2);

            if ($out !== true) {
                $extra = "";

                foreach ($out as $msg) {
                    $extra .= "<br />".__($msg);
                }

                $msg   = I18n::getTranslation(__("Please correct your paramaters !").$extra);
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                $redirect = true;
            }

            header('location: '.LINK.'compare/index/compare_main:id_mysql_server__original:'.$id_server1
                .'/compare_main:'.'id_mysql_server__compare:'.$id_server2
                .'/compare_main:'.'database__original:'.$db1
                .'/compare_main:'.'database__compare:'.$db2
            );
        }


        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "compare/index.js"));

        $sql     = "SELECT * FROM mysql_server order by `name`";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp              = [];
            $tmp['id']        = $server['id'];
            $tmp['libelle']   = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $data['server'][] = $tmp;
        }

        $data['listdb1'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__original'])) {
            $select1         = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__original']));
            $data['listdb1'] = $select1['databases'];
        }

        $data['listdb2'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__compare'])) {
            $select1         = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__compare']));
            $data['listdb2'] = $select1['databases'];
        }

        $this->set('data', $data);
    }

    function getDatabaseByServer($param)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($param[0])."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_to_get_db = $this->di['db']->sql($ob->name);
        }

        $sql = "SHOW DATABASES";
        $res = $db_to_get_db->sql_query($sql);


        $data['databases'] = [];
        while ($ob                = $db_to_get_db->sql_fetch_object($res)) {
            $tmp                 = [];
            $tmp['id']           = $ob->Database;
            $tmp['libelle']      = $ob->Database;
            $data['databases'][] = $tmp;
        }

        $this->set("data", $data);

        return $data;
    }

    private function checkConfig($id_server1, $db1, $id_server2, $db2)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $error = array();

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server1)."';";
        $res = $db->sql_query($sql);
        if ($db->sql_num_rows($res) == 1) {
            while ($ob = $db->sql_fetch_object($res)) {
                $db_name_ori = $ob->name;
            }
        } else {
            $error[] = "The server original is unknow";
            unset($_GET['compare_main']['id_mysql_server__original']);
        }


        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server2)."';";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) == 1) {
            while ($ob = $db->sql_fetch_object($res)) {
                $db_name_cmp = $ob->name;
            }
        } else {
            $error[] = "The server to compare is unknow";
            unset($_GET['compare_main']['id_mysql_server__compare']);
        }

        if (count($error) !== 0) {
            return $error;
        }


        $db_ori = $this->di['db']->sql($db_name_ori);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_ori->sql_real_escape_string($db1)."';";
        $res    = $db_ori->sql_query($sql);
        $ob     = $db_ori->sql_fetch_object($res);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db1."' original doesn't exist on server original : '".$db_name_ori."'";
        }

        $db_cmp = $this->di['db']->sql($db_name_cmp);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_cmp->sql_real_escape_string($db2)."';";
        $res    = $db_cmp->sql_query($sql);
        $ob     = $db_cmp->sql_fetch_object($res);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db2."' original doesn't exist on server original : '".$db_name_cmp."'";
        }

        if ($id_server1 == $id_server2 && $db1 == $db2) {
            $error[] = "The databases to compare cannot be the same on same server";
        }

        $this->di['log']->info('GG');

        if (count($error) === 0) {
            return true;
        } else {
            return $error;
        }
    }

    private function analyse($id_server1, $db1, $id_server2, $db2)
    {
        $this->compareTable($id_server1, $db1, $id_server2, $db2);
        $this->compareField($id_server1, $db1, $id_server2, $db2);
        $this->compareIndex($id_server1, $db1, $id_server2, $db2);
    }

    private function compareTable($id_server1, $db1, $id_server2, $db2)
    {
        //check table name
        //check collation
        //check charactere set
        //check engine
    }

    private function compareField($id_server1, $db1, $id_server2, $db2)
    {

    }

    private function compareIndex($id_server1, $db1, $id_server2, $db2)
    {
        
    }
}