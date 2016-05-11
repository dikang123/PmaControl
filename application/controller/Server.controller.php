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

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

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


        /********/
        
        $data['menu_select']['main']['name'] = __('Servers');
        $data['menu_select']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
        $data['menu_select']['main']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/main';

        $data['menu_select']['hardware']['name'] = __('Hardware');
        $data['menu_select']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
        $data['menu_select']['hardware']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/hardware';


        $data['menu_select']['database']['name'] = __('Databases');
        $data['menu_select']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
        $data['menu_select']['database']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/database';

        $data['menu_select']['statistics']['name'] = __('Statistics');
        $data['menu_select']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
        $data['menu_select']['statistics']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/statistics';

        $data['menu_select']['memory']['name'] = __('Memory');
        $data['menu_select']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
        $data['menu_select']['memory']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/memory';

        $data['menu_select']['index']['name'] = __('Index');
        $data['menu_select']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
        $data['menu_select']['index']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/index';


        $data['menu_select']['system']['name'] = __('System');
        $data['menu_select']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
        $data['menu_select']['system']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/system';

        $data['menu_select']['logs']['name'] = __('Logs');
        $data['menu_select']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu_select']['logs']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/index';


        $data['menu_select']['id']['name'] = __('Server');
        $data['menu_select']['id']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu_select']['id']['path'] = LINK . __CLASS__ . '/' . __FUNCTION__ . '/id';
        
        
        
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


        $this->title = '<span class="glyphicon glyphicon glyphicon-home"></span> '.__("Dashboard");
        $this->ariane = ' > <a href⁼"">' . '<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '.__("Dashboard") . '</a> > ' . $data['menu'][$param[0]]['icone'] . ' ' . $data['menu'][$param[0]]['name'];

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

    
    /*
     * 
     * graph with Chart.js
     * 
     */
    public function id($param) {

        $this->di['js']->addJavascript(array("chart.js/src/chart.js"));
        
        $db = $this->di['db']->sql(DB_DEFAULT);
        
        
        if ($_SERVER['REQUEST_METHOD'] === "POST")
        {
            $sql = "SELECT * FROM mysql_server where id='".$_POST['mysql_server']['id']."'";
            
            $res = $db->sql_query($sql);
            
            
            while ($ob = $db->sql_fetch_object($res))
            {
                $id_mysql_server = $ob->id;
                
                header('location: '.LINK.__CLASS__.'/listing/id/mysql_server:id:'.$id_mysql_server.'/mysql_status_name:id:'.$_POST['mysql_status_name']['id']);
            }
            
        }
        
        // get server available
        $sql = "SELECT * FROM mysql_server WHERE error = '' order by name ASC";
        $res = $db->sql_query($sql);
        $data['servers'] = array();
        while($ob = $db->sql_fetch_object($res))
        {
            $tmp = [];    
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name." (".$ob->ip.")";
            $data['servers'][] = $tmp;
        }
        
        
        // get server available
        $sql = "SELECT * FROM mysql_status_name order by name ASC";
        $res = $db->sql_query($sql);
        $data['status'] = array();
        while($ob = $db->sql_fetch_object($res))
        {
            $tmp = [];    
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name;
            $data['status'][] = $tmp;
        }
        
        
        
        
        
        $elems = array(60*5, 60*15, 3600, 3600*2, 3600*6, 3600*12, 3600*24, 3600*48, 3600*24*7);
        
        if (!empty($_GET['mysql_server']['id']))
        {
            $sql ="SELECT * FROM mysql_status_value_int a
                    
                    WHERE a.id_mysql_server = ".$_GET['mysql_server']['id']." 
                    AND a.id_mysql_status_name = '".$_GET['mysql_status_name']['id']."'
                    and a.date > date_sub(now(), interval 60 minute) ORDER BY a.date ASC;";
            
            $data['sql'] = $sql;
            
            $data['graph'] = $db->sql_fetch_yield($sql);
            
            
   
            
        }


$this->di['js']->code_javascript('


var riceData = {
    labels : ["January","February","March","April","May","June"],
    datasets : [
        {
            fillColor : "rgba(172,194,132,0.4)",
            strokeColor : "#ACC26D",
            pointColor : "#fff",
            pointStrokeColor : "#9DB86D",
            data : [203000,15600,99000,25100,30500,24700]
        }
    ]
}

var rice = document.getElementById("rice").getContext("2d");
//new Chart(rice).Line(riceData);

');
        

        $this->set('data', $data);
    }

}
