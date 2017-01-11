<?php

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;

class Benchmark extends Controller
{

    public function run($param)
    {


        $this->view = false;

        $id_mysql_server = 487;

        $id_mysql_server = 632;
        $id_mysql_server = 633;


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id = ".$id_mysql_server;
        $res = $db->sql_query($sql);

        while ($mysql = $db->sql_fetch_object($res)) {


            $sql  = "SELECT * FROM benchmark_config";
            $res2 = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res2)) {

                $sql = "INSERT INTO benchmark_main
                    SET id_mysql_server = '".$id_mysql_server."',
                    date = '".date("c")."',
                    sysbench_version = '".shell_exec("sysbench --version")."',
                    threads = '".$ob->threads."',
                    tables_count = '".$ob->tables_count."',
                    table_size = '".$ob->table_size."',
                    max_time = '".$ob->max_time."'
                    ";

                $db->sql_query($sql);

                $id_benchmark_main = $db->_insert_id();


                $password = Crypt::decrypt($mysql->passwd, CRYPT_KEY);


                $prepare = 'sysbench --test=/usr/local/sysbench/tests/db/oltp.lua --mysql-host='.$mysql->ip.' --mysql-port='.$mysql->port;
                $prepare .= ' --mysql-user='.$mysql->login.' --mysql-password='.$password.' '
                    .'--mysql-db=sysbench --mysql-table-engine=InnoDB '
                    .'--oltp-tables-count='.$ob->tables_count.' --max-time='.$ob->max_time.' prepare';

                $threads = explode(',', $ob->threads);


                foreach ($threads as $thread) {
                    $cmd = 'sysbench --test=/usr/local/sysbench/tests/db/oltp.lua --mysql-host='.$mysql->ip.' --mysql-port='.$mysql->port.''
                        .' --mysql-user='.$mysql->login.' --mysql-password='.$password.' --mysql-db=sysbench --mysql-table-engine=InnoDB '
                        .'--oltp-tables-count='.$ob->tables_count.' --num-threads='.$thread.' --max-time='.$ob->max_time.' run';

                    echo $cmd."\n";



                    $input_lines = shell_exec($cmd);



                    $sql = "INSERT INTO benchmark_run
                    SET id_benchmark_main = '".$id_benchmark_main."',
                    `date` = '".date("c")."',
                    `threads`  = '".$thread."',

                    `read` = '".$this->getQueriesPerformedRead($input_lines)."',
                    `write` = '".$this->getQueriesPerformedWrite($input_lines)."',
                    `other` = '".$this->getQueriesPerformedOther($input_lines)."',
                    `total` = '".$this->getQueriesPerformedTotal($input_lines)."',
                    `transaction` = '".$this->getTransactions($input_lines)."',
                    `error` = '".$this->getErrors($input_lines)."',
                    `time` = '".$this->getTotalTime($input_lines)."',
                    `reponse_min` = '".$this->getReponseTimeMin($input_lines)."',
                    `reponse_max` = '".$this->getReponseTimeMax($input_lines)."',
                    `reponse_avg` = '".$this->getReponseTimeAvg($input_lines)."',
                    `reponse_percentile95` = '".$this->getReponseTime95percent($input_lines)."'
                    ";

                    //better to get PANIC and FATAL error and send to log
                    if (!empty($this->getQueriesPerformedRead($input_lines))) {
                        $db->sql_query($sql);
                    }
                }
            }
        }
    }

    public function getQueriesPerformedRead($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getQueriesPerformedWrite($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getQueriesPerformedOther($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+[\d]+[\s]+other:[\s]+([\d]+)[\s]+/Ux", $input_lines,
            $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getQueriesPerformedTotal($input_lines)
    {
        preg_match_all("/queries\sperformed:[\s]+read:[\s]+[\d]+[\s]+write:[\s]+[\d]+[\s]+other:[\s]+[\d]+[\s]+total:[\s]+([\d]+)[\s]+/Ux",
            $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getTransactions($input_lines)
    {
        preg_match_all("/transactions:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getErrors($input_lines)
    {
        preg_match_all("/ignored\serrors:[\s]+([\d]+)[\s]+/Ux", $input_lines, $output_array);

        if (isset($output_array[1][0])) {
            return $output_array[1][0];
        } else {
            return false;
        }
    }

    public function getTotalTime($input_lines)
    {
        preg_match_all("/total\stime:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return str_replace("s", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTimeMin($input_lines)
    {
        preg_match_all("/min:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTimeMax($input_lines)
    {
        preg_match_all("/max:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTimeAvg($input_lines)
    {
        preg_match_all("/avg:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {

            return str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function getReponseTime95percent($input_lines)
    {
        preg_match_all("/95\spercentile:[\s]+([\S]+)[\s]+/Ux", $input_lines, $output_array);

        if (!empty($output_array[1][0])) {
            str_replace("ms", "", $output_array[1][0]);
        } else {
            return false;
        }
    }

    public function testMoc()
    {
        $this->view = false;
        $data       = $this->moc();


        echo "Nombre de read : ".$this->getQueriesPerformedRead($data)."\n";
        echo "Nombre de write : ".$this->getQueriesPerformedWrite($data)."\n";
        echo "Nombre de other : ".$this->getQueriesPerformedOther($data)."\n";
        echo "Nombre de total : ".$this->getQueriesPerformedTotal($data)."\n";


        echo "Nombre de transaction  : ".$this->getTransactions($data)."\n";
        echo "Nombre de time : ".$this->getTotalTime($data)."\n";
        echo "Nombre de min : ".$this->getReponseTimeMin($data)."\n";
        echo "Nombre de max : ".$this->getReponseTimeMax($data)."\n";
        echo "Nombre de avg : ".$this->getReponseTimeAvg($data)."\n";
        echo "Nombre de percent95 : ".$this->getReponseTime95percent($data)."\n";

        // 13 benchmark_thread
    }

    public function moc()
    {
        return "LTP test statistics:
    queries performed:
        read:                            140098
        write:                           40010
        other:                           20007
        total:                           200115
    transactions:                        10000  (343.26 per sec.)
    read/write requests:                 180108 (6182.33 per sec.)
    other operations:                    20007  (686.75 per sec.)
    ignored errors:                      7      (0.24 per sec.)
    reconnects:                          0      (0.00 per sec.)

General statistics:
    total time:                          29.1327s
    total number of events:              10000
    total time taken by event execution: 1861.1741s
    response time:
         min:                                 74.15ms
         avg:                                186.12ms
         max:                                634.61ms
         approx.  95 percentile:             285.37ms

Threads fairness:
    events (avg/stddev):           156.2500/2.89
    execution time (avg/stddev):   29.0808/0.04";
    }

    public function graph()
    {

    }

    public function index()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array("Chart.min.js"));


        $sql = "select * from `benchmark_main` a
         INNER JOIN mysql_server b ON a.id_mysql_server = b.id
         ORDER BY b.name, a.date LIMIT 100";






        $sql = "SELECT max(id) as idmax from benchmark_main";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_benchmark_main = $ob->idmax;
        }

        $sql = "SELECT * FROM benchmark_run where `id_benchmark_main` = ".$id_benchmark_main;

        $res = $db->sql_query($sql);

        $threads      = [];
        $reads        = [];
        $write        = [];
        $reponse_time = [];
        $transaction  = [];
        $error        = [];

        while ($ob = $db->sql_fetch_object($res)) {

            $threads[]      = $ob->threads;
            $reads[]        = $ob->read / $ob->time;
            $write[]        = $ob->write / $ob->time;
            $reponse_time[] = $ob->reponse_avg;
            $transaction[]  = $ob->transaction / $ob->time;
            $error[]        = $ob->error;
        }

        $threads      = implode(',', $threads);
        $reads        = implode(',', $reads);
        $write        = implode(',', $write);
        $reponse_time = implode(',', $reponse_time);
        $transaction  = implode(',', $transaction);
        $error        = implode(',', $error);

        /*
          $this->di['js']->code_javascript('

          var rt = document.getElementById("rt").getContext("2d");

          var options = {
          pointDot : true,
          }

          var data_rt = {
          labels: "",
          datasets: [ ]
          };

          data_rt.datasets[0] = {
          fillColor: "rgba(252,215,95,0.4)",
          strokeColor: "rgba(252,215,95,1)",
          pointStrokeColor: "#fff",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(214,165,5,1)"
          };
          data_rt.datasets[0].label = "AVG";
          data_rt.datasets[0].data = ['.$reponse_time.'];


          var myLineChart_rt = new Chart(rt,data_rt);
          ');
         */

        $this->di['js']->code_javascript('
var ctx4 = document.getElementById("tps");

var myChart4 = new Chart(ctx4, {
    type: "line",
    data: {
        labels: ['.$threads.'],
        datasets: [{
            label: "Transactions by second",
            data: ['.$transaction.'],
             backgroundColor: "rgba(75,215,134,0.4)",
             borderColor: "rgba(75,215,134,1)",
             /*
             borderWidth: 1,
             pointBackgroundColor: "#000",
             pointRadius :0
             */
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

        $this->di['js']->code_javascript('
var ctx1 = document.getElementById("rds");

var myChart1 = new Chart(ctx1, {
    type: "line",
    data: {
        labels: ['.$threads.'],
        datasets: [{
            label: "Writes by second",
            data: ['.$write.'],
            backgroundColor: "rgba(255,168,168, 0.4)",
            borderColor: "rgba(255,168,168,1)",
        },{
            label: "Reads by second",
            data: ['.$reads.'],
            backgroundColor: "rgba(142,199,255,0.4)",
            borderColor: "rgba(142,199,255,1)",


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

/*
        $this->di['js']->code_javascript('
var ctx2 = document.getElementById("wrs");


var myChart2 = new Chart(ctx2, {
    type: "line",

    data: {
        labels: ['.$threads.'],
        datasets: [{            
            label: "Writes by second",
            data: ['.$write.'],
            backgroundColor: "rgba(255,168,168, 0.4)",
            borderColor: "rgba(255,168,168,1)",
            fill: true,            
        }],

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
*/

        $this->di['js']->code_javascript('
var ctx3 = document.getElementById("rt");

var myChart = new Chart(ctx3, {
    type: "line",
    data: {
        labels: ['.$threads.'],
        datasets: [{
            label: "Response Time",
            data: ['.$reponse_time.'],
            backgroundColor: ["rgba(252,215,95,0.4)"],
            borderColor: "rgba(252,215,95,1)"
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


        $this->di['js']->code_javascript('
          var ctx5 = document.getElementById("err");

          var myChart5 = new Chart(ctx5, {
            type: "line",
                data: {
                    labels: ['.$threads.'],
                    datasets: [{
                    label: "Errors by second",
                    data: ['.$error.'],
                    
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
    }

    public function testError($input_lines)
    {
        $pos = strpos($input_lines, "FATAL:");

        if ($pos !== false) {
            return true;
        }
        return false;
    }

    public function saveVariables($id_benchmark_main)
    {
        $name_server = $param[0];
        $id_server   = $param[1];

        $mysql_tested = @$this->di['db']->sql($name_server);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $variables = $mysql_tested->getVariables();
    }
}