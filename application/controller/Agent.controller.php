<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use \Glial\Cli\Color;

class Agent extends Controller
{
    var $debug = false;

    public function start()
    {
        
    }

    public function stop()
    {
        
    }

    public function launch($id)
    {
        
    }

    public function getMysqlInfo()
    {
        
    }

    /**
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description get list of MySQL server and try to connect on each one
     * @access public
     */
    public function testAllMysql($param)
    {
        if (!empty($param)) {
            foreach ($param as $elem) {
                if ($elem == "--debug") {
                    $this->debug = true;
                    echo Color::getColoredString("DEBUG activated !", "yellow")."\n";
                }
            }
        }

        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $sql        = "select * from mysql_server";
        $res        = $db->sql_query($sql);


        $server_list = array();
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        //to prevent any trouble with fork
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();
        $maxThreads      = 50; // check MySQL server 50 by 50
        $openThreads     = 0;
        $child_processes = array();

        if (empty($server_list)) {
            throw new Exception("List of server to test is empty", 20);
        }


        foreach ($server_list as $server) {
            //echo str_repeat("#", count($child_processes)) . "\n";

            $pid                   = pcntl_fork();
            $child_processes[$pid] = 1;

            if ($pid == -1) {
                throw new Exception('PMACTRL-057 : Couldn\'t fork thread !', 80);
            } else if ($pid) {
                if (count($child_processes) > $maxThreads) {
                    $childPid = pcntl_wait($status);
                    unset($child_processes[$childPid]);
                }
                $father = true;
            } else {
                // one thread to test each MySQL server
                $this->testMysqlServer($server);
                $father = false;
                //we want that child exit the foreach
                break;
            }
        }

        if ($father) {
            $tmp = $child_processes;
            foreach ($tmp as $thread) {
                $childPid = pcntl_wait($status);
                unset($child_processes[$childPid]);
            }
            echo "All child termined !\n";
        }
    }

    /**
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description launch a subprocess limited in time to try MySQL connection
     * @access public
     */
    private function testMysqlServer($server)
    {
        $this->view = false;

        //exeute a process with a timelimit (in case of MySQL don't answer and keep connection)
        $ret = SetTimeLimit::run("Agent", "tryMysqlConnection",
                array($server['name'], $server['id']), 3);


        ($this->debug) ? print_r($ret) : '';

        if (!SetTimeLimit::exitWithoutError($ret)) {
            /* in case of somthing wrong :
             * server don't answer
             * server didn't give msg 
             * wrong credentials
             * error in PHP script
             */

            $db  = $this->di['db']->sql(DB_DEFAULT);
            $sql = "UPDATE mysql_replication_stats SET is_available = 0 where id_mysql_server = '".$server['id']."'";
            $db->sql_query($sql);
            $db->sql_close();

            echo ($this->debug) ? $server['name']." KO \n" : "";
            return false;
        } else {
            echo ($this->debug) ? $server['name']." OK \n" : "";
            return true;
        }
    }

    /**
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description try to connect successfully on MySQL, if any one error in process even in PHP it throw a new Exception.
     * @access public
     */
    public function tryMysqlConnection($param)
    {
        $this->view = false;

        $name_server = $param[0];
        $id_server   = $param[1];

        $mysql_tested = $this->di['db']->sql($name_server);

        $db = $this->di['db']->sql(DB_DEFAULT);


        $variables = $mysql_tested->getVariables();
        $master    = $mysql_tested->isMaster();
        $slave     = $mysql_tested->isSlave();

        $sql       = "SELECT now() as date_time";
        $res       = $mysql_tested->sql_query($sql);
        $date_time = $mysql_tested->sql_fetch_object($res);  // can be empty ???????????



        try {
            $db->sql_query("START TRANSACTION;");


            $sql = "DELETE FROM mysql_replication_stats where id_mysql_server = '".$id_server."'";
            $db->sql_query($sql);

            $sql = "DELETE FROM mysql_database where id_mysql_server = '".$id_server."'";
            $db->sql_query($sql);

            $table = array();

            $table['mysql_replication_stats']['id']           = 1;
            $table['mysql_replication_stats']['is_available'] = 1;
            $table['mysql_replication_stats']['date']         = date("Y-m-d H:i:s");
            $table['mysql_replication_stats']['ping']         = 1;

            $table['mysql_replication_stats']['version']        = $mysql_tested->getServerType()." : ".$mysql_tested->getVersion();
            $table['mysql_replication_stats']['date']           = $date_time->date_time;
            $table['mysql_replication_stats']['is_master']      = ($master) ? 1 : 0;
            $table['mysql_replication_stats']['is_slave']       = ($slave) ? 1 : 0;
            $table['mysql_replication_stats']['uptime']         = ($mysql_tested->getStatus('Uptime'))
                    ? $mysql_tested->getStatus('Uptime') : '-1';
            $table['mysql_replication_stats']['time_zone']      = ($mysql_tested->getVariables('system_time_zone'))
                    ? $mysql_tested->getVariables('system_time_zone') : '-1';
            $table['mysql_replication_stats']['ping']           = 1;
            $table['mysql_replication_stats']['last_sql_error'] = '';
            $table['mysql_replication_stats']['binlog_format']  = ($mysql_tested->getVariables('binlog_format'))
                    ? $mysql_tested->getVariables('binlog_format') : 'N/A';


            $res = $db->sql_save($table);

            if (!$res) {
                throw new \Exception('PMACTRL-059 : insert in mysql_replication_stats !',
                60);
            }



            $db->sql_query("COMMIT;");
        } catch (\Exception $ex) {

            $db->sql_query("ROLLBACK");

            throw new \Exception('PMACTRL-058 : ROLLBACK made !', 60);
        }

        $db->sql_close();
        $mysql_tested->sql_close();

        if (count($err = error_get_last()) != 0) {
            throw new \Exception('PMACTRL-056 : '.$err['message'].' in '.$err['file'].' on line '.$err['line'],
            80);
        }
    }
}