<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;

class Agent extends Controller
{

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
     * @description get all MySQL and try to connect on each one
     * @access public
     */
    
    
    public function testAllMysql()
    {
        $this->view = false;
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "select * from mysql_server";
        $res = $db->sql_query($sql);


        $server_list = array();
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_list[] = $ob;
        }

        //to prevent any trouble with fork
        $db->sql_close();

        //$maxThreads = \Glial\System\Cpu::getCpuCores();
        $maxThreads = 50; // check MySQL server 50 by 50
        $openThreads = 0;
        $child_processes = array();

        foreach ($server_list as $server) {
            //echo str_repeat("#", count($child_processes)) . "\n";
            
            $pid = pcntl_fork();
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
        $ret = SetTimeLimit::run("Agent", "tryMysqlConnection", array($server['name'], $server['id']), 3);

        if (!SetTimeLimit::exitWithoutError($ret)) {
            /* in case of somthing wrong :
             * server don't answer
             * server didn't give msg 
             * wrong credentials
             * error in PHP script
             */

            $db = $this->di['db']->sql(DB_DEFAULT);
            $sql = "UPDATE mysql_replication_stats SET is_available = 0 where id_mysql_server = '" . $server['id'] . "'";
            $db->sql_query($sql);
            $db->sql_close();

            echo $server['name'] . " KO \n";
            return false;
        } else {
            echo $server['name'] . " OK \n";
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
        $id_server = $param[1];

        $mysql_tested = $this->di['db']->sql($name_server);

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "UPDATE mysql_replication_stats SET is_available = 1 where id_mysql_server = '" . $id_server . "'";
        $db->sql_query($sql);
        $db->sql_close();

        $mysql_tested->sql_close();

        if (count($err = error_get_last()) != 0) {
            throw new Exception('PMACTRL-056 : ' . $err['message'] . ' in ' . $err['file'] . ' on line ' . $err['line'], 80);
        }
    }

}
