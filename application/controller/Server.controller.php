<?php

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;
use \Glial\Net\Ssh;
use \Glial\Security\Crypt\Crypt;

class Server extends Controller {

    //dba_source

    public function hardware() {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->title = __("Hardware");
        $this->ariane = " > " . $this->title;

        $sql = "SELECT c.libelle as client,d.libelle as environment,a.*, b.version, b.is_available 
            FROM mysql_server a 
                 INNER JOIN client c on c.id = a.id_client 
                 INNER JOIN environment d on d.id = a.id_client 
         LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
         order by `name`;";

        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function before($param) {
        $this->layout_name = 'pmacontrol';
    }

    public function listing($param) {

        $this->di['js']->addJavascript(array('http://select2.github.io/select2/select2-3.4.1/select2.js'));

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main WHERE id=1";
        $res = $db->sql_query($sql);

        $ob = $db->sql_fetch_object($res);

        $data['pid'] = $ob->pid;
        $data['date'] = $ob->date;
        $data['log_file'] = $ob->log_file;

        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);


        $data['client'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);


        $data['environment'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }


        $data['menu']['main']['name'] = __('Servers');
        $data['menu']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
        $data['menu']['main']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/main';

        $data['menu']['hardware']['name'] = __('Hardware');
        $data['menu']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
        $data['menu']['hardware']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/hardware';


        $data['menu']['database']['name'] = __('Databases');
        $data['menu']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
        $data['menu']['database']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/database';

        $data['menu']['statistics']['name'] = __('Statistics');
        $data['menu']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
        $data['menu']['statistics']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/statistics';

        $data['menu']['memory']['name'] = __('Memory');
        $data['menu']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
        $data['menu']['memory']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/memory';

        $data['menu']['index']['name'] = __('Index');
        $data['menu']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
        $data['menu']['index']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/index';


        $data['menu']['system']['name'] = __('System');
        $data['menu']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
        $data['menu']['system']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/system';

        $data['menu']['logs']['name'] = __('Logs');
        $data['menu']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu']['logs']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/index';


        $data['menu']['id']['name'] = __('Server');
        $data['menu']['id']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu']['id']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/id';


        if (!empty($param[0])) {
            if (in_array($param[0], array("main", "database", "statistics", "logs", "memory", "index", "hardware", "system", "id"))) {
                $_GET['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/' . $param[0];
            }
        }

        if (empty($_GET['path']) && empty($param[0])) {
            $_GET['path'] = $data['menu']['main']['path'];
            $param[0] = 'main';
        }

        if (empty($_GET['path'])) {
            $_GET['path'] = 'main';
        }


        $this->title = __("Dashboard");
        $this->ariane = ' > <a href⁼"">' . $this->title . '</a> > ' . $data['menu'][$param[0]]['icone'] . ' ' . $data['menu'][$param[0]]['name'];


        $this->set('data', $data);
    }

    public function main() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->title = __("Dashboard");
        $this->ariane = " > " . $this->title;

        $sql = "SELECT a.*, b.version, b.is_available,c.libelle as client,d.libelle as environment FROM mysql_server a 
                     INNER JOIN client c on c.id = a.id_client 
                 INNER JOIN environment d on d.id = a.id_environment
                 LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
                 order by `name`;";

        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function database() {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT a.id,a.name,a.ip,a.port,a.error,
			GROUP_CONCAT('',b.name) as dbs,
			GROUP_CONCAT('',b.id) as id_db,
			GROUP_CONCAT('',b.data_length) as data_length,
			GROUP_CONCAT('',b.data_free) as data_free,
			GROUP_CONCAT('',b.index_length) as index_length,
			GROUP_CONCAT('',b.collation_name) as collation_name,
			GROUP_CONCAT('',b.character_set_name) as character_set_name,
			GROUP_CONCAT('',b.binlog_do_db) as binlog_do_db,
			GROUP_CONCAT('',b.tables) as tables,
			GROUP_CONCAT('',b.rows) as rows
			FROM mysql_server a
			INNER JOIN mysql_database b ON b.id_mysql_server = a.id
			GROUP BY a.id";

        $data['servers'] = $db->sql_fetch_yield($sql);
        $this->set('data', $data);
    }

    public function statistics() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $fields = array("Com_select", "Com_update", "Com_insert", "Com_delete", "Threads_connected", "Uptime", "Com_commit", "Com_rollback", "Com_begin", "Com_replace");
        $sql = $this->buildQuery($fields);
        $res = $db->sql_query($sql);

        $data['servers'] = $db->sql_fetch_yield($sql);

        //$db->sql_query("DROP TABLE IF EXISTS `temp`");

        $this->set('data', $data);
    }

    public function logs() {
        //used with recursive
    }

    private function buildQuery($fields) {
        $sql = 'select a.ip, a.port, a.id, a.name,';

        $i = 0;
        $tmp = [];
        foreach ($fields as $field) {
            $tmp[] = " c$i.value as $field";
            $i++;
        }

        $sql .= implode(",", $tmp);
        $sql .= " from mysql_server a ";
        $sql .= " INNER JOIN mysql_status_max_date b ON a.id = b.id_mysql_server ";

        $tmp = [];
        $i = 0;
        foreach ($fields as $field) {
            $sql .= " INNER JOIN mysql_status_value_int c$i ON c$i.id_mysql_server = a.id AND b.date = c$i.date";
            $sql .= " INNER JOIN mysql_status_name d$i ON d$i.id = c$i.id_mysql_status_name ";
            $i++;
        }

        $sql .= " WHERE 1 ";

        $tmp = [];
        $i = 0;
        foreach ($fields as $field) {
            $sql .= " AND d$i.name = '" . $field . "'  ";
            $i++;
        }

        $sql .=";";

        return $sql;
    }

    public function gg() {
        $this->view = false;

        $fields = array("Com_select", "Com_update", "Com_insert", "Com_delete", "Threads_connected", "Uptime");

        echo \SqlFormatter::format($this->buildQuery($fields));
    }

    public function memory() {
        $this->layout_name = 'pmacontrol';
        $this->title = __("Memory");
        $this->ariane = " > " . __("Tools Box") . " > " . $this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server 
            WHERE is_available = 1 
            order by a.`name`";
        $res50 = $default->sql_query($sql);

        while ($ob50 = $default->sql_fetch_object($res50)) {
            $db = $this->di['db']->sql($ob50->name);
            $data['variables'][$ob50->name] = $db->getVariables();
            $data['status'][$ob50->name] = $db->getStatus();
            $data['memory'][$ob50->name] = $ob50->memory_kb;
        }
        $this->set('data', $data);
    }

    public function index() {
        $this->layout_name = 'pmacontrol';

        $this->title = __("Index usage");
        $this->ariane = " > " . __("Tools Box") . " > " . $this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server
            WHERE is_available = 1
            order by `name`";
        $res50 = $default->sql_query($sql);

        $data = [];
        while ($ob50 = $default->sql_fetch_object($res50)) {

            $db = $this->di['db']->sql($ob50->name);
            $data['status'][$ob50->name] = $db->getStatus();
        }


        $this->set('data', $data);
    }

    public function id($param) {

        $default = $this->di['db']->sql(DB_DEFAULT);
    }

}
