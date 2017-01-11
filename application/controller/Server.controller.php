<?php

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;
use \Glial\Net\Ssh;
use \Glial\Security\Crypt\Crypt;

class Server extends Controller
{

    //dba_source
    public function hardware()
    {


        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->title  = __("Hardware");
        $this->ariane = " > ".$this->title;

        $sql = "SELECT c.libelle as client,d.libelle as environment,a.*, b.version, b.is_available 
            FROM mysql_server a 
                 INNER JOIN client c on c.id = a.id_client 
                 INNER JOIN environment d on d.id = a.id_environment
         LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
         WHERE 1 ".$this->getFilter()."
         order by `name`;";

        //echo SqlFormatter::format($sql);

        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function before($param)
    {
        $this->layout_name = 'pmacontrol';
    }

    public function listing($param)
    {


        // doc : http://silviomoreto.github.io/bootstrap-select/examples/#standard-select-boxes
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM daemon_main WHERE id=1";
        $res = $db->sql_query($sql);

        $ob = $db->sql_fetch_object($res);

        $data['pid']      = $ob->pid;
        $data['date']     = $ob->date;
        $data['log_file'] = $ob->log_file;

        $data['client']      = $this->getClients();
        $data['environment'] = $this->getEnvironments();


        $data['menu']['main']['name']  = __('Servers');
        $data['menu']['main']['icone'] = '<i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i>';
        //$data['menu']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
        $data['menu']['main']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/main';

        $data['menu']['hardware']['name']  = __('Hardware');
        $data['menu']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
        $data['menu']['hardware']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/hardware';

        $data['menu']['database']['name']  = __('Databases');
        $data['menu']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
        $data['menu']['database']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/database';

        $data['menu']['statistics']['name']  = __('Statistics');
        $data['menu']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
        $data['menu']['statistics']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/statistics';

        $data['menu']['memory']['name']  = __('Memory');
        $data['menu']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
        $data['menu']['memory']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/memory';

        $data['menu']['index']['name']  = __('Index');
        $data['menu']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
        $data['menu']['index']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/index';

        $data['menu']['system']['name']  = __('System');
        $data['menu']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
        $data['menu']['system']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/system';

        $data['menu']['logs']['name']  = __('Logs');
        $data['menu']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu']['logs']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/index';

        $data['menu']['id']['name']  = __('Graphs');
        $data['menu']['id']['icone'] = '<i class="fa fa-line-chart" aria-hidden="true"></i>';
        $data['menu']['id']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/id';

        $data['menu']['cache']['name']  = __('Cache');
        $data['menu']['cache']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
        $data['menu']['cache']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/cache';

        /*         * ***** */

        $data['menu_select']['main']['name']  = __('Servers');
        $data['menu_select']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
        $data['menu_select']['main']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/main';

        $data['menu_select']['hardware']['name']  = __('Hardware');
        $data['menu_select']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
        $data['menu_select']['hardware']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/hardware';


        $data['menu_select']['database']['name']  = __('Databases');
        $data['menu_select']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
        $data['menu_select']['database']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/database';

        $data['menu_select']['statistics']['name']  = __('Statistics');
        $data['menu_select']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
        $data['menu_select']['statistics']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/statistics';

        $data['menu_select']['memory']['name']  = __('Memory');
        $data['menu_select']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
        $data['menu_select']['memory']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/memory';

        $data['menu_select']['index']['name']  = __('Index');
        $data['menu_select']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
        $data['menu_select']['index']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/index';


        $data['menu_select']['system']['name']  = __('System');
        $data['menu_select']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
        $data['menu_select']['system']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/system';

        $data['menu_select']['logs']['name']  = __('Logs');
        $data['menu_select']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu_select']['logs']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/index';


        $data['menu_select']['id']['name']  = __('Server');
        $data['menu_select']['id']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
        $data['menu_select']['id']['path']  = LINK.__CLASS__.'/'.__FUNCTION__.'/id';



        if (!empty($param[0])) {
            if (in_array($param[0], array("main", "database", "statistics", "logs", "memory", "index", "hardware", "system", "id","cache"))) {
                $_GET['path'] = LINK.__CLASS__.'/'.__FUNCTION__.'/'.$param[0];
            }
        }

        if (empty($_GET['path']) && empty($param[0])) {
            $_GET['path'] = $data['menu']['main']['path'];
            $param[0]     = 'main';
        }

        if (empty($_GET['path'])) {
            $_GET['path'] = 'main';
        }


        //@TODO bug with item not selected (empty) need put case by default

        $this->title  = '<span class="glyphicon glyphicon glyphicon-home"></span> '.__("Dashboard");
        $this->ariane = ' > <a href⁼"">'.'<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '.__("Dashboard").'</a> > '.$data['menu'][$param[0]]['icone'].' '.$data['menu'][$param[0]]['name'];

        $this->set('data', $data);
    }

    public function main()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->title  = __("Dashboard");
        $this->ariane = " > ".$this->title;

        $this->di['js']->code_javascript('
        $("#checkAll").click(function(){
    $("input:checkbox").not(this).prop("checked", this.checked);
});

');
        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            if (!empty($_POST['is_monitored'])) {

                //start transaction !
                $sql = "UPDATE mysql_server a SET is_monitored='0' WHERE 1 ".$this->getFilter();

                $db->sql_query($sql);

                if (!empty($_POST['monitored'])) {
                    foreach ($_POST['monitored'] as $key => $val) {
                        //ugly to optimize but no time now
                        if ($val == "on") {
                            $sql = "UPDATE mysql_server a SET is_monitored='1' WHERE id='".$key."' ".$this->getFilter();
                            $db->sql_query($sql);
                        }
                    }
                }
            }

            //header
        }


        $sql = "SELECT a.*, b.version, b.is_available,c.libelle as client,d.libelle as environment FROM mysql_server a 
                     INNER JOIN client c on c.id = a.id_client 
                 INNER JOIN environment d on d.id = a.id_environment
                 LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
                 WHERE 1 ".$this->getFilter()."
                 order by `name`;";

        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function database()
    {

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
                        ".$this->getFilter()."
			GROUP BY a.id";

        $data['servers'] = $db->sql_fetch_yield($sql);
        $this->set('data', $data);
    }

    public function statistics()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $fields = array("Com_select", "Com_update", "Com_insert", "Com_delete", "Threads_connected", "Uptime", "Com_commit", "Com_rollback",
            "Com_begin", "Com_replace");
        $sql    = $this->buildQuery($fields);
        $res    = $db->sql_query($sql);

        //echo \SqlFormatter::format($sql);

        $data['servers'] = $db->sql_fetch_yield($sql);

        //$db->sql_query("DROP TABLE IF EXISTS `temp`");

        $this->set('data', $data);
    }

    public function logs()
    {
        //used with recursive
    }

    private function buildQuery($fields)
    {
        $sql = 'select a.ip, a.port, a.id, a.name,';

        $i   = 0;
        $tmp = [];
        foreach ($fields as $field) {
            $tmp[] = " c$i.value as $field";
            $i++;
        }

        $sql .= implode(",", $tmp);
        $sql .= " from mysql_server a ";
        $sql .= " INNER JOIN status_max_date b ON a.id = b.id_mysql_server ";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " LEFT JOIN status_value_int c$i ON c$i.id_mysql_server = a.id AND b.date = c$i.date";
            $sql .= " LEFT JOIN status_name d$i ON d$i.id = c$i.id_status_name ";
            $i++;
        }

        $sql .= " WHERE 1 ".$this->getFilter()."";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " AND d$i.name = '".$field."'  ";
            $i++;
        }

        $sql .=";";
        return $sql;
    }

    public function memory()
    {
        $this->layout_name = 'pmacontrol';
        $this->title       = __("Memory");
        $this->ariane      = " > ".__("Tools Box")." > ".$this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql     = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server 
            WHERE is_available = 1 ".$this->getFilter()."
            order by a.`name`";
        $res50   = $default->sql_query($sql);

        $data = [];
        while ($ob50 = $default->sql_fetch_object($res50)) {
            $db                             = $this->di['db']->sql($ob50->name);
            $data['variables'][$ob50->name] = $db->getVariables();
            $data['status'][$ob50->name]    = $db->getStatus();
            $data['memory'][$ob50->name]    = $ob50->memory_kb;
        }
        $this->set('data', $data);
    }

    public function index()
    {
        $this->layout_name = 'pmacontrol';

        $this->title  = __("Index usage");
        $this->ariane = " > ".__("Tools Box")." > ".$this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql     = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server
            WHERE is_available = 1 ".$this->getFilter()."
            order by `name`";
        $res50   = $default->sql_query($sql);

        $data = [];
        while ($ob50 = $default->sql_fetch_object($res50)) {

            $db                          = $this->di['db']->sql($ob50->name);
            $data['status'][$ob50->name] = $db->getStatus();
        }


        $this->set('data', $data);
    }
    /*
     * 
     * graph with Chart.js
     * 
     */

    public function id($param)
    {

        /*
          select avg(Column), convert((min(datetime) div 500)*500 + 230, datetime) as time
          from Databasename.tablename
          where datetime BETWEEN '2012-09-08 00:00:00' AND '2012-09-08 15:30:00'
          group by datetime div 500
         *
         * select avg(Column), convert((min(datetime) div 500)*500 + 230, datetime) as time
          from Databasename.tablename
          where datetime BETWEEN '2012-09-08 00:00:00' AND '2012-09-08 15:30:00'
          group by datetime div 500
         */
        $this->di['js']->addJavascript(array("Chart.min.js")); //,
        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $sql = "SELECT * FROM mysql_server where id='".$_POST['mysql_server']['id']."'";
            $res = $db->sql_query($sql);
            while ($ob  = $db->sql_fetch_object($res)) {
                $id_mysql_server = $ob->id;

                header('location: '.LINK.__CLASS__
                    .'/listing/id/mysql_server:id:'.$id_mysql_server
                    .'/status_name:id:'.$_POST['status_name']['id']
                    .'/status_value_int:date:'.$_POST['status_value_int']['date']
                    .'/status_value_int:derivate:'.$_POST['status_value_int']['derivate']);
            }
        } else {

            // get server available
            $sql             = "SELECT * FROM mysql_server a WHERE error = '' ".$this->getFilter()." order by a.name ASC";
            $res             = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob              = $db->sql_fetch_object($res)) {
                $tmp               = [];
                $tmp['id']         = $ob->id;
                $tmp['libelle']    = $ob->name." (".$ob->ip.")";
                $data['servers'][] = $tmp;
            }


            // get server available
            $sql            = "SELECT * FROM status_name order by name ASC";
            $res            = $db->sql_query($sql);
            $data['status'] = array();
            while ($ob             = $db->sql_fetch_object($res)) {
                $tmp              = [];
                $tmp['id']        = $ob->id;
                $tmp['libelle']   = $ob->name;
                $data['status'][] = $tmp;
            }


            $interval = array('5 minute', '15 minute', '1 hour', '2 hour', '6 hour', '12 hour', '1 day', '2 day', '1 week', '2 week', '1 month');
            $libelles = array('5 minutes', '15 minutes', '1 hour', '2 hours', '6 hours', '12 hours', '1 day', '2 days', '1 week', '2 weeks',
                '1 month');
            $elems    = array(60 * 5, 60 * 15, 3600, 3600 * 2, 3600 * 6, 3600 * 12, 3600 * 24, 3600 * 48, 3600 * 24 * 7, 3600 * 24 * 14, 3600
                * 24 * 30);

            $data['interval'] = array();
            $i                = 0;
            foreach ($libelles as $libelle) {
                $tmp                = [];
                $tmp['id']          = $interval[$i];
                $tmp['libelle']     = $libelle;
                $data['interval'][] = $tmp;
                $i++;
            }

            $data['derivate'][0]['id']      = 1;
            $data['derivate'][0]['libelle'] = __("Yes");
            $data['derivate'][1]['id']      = 2;
            $data['derivate'][1]['libelle'] = __("No");

            if (empty($_GET['status_value_int']['date'])) {
                $_GET['status_value_int']['date'] = "6 hour";
            }

            if (!empty($_GET['mysql_server']['id']) && !empty($_GET['status_name']['id']) && !empty($_GET['status_value_int']['date']) && !empty($_GET['status_value_int']['derivate'])
            ) {
                $sql = "SELECT * FROM status_value_int a
                    
                    WHERE a.id_mysql_server = ".$_GET['mysql_server']['id']." 
                    AND a.id_status_name = '".$_GET['status_name']['id']."'
                    and a.`date` > date_sub(now(), INTERVAL ".$_GET['status_value_int']['date'].") ORDER BY a.`date` ASC;";


                $data['sql']   = $sql;
                $data['graph'] = $db->sql_fetch_yield($sql);
                $dates         = [];
                $val           = [];





                $sql2 = "SELECT name FROM status_name WHERE id= '".$_GET['status_name']['id']."'";


                //debug($sql2);

                $res2 = $db->sql_query($sql2);

                while ($ob2 = $db->sql_fetch_object($res2)) {
                    $name = $ob2->name;
                }

                $i = 0;

                $old_date = "";
                $points   = [];

                foreach ($data['graph'] as $value) {

                    if (empty($old_date) && $_GET['status_value_int']['derivate'] == "1") {

                        $old_date  = $value['date'];
                        $old_value = $value['value'];
                        continue;
                    } elseif ($_GET['status_value_int']['derivate'] == "1") {

                        $datetime1 = strtotime($old_date);
                        $datetime2 = strtotime($value['date']);

                        $secs = $datetime2 - $datetime1; // == <seconds between the two times>
                        //echo $datetime1. ' '.$datetime2 . ' : '. $secs." ".$value['value'] ." - ". $old_value." => ".($value['value']- $old_value)/ $secs."<br>";

                        $derivate = round(($value['value'] - $old_value) / $secs, 2);

                        if ($derivate < 0) {
                            $derivate = 0;
                        }

                        $val[] = $derivate;

                        //$points[] = "{ x: " . $datetime2 . ", y :" . $derivate . "}";
                    } else {
                        $val[] = $value['value'];
                    }



                    //$points[] = "{ x: " . $datetime2 . "000, y :" . $derivate . "}";

                    $dates[] = $value['date'];

                    $old_date  = $value['date'];
                    $old_value = $value['value'];
                }



                $date = implode('","', $dates);
                $vals = implode(',', $val);
                //$arr_points = implode(',', $points);


                $this->di['js']->code_javascript('
var ctx = document.getElementById("myChart");

var myChart = new Chart(ctx, {
    type: "line",
    data: {
        labels: ["'.$date.'"],
        datasets: [{    
            label: "'.ucwords($name).'",
            data: ['.$vals.'],
                borderWidth: 1,
             pointBackgroundColor: "#000",
             pointRadius :0
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:false
                }
            }]
        },
        pointDot : false,
    }
});
');
            } else {
                if (empty($data['servers'])) $data['servers']  = "";
                if (empty($data['status'])) $data['status']   = "";
                if (empty($data['interval'])) $data['interval'] = "";
                if (empty($data['derivate'])) $data['derivate'] = "";


                $data['fields_required'] = 1;
            }



            $this->set('data', $data);
        }
    }

    //to mutualize
    private function getFilter()
    {

        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (".implode(',', json_decode($client, true)).")";
        }


        return $where;
    }

    public function settings()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['settings'])) {

                foreach ($_POST['id'] as $key => $value) {

                    if (empty($_POST['mysql_server'][$key]['is_monitored'])) {
                        $_POST['mysql_server'][$key]['is_monitored'] = 0;
                    } else {
                        $_POST['mysql_server'][$key]['is_monitored'] = 1;
                    }


                    $server_main                                   = array();
                    $server_main['mysql_server']['id']             = $value;
                    $server_main['mysql_server']['display_name']   = $_POST['mysql_server'][$key]['display_name'];
                    $server_main['mysql_server']['id_client']      = $_POST['mysql_server'][$key]['id_client'];
                    $server_main['mysql_server']['id_environment'] = $_POST['mysql_server'][$key]['id_environment'];
                    $server_main['mysql_server']['is_monitored']   = $_POST['mysql_server'][$key]['is_monitored'];


                    $ret = $db->sql_save($server_main);

                    if (!$ret) {
                        debug($server_main);
                        print_r($db->sql_error());
                    }
                }

                header("location: ".LINK."Server/settings");
                exit;
            }
        }



        $this->title  = '<i class="fa fa-server"></i> '.__("Servers");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-cog" style="font-size:14px"></i> '
            .__("Settings").'</a> > <i class="fa fa-server"  style="font-size:14px"></i> '.__("Servers");





        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            
        }

        $sql             = "SELECT * FROM mysql_server a WHERE 1=1 ".$this->getFilter()." ORDER by name";
        $data['servers'] = $db->sql_fetch_yield($sql);


        $data['clients']      = $this->getClients();
        $data['environments'] = $this->getEnvironments();



        $this->set('data', $data);
    }

    public function getClients()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);

        $data['client'] = array();

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        return $data['client'];
    }

    public function getEnvironments()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);


        $data['environment'] = array();
        while ($ob                  = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }


        return $data['environment'];
    }

    public function add()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);
    }


    public function cache()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);
        

        // Qcache_hits / (Qcache_hits + Com_select )
    }
}