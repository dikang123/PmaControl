<?php

/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK 
 */

use Glial\Synapse\Controller;
use Glial\Neuron\PmaCli\PmaCliDraining;
use Glial\Cli\Table;
use \Glial\I18n\I18n;

/*
  declare(ticks = 1);
  pcntl_signal(SIGTERM, "sig_handler");
  pcntl_signal(SIGHUP, "sig_handler");
  pcntl_signal(SIGUSR1, "sig_handler");



  // signal handler function
  function sig_handler($signo)
  {

  switch ($signo) {
  case SIGTERM:
  echo "handle shutdown tasks \n";
  // handle shutdown tasks
  exit;
  break;
  case SIGHUP:
  // handle restart tasks
  echo "handle shutdown tasks \n";
  break;
  case SIGUSR1:
  echo "Caught SIGUSR1...\n";
  break;
  case SIGINT:
  printf("Warning: interrupt received, killing server…%s", PHP_EOL);

  break;
  default:
  echo "all other signals\n";
  // handle all other signals
  }
  } */

class Cleaner extends Controller {

    private function anonymous() {
        $fct = function() {
            $default = $this->di['db']->sql(DB_DEFAULT);

            $id_pmacli_drain_process = $this->id_pmacli_drain_process;
        };
    }

    public function statistics($param) {

        $default = $this->di['db']->sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array('jquery-latest.min.js',
            'jQplot/jquery.jqplot.min.js',
            'jQplot/plugins/jqplot.dateAxisRenderer.min.js',
            //  'jQplot/plugins/jqplot.barRenderer.min.js',
//  'jQplot/plugins/jqplot.pointLabels.min.js',
            'jQplot/plugins/jqplot.categoryAxisRenderer.min.js',
            'jQplot/plugins/jqplot.canvasTextRenderer.min.js',
            'jQplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
            'jQplot/plugins/jqplot.ohlcRenderer.min.js',
            'jQplot/plugins/jqplot.highlighter.min.js',
            'jQplot/plugins/jqplot.pointLabels.min.js',
            'jqplot.categoryAxisRenderer.min.js'));


        $sql = "SELECT `table`, avg(row) as avg FROM `pmacli_drain_item` GROUP BY `table` ORDER by `table`;";


        $sql = "SELECT `item_deleted`, time as avg, `date_end` as date_end
                FROM `pmacli_drain_process` 
                WHERE name='" . $param[0] . "' 
                GROUP BY 
                ;";


        $sql = "SELECT max(date_end) as date_end,
            HOUR(date_end) as hour,
                 avg(time) as avg,
                 max(time) as max,
                 min(time) as min,
                "
                . " sum(item_deleted) as item_deleted "
                . "FROM `pmacli_drain_process` "
                . "where name='" . $param[0] . "' and date_end >= ADDDATE(now(), INTERVAL -1 DAY) GROUP BY HOUR(date_end) order by max(date_end)";

//echo $sql;


        $hour = $default->sql_fetch_yield($sql);
        $data = [];

        $data['param'] = $param;

        foreach ($hour as $line) {

            $data['nb_item'][] = "['" . $line['date_end'] . "'," . $line['item_deleted'] . "]";
            $data['avg'][] = "['" . $line['date_end'] . "'," . $line['avg'] . "]";
            $data['min'][] = "['" . $line['date_end'] . "'," . $line['min'] . "]";
            $data['max'][] = "['" . $line['date_end'] . "'," . $line['max'] . "]";
        }


        $sql = "SELECT max(date_end) as date_end,
            day(date_end) as day,
                 avg(time) as avg,
                 max(time) as max,
                 min(time) as min,
                "
                . " sum(item_deleted) as item_deleted "
                . "FROM `pmacli_drain_process` "
                . "where name='" . $param[0] . "'  and date_end >= ADDDATE(now(), INTERVAL -1 WEEK) GROUP BY DAY(date_end) order by max(date_end)";

//echo $sql;

        $day = $default->sql_fetch_yield($sql);

        foreach ($day as $line) {
            $data['day_nb_item'][] = "['" . $line['date_end'] . "'," . $line['item_deleted'] . "]";
            $data['day_avg'][] = "['" . $line['date_end'] . "'," . $line['avg'] . "]";
            $data['day_min'][] = "['" . $line['date_end'] . "'," . $line['min'] . "]";
            $data['day_max'][] = "['" . $line['date_end'] . "'," . $line['max'] . "]";
        }

        $sql = "SELECT max(date_end) as date_end,
            month(date_end) as month,
                 avg(time) as avg,
                 max(time) as max,
                 min(time) as min,
                "
                . " sum(item_deleted) as item_deleted "
                . "FROM `pmacli_drain_process` "
                . "where name='" . $param[0] . "'  and date_end >= ADDDATE(now(), INTERVAL -1 YEAR) GROUP BY month(date_end) order by max(date_end)";

// echo $sql;

        $day = $default->sql_fetch_yield($sql);

        foreach ($day as $line) {
            $data['year_nb_item'][] = "['" . $line['date_end'] . "'," . $line['item_deleted'] . "]";
            $data['year_avg'][] = "['" . $line['date_end'] . "'," . $line['avg'] . "]";
            $data['year_min'][] = "['" . $line['date_end'] . "'," . $line['min'] . "]";
            $data['year_max'][] = "['" . $line['date_end'] . "'," . $line['max'] . "]";
        }

        $sql = "SELECT max(date_end) as date_end,
            day(date_end) as day2,
                 avg(time) as avg,
                 max(time) as max,
                 min(time) as min,
                "
                . " sum(item_deleted) as item_deleted "
                . "FROM `pmacli_drain_process` "
                . "where name='" . $param[0] . "'  and date_end >= ADDDATE(now(), INTERVAL -1 MONTH) GROUP BY DAY(date_end) order by max(date_end)";

// echo $sql;

        $day = $default->sql_fetch_yield($sql);

        foreach ($day as $line) {
            $data['day2_nb_item'][] = "['" . $line['date_end'] . "'," . $line['item_deleted'] . "]";
            $data['day2_avg'][] = "['" . $line['date_end'] . "'," . $line['avg'] . "]";
            $data['day2_min'][] = "['" . $line['date_end'] . "'," . $line['min'] . "]";
            $data['day2_max'][] = "['" . $line['date_end'] . "'," . $line['max'] . "]";
        }



        $this->di['js']->code_javascript("$(document).ready(function(){
            

var line1=[" . implode(',', $data['nb_item']) . "];
var line2=[" . implode(',', $data['avg']) . "];
var line3=[" . implode(',', $data['min']) . "];
var line4=[" . implode(',', $data['max']) . "];

var line11=[" . implode(',', $data['day_nb_item']) . "];
var line12=[" . implode(',', $data['day_avg']) . "];
var line13=[" . implode(',', $data['day_min']) . "];
var line14=[" . implode(',', $data['day_max']) . "];
    
var line21=[" . implode(',', $data['year_nb_item']) . "];
var line22=[" . implode(',', $data['year_avg']) . "];
var line23=[" . implode(',', $data['year_min']) . "];
var line24=[" . implode(',', $data['year_max']) . "];

var line31=[" . implode(',', $data['day2_nb_item']) . "];
var line32=[" . implode(',', $data['day2_avg']) . "];
var line33=[" . implode(',', $data['day2_min']) . "];
var line34=[" . implode(',', $data['day2_max']) . "];

var legendLabels = ['Commandes effacé par heure', 'Traitement moyen d\'un run', 'Traitement minimum d\'un run', 'Traitement maximum d\'un run'];


    
  var plot1 = $.jqplot('chart1', [line1,line2,line3,line4], {
      title:'La dernière journée', 
       seriesDefaults: { 
        showMarker:true
      },
      
      legend:{
    show:true, 
    location: 'w',
    labels: legendLabels,
    rendererOptions:{numberRows: 4, placement: 'inside'}
},    
      axes:{
        xaxis:{           
         renderer:$.jqplot.DateAxisRenderer, 
         // renderer:$.jqplot.BarRenderer,
          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
          tickOptions:{formatString:'%H:00', angle:0}
        }
      },
              series:[
            {yaxis:'yaxis', label:'dataForAxis1'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'}
        ]
  });

  var plot2 = $.jqplot('chart2', [line11,line12,line13,line14], {
      title:'La dernière semaine', 
       seriesDefaults: { 
        showMarker:true,
        pointLabels: { show:false } 
      },
      
      legend:{
    show:false, 
    location: 'w',
    labels: legendLabels,
    rendererOptions:{numberRows: 4, placement: 'inside'}
},    
      axes:{
        xaxis:{           
         renderer:$.jqplot.DateAxisRenderer, 
         // renderer:$.jqplot.BarRenderer,
          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
          tickOptions:{formatString:'%A', angle:30}
        }
      },
              series:[
            {yaxis:'yaxis', label:'dataForAxis1'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'}
        ]
  });


  var plot3 = $.jqplot('chart3', [line21,line22,line23,line24], {
      title:'La dernière année', 
       seriesDefaults: { 
        showMarker:true
      },
      
      legend:{
    show:false, 
    location: 'w',
    labels: legendLabels,
    rendererOptions:{numberRows: 4, placement: 'inside'}
},    
      axes:{
        xaxis:{           
         renderer:$.jqplot.DateAxisRenderer, 
         // renderer:$.jqplot.BarRenderer,
          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
          tickOptions:{formatString:'%B', angle:30}
        }
      },
              series:[
            {yaxis:'yaxis', label:'dataForAxis1'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'}
        ]
  });

  
  var plot4 = $.jqplot('chart4', [line31,line32,line33,line34], {
      title:'La dernier mois', 
       seriesDefaults: { 
        showMarker:true
      },
      
      legend:{
    show:false, 
    location: 'w',
    labels: legendLabels,
    rendererOptions:{numberRows: 4, placement: 'inside'}
},    
      axes:{
        xaxis:{           
         renderer:$.jqplot.DateAxisRenderer, 
         // renderer:$.jqplot.BarRenderer,
          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
          tickOptions:{formatString:'%d', angle:30}
        }
      },
              series:[
            {yaxis:'yaxis', label:'dataForAxis1'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'},
            {yaxis:'y2axis', label:'dataForAxis2'}
        ]
  });

});");





        $this->set('data', $data);
    }

    function getIdMysqlServer($name) {

        $default = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id FROM mysql_server WHERE name ='" . $name . "';";
        $res_id_mysql_server = $default->sql_query($sql);
        if ($default->sql_num_rows($res_id_mysql_server) == 1) {
            $ob = $default->sql_fetch_object($res_id_mysql_server);
            $id_mysql_server = $ob->id;
        } else {
            throw new \Exception("PMACTRL-001 : Impossible to find the MySQL server");
        }

        return $id_mysql_server;
    }

    function getMsgStartDaemon($ob) {
        $table = new Table(0);

        echo "Starting deamon for cleaner ..." . PHP_EOL;


        $table->addHeader(array("Parameter", "Value"));
        $table->addLine(array("SERVER_TO_PURGE", $ob->link_to_purge));
        $table->addLine(array("DATABASE_TO_PURGE", $ob->schema_to_purge));
        $table->addLine(array("TABLES_TO_SET_FIRST", implode(",", $ob->table_to_purge)));
        $table->addLine(array("INIT_DATA_WITH", $ob->main_table));
        $table->addLine(array("QUERY", $ob->query));
        $table->addLine(array("WAIT_TIME", $ob->wait_time_in_sec));

        echo $table->display();
    }

    private function checkPid($path_file_pid) {

        if (file_exists($path_file_pid)) {
            $pid = file_get_contents($path_file_pid);
            $msg = "Error a daemon already started with pid : '" . trim($pid) . "' !";
            error_log($msg);
            throw new \Exception("PMACTRL-004 : " . $msg);
        } else {
            $pid = getmypid();
            file_put_contents($path_file_pid, $pid);
        }
    }

    public function showDaemon() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `pmacli_drain_process` order by date_start DESC LIMIT 5000";

        $data['clean'] = $db->sql_fetch_yield($sql);


        $this->set('data', $data);
    }

    public function index($param) {


        $db = $this->di['db']->sql(DB_DEFAULT);

        /** new cleaner with UI * */
        $sql = "SELECT *,a.id as id_cleaner_main,
            b.name as mysql_server_name
        FROM cleaner_main a
        INNER JOIN mysql_server b ON a.id_mysql_server = b.id";

        $data['cleaner_main'] = $db->sql_fetch_yield($sql);

        $sql = "SELECT DISTINCT `name` FROM pmacli_drain_process";
        $data['cleaner_name'] = $db->sql_fetch_yield($sql);

        $data['cleaner_name'] = iterator_to_array($data['cleaner_name']);


        $data['id_cleaner'] = empty($param[0]) ? 0 : $param[0];
        $data['menu'] = empty($param[1]) ? "log" : $param[1];


        $this->title = __("Cleaner");
        $this->ariane = " > " . $this->title . " > " . $data['id_cleaner'];
        $this->layout_name = 'pmacontrol';

        $this->di['js']->addJavascript(array('jquery-latest.min.js',
            'cleaner/index.cleaner.js',
            'jQplot/jquery.jqplot.min.js',
            'jQplot/plugins/jqplot.dateAxisRenderer.min.js',
            //  'jQplot/plugins/jqplot.barRenderer.min.js',
//  'jQplot/plugins/jqplot.pointLabels.min.js',
            'jQplot/plugins/jqplot.categoryAxisRenderer.min.js',
            'jQplot/plugins/jqplot.canvasTextRenderer.min.js',
            'jQplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
            'jQplot/plugins/jqplot.ohlcRenderer.min.js',
            'jQplot/plugins/jqplot.highlighter.min.js',
            'jQplot/plugins/jqplot.pointLabels.min.js',
            'jqplot.categoryAxisRenderer.min.js'));




        /*
        $sql = "SELECT `table`, avg(row) as avg FROM `pmacli_drain_item` GROUP BY `table` ORDER by `table`;";


        $sql = "SELECT `item_deleted`, time as avg, `date_end` as date_end
                FROM `pmacli_drain_process` 
                WHERE name='" . $param[0] . "' 
                GROUP BY 
                ;";


        $sql = "SELECT max(date_end) as date_end,
            HOUR(date_end) as hour,
                 avg(time) as avg,
                 max(time) as max,
                 min(time) as min,
                "
                . " sum(item_deleted) as item_deleted "
                . "FROM `pmacli_drain_process` "
                . "where name='" . $param[0] . "' and date_end >= ADDDATE(now(), INTERVAL -1 DAY) GROUP BY HOUR(date_end) order by max(date_end)";

//echo $sql;


        $hour = $db->sql_fetch_yield($sql);
*/


        $this->set('data', $data);
    }

    public function treatment($param) {


        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM `pmacli_drain_process` WHERE `name`='" . $param[0] . "' ORDER BY date_start DESC LIMIT 100";
        $data['treatment'] = $db->sql_fetch_yield($sql);
        $data['process'] = $param[0];
        $this->set('data', $data);
    }

    public function removeOldData($db_name) {

        $name = str_replace('-', '_', $db_name[0]);

        $db = $this->di['db']->sql($name);

        $sql = "USE PRODUCTION";
        $db->sql_query($sql);

        $total = 0;

        $PROD_INTEGRATION_IPROD = 0;
        $AFF_COLIS = 0;
        $PROD_CTRL_POIDS_QO = 0;
        $PROD_STAT_SHIPPING_COST = 0;
        $PROD_TRACES = 0;


        do {
            $table = new Table(1);
            $table->addHeader(array("Tables", "Current", "Total deleted"));

            $sql = "SET @@skip_replication = ON;";
            $db->sql_query($sql);


            $sql = "DELETE from PROD_INTEGRATION_IPROD WHERE DATE_PASSAGE < DATE_ADD(now(),INTERVAL -3 MONTH) LIMIT 1000;";
            $db->sql_query($sql);
            $deleted = $db->sql_affected_rows();
            $PROD_INTEGRATION_IPROD += $deleted;
            $table->addLine(array("PROD_INTEGRATION_IPROD", $deleted, $PROD_INTEGRATION_IPROD));


            $sql = "DELETE from AFF_COLIS WHERE DATE_EXPEDITION  < DATE_ADD(now(),INTERVAL -3 MONTH) LIMIT 1000;";
            $db->sql_query($sql);
            $deleted = $db->sql_affected_rows();
            $AFF_COLIS += $deleted;
            $table->addLine(array("AFF_COLIS", $deleted, $AFF_COLIS));

            $sql = "DELETE from PROD_CTRL_POIDS_QO WHERE DATE_PASSAGE < DATE_ADD(now(),INTERVAL -3 MONTH) LIMIT 1000;";
            $db->sql_query($sql);
            $deleted = $db->sql_affected_rows();
            $PROD_CTRL_POIDS_QO += $deleted;
            $table->addLine(array("PROD_CTRL_POIDS_QO", $deleted, $PROD_CTRL_POIDS_QO));


            $sql = "DELETE from PROD_STAT_SHIPPING_COST WHERE DATE_CONTROLEUR  < DATE_ADD(now(),INTERVAL -3 MONTH) LIMIT 1000;";
            $db->sql_query($sql);
            $deleted = $db->sql_affected_rows();
            $PROD_STAT_SHIPPING_COST += $deleted;
            $table->addLine(array("PROD_STAT_SHIPPING_COST", $deleted, $PROD_STAT_SHIPPING_COST));


            $sql = "DELETE from PROD_TRACES WHERE DATE_PASSAGE < DATE_ADD(now(),INTERVAL -3 MONTH) LIMIT 1000;";
            $db->sql_query($sql);

            $deleted = $db->sql_affected_rows();
            $PROD_TRACES += $deleted;
            $table->addLine(array("PROD_TRACES", $deleted, $PROD_TRACES));

            echo $table->display();

            sleep(1);
            echo "\033[9A";
        } while (true);
    }

    public function detail($param) {



        $db = $this->di['db']->sql(DB_DEFAULT);

        $tmp = explode('/', $_GET['url']);
        $var = end($tmp);

        $sql = "SELECT * FROM pmacli_drain_item WHERE id_pmacli_drain_process = '" . $var . "' order by `table`";
        $data['detail'] = $db->sql_fetch_yield($sql);

        $sql = "SELECT a.`table`, avg(row) as row FROM pmacli_drain_item a
        INNER JOIN pmacli_drain_process b ON a.id_pmacli_drain_process = b.id
        WHERE name = '" . $param[0] . "'
        GROUP BY a.`table`";
        $data['avg'] = $db->sql_fetch_yield($sql);
        //var_dump($sql);

        $this->set('data', $data);
    }

    public function deleteOrphans($server_name) {
        $server_name = str_replace('-', '_', $server_name);

        $db = $this->di['db']->sql($server_name);
    }

    public function add($param) {
        $this->layout_name = 'pmacontrol';

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js", "jquery.autocomplete.min.js", "cleaner/add.cleaner.js"));

        $this->title = __('Add a cleaner');

        $this->ariane = " > " . '<a href="' . LINK . 'Cleaner/index/">' . __('Cleaner') . "</a> > " . $this->title;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            //var_dump($_POST);


            $data['cleaner_main'] = $_POST['cleaner_main'];

            $id_cleaner_main = $db->sql_save($data);

            if ($id_cleaner_main) {
                foreach ($_POST['cleaner_foreign_key'] as $data) {
                    $cleaner_foreign_key['cleaner_foreign_key'] = $data;
                    $cleaner_foreign_key['cleaner_foreign_key']['id_cleaner_main'] = $id_cleaner_main;

                    $id_cleaner_foreign_key = $db->sql_save($cleaner_foreign_key);
                }


                if ($id_cleaner_foreign_key) {
                    header('location: ' . LINK . 'Cleaner/index/');
                }
            }
        }


        $sql = "SELECT * FROM mysql_server order by `name`";
        $servers = $db->sql_fetch_yield($sql);


        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp = [];

            $tmp['id'] = $server['id'];
            $tmp['libelle'] = str_replace('_', '-', $server['name']) . " (" . $server['ip'] . ")";

            $data['server'][] = $tmp;
        }


        $data['wait_time'] = [];
        for ($i = 1; $i < 101; $i++) {
            $tmp = [];

            $tmp['id'] = $i;
            $tmp['libelle'] = $i;

            $data['wait_time'][] = $tmp;
        }


        $this->set('data', $data);
    }

    function getDatabaseByServer($param) {


        $this->layout_name = false;
        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($param[0]) . "';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_to_get_db = $this->di['db']->sql($ob->name);
        }

        $sql = "SHOW DATABASES";
        $res = $db_to_get_db->sql_query($sql);


        $data['databases'] = [];
        while ($ob = $db_to_get_db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->Database;
            $tmp['libelle'] = $ob->Database;

            $data['databases'][] = $tmp;
        }

        $this->set("data", $data);
    }

    function getTableByDatabase($param) {
        $database = $param[0];

        $this->layout_name = false;
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($_GET['id_mysql_server']) . "';";
        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {
            $id_server = $ob->id;
            $db_clean = $this->di['db']->sql($ob->name);
        }

        $sql = "SELECT TABLE_NAME from `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '" . $database . "' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME";

        $res = $db_clean->sql_query($sql);

        $data['table'] = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->TABLE_NAME;
            $tmp['libelle'] = $ob->TABLE_NAME;

            $data['table'][] = $tmp;
        }

        $this->set("data", $data);
    }

    function getColumnByTable($param) {

        $this->layout_name = false;
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($_GET['id_mysql_server']) . "';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_server = $ob->id;
            $db_clean = $this->di['db']->sql($ob->name);
        }

        $sql = "show index from `" . $_GET['schema'] . "`.`" . $param[0] . "`";
        //$sql = "SELECT TABLE_NAME from `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '".$database."' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME";

        echo $sql;

        $res = $db_clean->sql_query($sql);

        $data['column'] = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->Column_name;
            $tmp['libelle'] = $ob->Column_name;

            $data['column'][] = $tmp;
        }

        $this->set("data", $data);
    }

    function delete($param) {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "DELETE FROM cleaner_main where id ='" . $param[0] . "'";
        $db->sql_query($sql);
    }

    public function settings($param) {
        $this->layout_name = 'pmacontrol';

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js", "jquery.autocomplete.min.js", "cleaner/add.cleaner.js"));

        $this->title = __('Add a cleaner');

        $this->ariane = " > " . '<a href="' . LINK . 'Cleaner/index/">' . __('Cleaner') . "</a> > " . $this->title;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {



            $data['cleaner_main'] = $_POST['cleaner_main'];

            $id_cleaner_main = $db->sql_save($data);

            if ($id_cleaner_main) {
                foreach ($_POST['cleaner_foreign_key'] as $data) {
                    $cleaner_foreign_key['cleaner_foreign_key'] = $data;
                    $cleaner_foreign_key['cleaner_foreign_key']['id_cleaner_main'] = $id_cleaner_main;

                    $id_cleaner_foreign_key = $db->sql_save($cleaner_foreign_key);
                }


                if ($id_cleaner_foreign_key) {
                    header('location: ' . LINK . 'Cleaner/index/');
                }
            }
        }


        $sql = "SELECT * FROM mysql_server order by `name`";
        $servers = $db->sql_fetch_yield($sql);


        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp = [];

            $tmp['id'] = $server['id'];
            $tmp['libelle'] = str_replace('_', '-', $server['name']) . " (" . $server['ip'] . ")";

            $data['server'][] = $tmp;
        }


        $data['wait_time'] = [];
        for ($i = 1; $i < 101; $i++) {
            $tmp = [];

            $tmp['id'] = $i;
            $tmp['libelle'] = $i;

            $data['wait_time'][] = $tmp;
        }


        $this->set('data', $data);
    }

    public function daemon($param) {

        $id_cleaner = $param[0];
        $command = $param[1];


        switch ($command) {
            case 'stop':

                break;

            case 'start':

                break;

            case 'restart':

                break;

            default:
        }
    }

    public function launch($param) {
        $id_cleaner = $param[0];
        $default = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;
        $this->layout_name = false;

        $sql = "SELECT *, b.name as nameserver,a.id as id_cleaner_main
            FROM cleaner_main a
                INNER JOIN mysql_server b ON a.id_mysql_server = b.id
                WHERE a.id = '" . $id_cleaner . "'";

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {
            $cleaner = $ob;
        }

        if (empty($cleaner)) {
            throw new \Exception("PMACTRL-254 : Impossible to find id_cleaner_main = '" . $id_cleaner . "'");
        }

        $purge = new PmaCliDraining($this->di['db']);

        $purge->link_to_purge = $cleaner->nameserver;
        $purge->schema_to_purge = $cleaner->database;
        $purge->schema_delete = $cleaner->cleaner_db;
        $purge->prefix = $cleaner->prefix;
        $purge->debug = false;



        //get and set virtual Foreign keys.
        $sql = "SELECT * FROM cleaner_foreign_key WHERE id_cleaner_main = '" . $id_cleaner . "'";
        $foreign_keys = $default->sql_fetch_yield($sql);

        foreach ($foreign_keys as $line) {
            $fk[$line['constraint_schema']][$line['constraint_table']][$line['constraint_column']] = $line['referenced_schema'] . "-" . $line['referenced_table'] . "-" . $line['referenced_column'];
        }
        $purge->foreign_keys = $fk;


        $purge->main_table = $cleaner->main_table;

        $i = 1;

        $this->WAIT_TIME = $cleaner->wait_time_in_sec;
        //$this->getMsgStartDaemon($purge);

        echo "Stating Cleaner with id : $id_cleaner\n";
        echo $cleaner->query."\n";
        
        $default->sql_close();

        while (true) {

//posix_kill(posix_getpid(), SIGUSR1);
            $purge->init_where = $cleaner->query;

            $date_start = date("Y-m-d H:i:s");
            $time_start = microtime(true);
            $ret = $purge->start();

            if (empty($ret[$purge->main_table])) {
                $ret[$purge->main_table] = 0;
            }

            $time_end = microtime(true);
            $date_end = date("Y-m-d H:i:s");


//$id_mysql_server = $this->getIdMysqlServer($purge->link_to_purge);


            $default = $this->di['db']->sql(DB_DEFAULT);

            $data = array();
            $data['pmacli_drain_process']['id_mysql_server'] = $cleaner->id_mysql_server;
            $data['pmacli_drain_process']['date_start'] = $date_start;
            $data['pmacli_drain_process']['date_end'] = $date_end;
            $data['pmacli_drain_process']['time'] = round($time_end - $time_start, 2);
            $data['pmacli_drain_process']['item_deleted'] = $ret[$purge->main_table];
            $data['pmacli_drain_process']['id_cleaner_main'] = $id_cleaner;
            $data['pmacli_drain_process']['name'] = $cleaner->libelle;
            //$data['pmacli_drain_process']['time_by_item'] = round($data['pmacli_drain_process']['time'] / $this->NB_ELEM, 4);

            $res = $default->sql_save($data);

            if (!$res) {
                debug($default->sql_error());
                debug($data);

                throw new \Exception("PMACTRL-002 : Impossible to insert stat for cleaner (ID : " . $id_cleaner . ")");
            } else {

                $id_pmacli_drain_process = $default->sql_insert_id();

                foreach ($ret as $table => $val) {

                    if (!empty($val)) {

                        $data = [];
                        $data['pmacli_drain_item']['id_pmacli_drain_process'] = $id_pmacli_drain_process;
                        $data['pmacli_drain_item']['row'] = $val;
                        $data['pmacli_drain_item']['table'] = $table;

                        $res = $default->sql_save($data);

                        if (!$res) {
                            debug($default->sql_error());
                            debug($data);

                            throw new \Exception("PMACTRL-003 : Impossible to insert an item for cleaner (ID : " . $id_cleaner . ")");
                        }
                    }
                }
            }

            $i++;

            echo "[" . date('Y-m-d H:i:s') . "] Execution time : " . round($time_end - $time_start, 2) . " - Comandes deleted : " . $ret[$purge->main_table] . "\n";

            $default->sql_close(); //to prevent mysql gone away

            sleep($this->WAIT_TIME);
        }
    }

    function start($param) {

        $id_cleaner = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;
        $this->layout_name = false;


        $sql = "SELECT * FROM cleaner_main where id ='" . $id_cleaner . "'";
        $res = $db->sql_query($sql);


        if ($db->sql_num_rows($res) !== 1) {
            $msg = I18n::getTranslation(__("Impossible to find the cleaner with the id : ") . "'" . $id_cleaner . "'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: " . LINK . "cleaner/index");
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($ob->pid === "0") {
            
            $php = explode(" ",shell_exec("whereis php"))[1];
            
            //todo add error flux in the log
            $cmd = $php." ".GLIAL_INDEX." Cleaner launch ".$id_cleaner." >> ".TMP."log/cleaner_[".  str_replace(" ", "_", $ob->libelle)."].log & echo $!";
            $pid = shell_exec($cmd);
            

            $sql = "UPDATE cleaner_main SET pid ='".$pid."' WHERE id = '".$id_cleaner."'";
            $db->sql_query($sql);

            $msg = I18n::getTranslation(__("The cleaner id (".$id_cleaner.") successfully started with") . " pid : ".$pid);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header("location: " . LINK . "cleaner/index");
        } else {
            
            $msg = I18n::getTranslation(__("Impossible to launch the cleaner with the id : ") . "'" . $id_cleaner . "'" . " (" . __("Already running !") . ")");
            $title = I18n::getTranslation(__("Error"));
            set_flash("caution", $title, $msg);
            header("location: " . LINK . "cleaner/index");
        }
    }

}
