<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use Glial\I18n\I18n;
use \Glial\Sgbd\Sql\Mysql\Parser;
use \Glial\Sgbd\Sql\Mysql\Comment;
use \Glial\Sgbd\Sql\Mysql\Compare as CompareTable;

class Compare extends Controller
{
    var $db_origin;
    var $db_target;
    var $db_default;

    function index($params)
    {
        /*
         * SHOW TABLES
         * SHOW COLUMNS FROM table_name
         *
         */
        $this->layout_name = 'pmacontrol';
        $db                = $this->di['db']->sql(DB_DEFAULT);
        $this->db_default  = $db;
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

        $this->data['server'] = [];
        foreach ($servers as $server) {
            $tmp                    = [];
            $tmp['id']              = $server['id'];
            $tmp['libelle']         = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $this->data['server'][] = $tmp;
        }

        $this->data['listdb1'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__original'])) {
            $select1               = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__original']));
            $this->data['listdb1'] = $select1['databases'];
        }

        $this->data['listdb2'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__compare'])) {
            $select1               = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__compare']));
            $this->data['listdb2'] = $select1['databases'];
        }


        $this->data['display'] = false;

        if (count($this->data['listdb2']) != 0 && count($this->data['listdb1']) != 0) {
            if (!empty($_GET['compare_main']['database__original']) && !empty($_GET['compare_main']['database__compare'])) {

                $this->analyse($_GET['compare_main']['id_mysql_server__original'], $_GET['compare_main']['database__original'],
                    $_GET['compare_main']['id_mysql_server__compare'], $_GET['compare_main']['database__compare']);

                $this->data['display'] = true;

                $this->di['log']->warning('[Compare] '.$_GET['compare_main']['id_mysql_server__original'].":".$_GET['compare_main']['database__original']." vs ".
                    $_GET['compare_main']['id_mysql_server__compare'].":".$_GET['compare_main']['database__compare']."(".$_SERVER["REMOTE_ADDR"].")");
            }
        }

        $this->set('data', $this->data);
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

        $sql  = "SHOW DATABASES";
        $res2 = $db_to_get_db->sql_query($sql);

        $data['databases'] = [];
        while ($ob                = $db_to_get_db->sql_fetch_object($res2)) {
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
        $db    = $this->di['db']->sql(DB_DEFAULT);
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

        $sql  = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server2)."';";
        $res2 = $db->sql_query($sql);

        if ($db->sql_num_rows($res2) == 1) {
            while ($ob = $db->sql_fetch_object($res2)) {
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
        $res3   = $db_ori->sql_query($sql);
        $ob     = $db_ori->sql_fetch_object($res3);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db1."' original doesn't exist on server original : '".$db_name_ori."'";
        }

        $db_cmp = $this->di['db']->sql($db_name_cmp);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_cmp->sql_real_escape_string($db2)."';";
        $res4   = $db_cmp->sql_query($sql);
        $ob     = $db_cmp->sql_fetch_object($res4);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db2."' original doesn't exist on server original : '".$db_name_cmp."'";
        }

        if ($id_server1 == $id_server2 && $db1 == $db2) {
            $error[] = "The databases to compare cannot be the same on same server";
        }

        if (count($error) === 0) {
            return true;
        } else {
            return $error;
        }
    }

    private function analyse($id_server1, $db1, $id_server2, $db2)
    {
        $db_original = $this->getDbLinkFromId($id_server1);
        $db_compare  = $this->getDbLinkFromId($id_server2);
        $db          = $this->di['db']->sql(DB_DEFAULT);

        $this->db_origin  = $db_original;
        $this->db_target  = $db_compare;
        $this->db_default = $db;

        $data = $this->compareTableList($db1, $db2, "BASE TABLE");
        $this->compareTable($db1, $db2, $data);


        $data2 = $this->compareTableList($db1, $db2, "VIEW");
        $this->compareView($db1, $db2, $data2);


        $this->compareListObject($db1, $db2, "TRIGGER");
        $this->compareListObject($db1, $db2, "FUNCTION");
        $this->compareListObject($db1, $db2, "PROCEDURE");
    }

    private function compareTable($original, $compare, $data)
    {
        $dbs = [$this->db_origin, $this->db_target];

        $queries = array();

        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $queries[$table] = "SHOW CREATE TABLE `".$original."`.`".$table."`";
            }
        }
        $resultat = $this->execMulti($queries, $this->db_origin);

        $queries2 = array();
        foreach ($data as $table => $elem) {
            if (!empty($elem[1])) {
                $queries2[$table] = "SHOW CREATE TABLE `".$compare."`.`".$table."`";
            }
        }
        $resultat2 = $this->execMulti($queries2, $this->db_target);

        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $this->data['BASE TABLE'][$table]['ori'] = $resultat[$table][0]['Create Table'];
            } else {
                $this->data['BASE TABLE'][$table]['ori'] = "";
            }

            if (!empty($elem[1])) {
                $this->data['BASE TABLE'][$table]['cmp'] = $resultat2[$table][0]['Create Table'];
            } else {
                $this->data['BASE TABLE'][$table]['cmp'] = "";
            }

            /* fine optim else we compare every thing even when not required */
            if ($this->data['BASE TABLE'][$table]['cmp'] === $this->data['BASE TABLE'][$table]['ori']) {
                $this->data['BASE TABLE'][$table]['script']  = array();
                $this->data['BASE TABLE'][$table]['script2'] = array();
            } elseif (empty($this->data['BASE TABLE'][$table]['cmp'])) {
                $this->data['BASE TABLE'][$table]['script2'][0] = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS",
                    $this->data['BASE TABLE'][$table]['ori']);
                $this->data['BASE TABLE'][$table]['script'][0]  = "DROP TABLE IF EXISTS `".$table."`";
            } elseif (empty($this->data['BASE TABLE'][$table]['ori'])) {
                $this->data['BASE TABLE'][$table]['script'][0]  = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS",
                    $this->data['BASE TABLE'][$table]['cmp']);
                $this->data['BASE TABLE'][$table]['script2'][0] = "DROP TABLE IF EXISTS `".$table."`";
            } else {
                $updater                                     = new CompareTable;
                $this->data['BASE TABLE'][$table]['script']  = $updater->getUpdates($this->data['BASE TABLE'][$table]['cmp'],
                    $this->data['BASE TABLE'][$table]['ori']);
                $updater                                     = new CompareTable;
                $this->data['BASE TABLE'][$table]['script2'] = $updater->getUpdates($this->data['BASE TABLE'][$table]['ori'],
                    $this->data['BASE TABLE'][$table]['cmp']);
                unset($updater);
            }
        }

        //check table name
        //check collation
        //check charactere set
        //check engine
    }

    function tst()
    {

        $this->view = false;

        $struct1 = "CREATE TABLE `orphan_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_client_code` varchar(128) NOT NULL,
  `external_id` varchar(128) NOT NULL,
  `raw` varchar(4096) NOT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `tried` int(11) DEFAULT NULL,
  `origin` varchar(2) DEFAULT NULL,
  `sicli` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_ORPHEAN_ORDER_EXTERNAL_ID` (`external_id`),
  KEY `IDX_ORPHEAN_EXTERNAL_CLIENT_CODE` (`external_client_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";


        $struct4 = "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `symwis`.`payment_methods` AS select `p`.`id` AS `id`,`p`.`client_id` AS `client_id`,`p`.`bank_account_id` AS `bank_account_id`,`p`.`card_id` AS `card_id`,if((`p`.`bank_account_id` is not null),'direct_debit',if((`p`.`card_id` is not null),'credit_card','unknown')) AS `type`,if(((`p`.`bank_account_id` is not null) and (`bm`.`active` <> 0) and (`bm`.`signature_date` is not null) and (`bm`.`signature_date` < now())),1,if(((`symwis`.`card`.`active` <> 0) and (`symwis`.`card`.`definitely_unactive` = 0)),1,0)) AS `active`,`p`.`created_at` AS `created_at`,`p`.`updated_at` AS `updated_at` from ((`symwis`.`payment` `p` left join `symwis`.`bank_mandate` `bm` on((`p`.`bank_account_id` = `bm`.`bank_account_id`))) left join `symwis`.`card` on((`p`.`card_id` = `symwis`.`card`.`id`)));";
        $struct3 = "CREATE ALGORITHM=UNDEFINED DEFINER=`fghfg`@`localhost` SQL SECURITY DEFINER VIEW `symwis`.`payment_methods` AS select `p`.`id` AS `id`,`p`.`client_id` AS `client_id`,`p`.`bank_account_id` AS `bank_account_id`,`p`.`card_id` AS `card_id`,if((`p`.`bank_account_id` is not null),'direct_debit',if((`p`.`card_idkj` is not null),'credit_card','unknown')) AS `type`,if(((`p`.`bank_account_id` is not null) and (`bm`.`active` <> 0) and (`bm`.`signature_date` is not null) and (`bm`.`signature_date` < now())),1,if(((`symwis`.`card`.`active` <> 0) and (`symwis`.`card`.`definitely_unactive` = 0)),1,0)) AS `active`,`p`.`created_at` AS `created_at`,`p`.`updated_at` AS `updated_at` from ((`symwis`.`payment` `p` left join `symwis`.`bank_mandate` `bm` on((`p`.`bank_account_id` = `bm`.`bank_account_id`))) inner join `symwis`.`card` on((`p`.`card_id` = `symwis`.`card`.`id`)));";

        $struct2 = "CREATE TABLE `orphan_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_client_code` varchar(128) NOT NULL,
  `external_id` varchar(128) NOT NULL,
  `raw` varchar(4096) NOT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `tried` int(11) DEFAULT NULL,
  `origin` varchar(32) DEFAULT NULL,
  `sicli` char(1) DEFAULT NULL,
  `sicli25` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_ORPHEAN_EXTERNAL_CLIENT_CODE` (`external_client_code`)
);";


        $updater = new CompareTable;
        $res     = $updater->getUpdates($struct3, $struct4);
        print_r($res);
    }

    public function compareTableList($original, $compare, $table_type = 'BASE TABLE')
    {
        $dbs = [$this->db_origin, $this->db_target];
        $req = ["select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '".$original."' AND TABLE_TYPE='".$table_type."' order by TABLE_NAME;",
            "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '".$compare."' AND TABLE_TYPE='".$table_type."' order by TABLE_NAME;"];
        $res = $this->exectute($dbs, $req);

        $i = 0;
        foreach ($res as $query) {
            foreach ($query as $elem) {
                //debug($elem);
                $this->data[$table_type][$elem['TABLE_NAME']][$i] = 1;
            }
            $i++;
        }

        ksort($this->data[$table_type]);
        return $this->data[$table_type];
    }

    private function getDbLinkFromId($id_db)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_db)."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_link = $this->di['db']->sql($ob->name);
        }

        return $db_link;
    }

    private function exectute(array $dbs, $query)
    {
        $i   = 0;
        $ret = [];

        foreach ($dbs as $db) {
            if (is_array($query)) {
                $req = $query[$i];
            } else {
                $req = $query;
            }

            $res = $db->sql_query($req);
            while ($tab = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $ret[$i][] = $tab;
            }

            $i++;
        }
        return $ret;
    }

    public function execMulti($queries, $db_link)
    {
        if (IS_CLI) {
            $this->view = false;
        }
        $query = implode(";", $queries);
        $ret   = [];
        $i     = 0;
        if ($db_link->sql_multi_query($query)) {
            foreach ($queries as $table => $elem) {
                $result = $db_link->sql_store_result();
                while ($row    = $db_link->sql_fetch_array($result, MYSQLI_ASSOC)) {
                    $ret[$table][] = $row;
                }
                if ($db_link->sql_more_results()) {
                    $db_link->sql_next_result();
                }
            }
        }
        return $ret;
    }

    public function compareView($original, $compare, $data)
    {
        $queries = array();

        foreach ($data as $table => $elem) {
            if (!empty($elem[1])) {
                $queries[$table] = "SHOW CREATE VIEW `".$original."`.`".$table."`";
            }
        }
        $resultat = $this->execMulti($queries, $this->db_origin);


        $queries2 = array();
        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $queries2[$table] = "SHOW CREATE VIEW `".$compare."`.`".$table."`";
            }
        }
        $resultat2 = $this->execMulti($queries2, $this->db_target);

        // UPDATE `mysql`.`proc` p SET definer = 'root@localhost' WHERE definer='root@foobar' AND db='whateverdbyouwant';



        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $this->data['VIEW'][$table]['ori'] = $resultat2[$table][0]['Create View'];
            } else {
                $this->data['VIEW'][$table]['ori'] = "";
            }

            if (!empty($elem[1])) {
                $this->data['VIEW'][$table]['cmp'] = $resultat2[$table][0]['Create View'];
            } else {
                $this->data['VIEW'][$table]['cmp'] = "";
            }

            /* fine optim else we compare every thing even when not required */
            if ($this->data['VIEW'][$table]['cmp'] === $this->data['VIEW'][$table]['ori']) {
                $this->data['VIEW'][$table]['script']  = array();
                $this->data['VIEW'][$table]['script2'] = array();
            } elseif (empty($this->data['VIEW'][$table]['cmp'])) {
                $this->data['VIEW'][$table]['script2'][0] = $this->data['VIEW'][$table]['ori'];
                $this->data['VIEW'][$table]['script'][0]  = "DROP VIEW `".$table."`";
            } elseif (empty($this->data['VIEW'][$table]['ori'])) {
                $this->data['VIEW'][$table]['script'][0]  = $this->data['VIEW'][$table]['cmp'];
                $this->data['VIEW'][$table]['script2'][0] = "DROP VIEW `".$table."`";
            } else {
                $this->data['VIEW'][$table]['script'][0]  = $this->data['VIEW'][$table]['cmp'];
                $this->data['VIEW'][$table]['script2'][0] = $this->data['VIEW'][$table]['ori'];
            }
        }
    }

    function compareListObject($db1, $db2, $type_object)
    {
        $query['TRIGGER']['query']   = "select trigger_schema, trigger_name, action_statement from information_schema.triggers where trigger_schema ='{DB}'";
        $query['FUNCTION']['query']  = "show function status WHERE Db ='{DB}';";
        $query['PROCEDURE']['query'] = "show procedure status WHERE Db ='{DB}'";

        $query['TRIGGER']['field']   = "trigger_name";
        $query['FUNCTION']['field']  = "Name";
        $query['PROCEDURE']['field'] = "Name";
        $query['TABLE']['field']     = "TABLE_NAME";

        if (!in_array($type_object, array_keys($query))) {
            throw new \Exception("PMACTRL-095 : this type of object is not supported : '".$type_object."'", 80);
        }

        $dbs[$db1] = $this->db_origin;
        $dbs[$db2] = $this->db_target;

        $i = 0;

        //to prevent if a DB don't have a type of object
        $this->data[$type_object] = array();

        foreach($dbs as $db_name => $db_link)
        {
            $sql = str_replace('{DB}', $db_name, $query[$type_object]['query']);
            $res = $db_link->sql_query($sql);

            while ($row = $db_link->sql_fetch_array($res, MYSQLI_ASSOC))
            {
                $this->data[$type_object][$query[$type_object]['field']][$i] = 1;
            }
            $i++;
        }

        ksort($this->data[$type_object]);
        return $this->data[$type_object];
    }
}