<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;

class Agent extends Controller
{

    var $debug = false;
    var $url = "server/listing/";

    function start($param)
    {
        $id_daemon = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM daemon where id ='" . $id_daemon . "'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg = I18n::getTranslation(__("Impossible to find the daemon with the id : ") . "'" . $id_daemon . "'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: " . LINK . $this->url);
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($ob->pid === "0") {

            $php = explode(" ", shell_exec("whereis php"))[1];

            //todo add error flux in the log

            $log_file = TMP . "log/daemon_" . $ob->id . ".log";

            $cmd = $php . " " . GLIAL_INDEX . " Agent launch " . $id_daemon . " >> " . $log_file . " & echo $!";
            $pid = shell_exec($cmd);


            $sql = "UPDATE daemon SET pid ='" . $pid . "',log_file='" . $log_file . "' WHERE id = '" . $id_daemon . "'";
            $db->sql_query($sql);

            $msg = I18n::getTranslation(__("The cleaner id (" . $id_daemon . ") successfully started with") . " pid : " . $pid);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header("location: " . LINK . $this->url);
        } else {

            $msg = I18n::getTranslation(__("Impossible to launch the cleaner with the id : ") . "'" . $id_daemon . "'" . " (" . __("Already running !") . ")");
            $title = I18n::getTranslation(__("Error"));
            set_flash("caution", $title, $msg);
            header("location: " . LINK . $this->url);
        }
    }

    function stop($param)
    {
        $id_daemon = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM daemon where id ='" . $id_daemon . "'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg = I18n::getTranslation(__("Impossible to find the cleaner with the id : ") . "'" . $id_daemon . "'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: " . LINK . $this->url);
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($this->isRunning($ob->pid)) {
            $msg = I18n::getTranslation(__("The cleaner with pid : '" . $ob->pid . "' successfully stopped "));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);

            $cmd = "kill " . $ob->pid;
            shell_exec($cmd);
            shell_exec("echo '[" . date("Y-m-d H:i:s") . "] CLEANER STOPED !' >> " . $ob->log_file);
        } else {
            $msg = I18n::getTranslation(__("Impossible to find the cleaner with the pid : ") . "'" . $ob->pid . "'");
            $title = I18n::getTranslation(__("Cleaner was already stopped or in error"));
            set_flash("caution", $title, $msg);
        }

        sleep(1);

        if (!$this->isRunning($ob->pid)) {
            $sql = "UPDATE daemon SET pid ='0' WHERE id = '" . $id_daemon . "'";
            $db->sql_query($sql);
        } else {
            throw new Exception('PMACTRL-875 : Impossible to stop cleaner with pid : "' . $ob->pid . '"');
        }

        header("location: " . LINK . $this->url);
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
                    echo Color::getColoredString("DEBUG activated !", "yellow") . "\n";
                }
            }
        }

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
        $maxThreads = 3; // check MySQL server 50 by 50
        $openThreads = 0;
        $child_processes = array();

        if (empty($server_list)) {
            throw new Exception("List of server to test is empty", 20);
        }


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
        $ret = SetTimeLimit::run("Agent", "tryMysqlConnection", array($server['name'], $server['id']), 100);

        if (!SetTimeLimit::exitWithoutError($ret)) {
            /* in case of somthing wrong :
             * server don't answer
             * server didn't give msg 
             * wrong credentials
             * error in PHP script
             */
            $db = $this->di['db']->sql(DB_DEFAULT);
            $sql = "UPDATE mysql_server SET `error`='" . $db->sql_real_escape_string($ret['stdout']) . "', `date_refresh`='".date("Y-m-d H:i:s")."' where id = '" . $server['id'] . "'";
            $db->sql_query($sql);

            //echo $sql . "\n";

            $sql = "UPDATE mysql_replication_stats SET is_available = 0 where id_mysql_server = '" . $server['id'] . "'";
            $db->sql_query($sql);
            $db->sql_close();

            echo ($this->debug) ? $server['name'] . " KO :\n" : "";
            ($this->debug) ? print_r($ret) : '';
            return false;
        } else {
            //echo ($this->debug) ? $server['name']." OK \n" : "";
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

        $mysql_tested = @$this->di['db']->sql($name_server);

        $db = $this->di['db']->sql(DB_DEFAULT);


        $variables = $mysql_tested->getVariables();
        $status = $mysql_tested->getStatus();
        $master = $mysql_tested->isMaster();
        $slave = $mysql_tested->isSlave();

        $sql = "SELECT now() as date_time";
        $res2 = $mysql_tested->sql_query($sql);
        $date_time = $mysql_tested->sql_fetch_object($res2);  //can be empty ???????????

        $schema = array();
        if (version_compare($mysql_tested->getVersion(), '5.0', '>=')) {
            $sql = 'SELECT table_schema,
sum( data_length ) as "data",
sum( index_length ) as "index",
sum( data_free ) as "data_free" ,
count(1) as "tables",
sum(TABLE_ROWS) as "rows",
DEFAULT_CHARACTER_SET_NAME,
DEFAULT_COLLATION_NAME
FROM information_schema.TABLES a
INNER JOIN information_schema.SCHEMATA b ON a.table_schema = b.SCHEMA_NAME
GROUP BY table_schema ;';


            $schema = [];
            $res5 = $mysql_tested->sql_query($sql);
            while ($ob = $mysql_tested->sql_fetch_array($res5)) {
                $schema[$ob['table_schema']] = $ob;
            }
        }

        try {
            $db->sql_query("START TRANSACTION;");
            $sql = "SELECT id FROM mysql_replication_stats where id_mysql_server = '" . $id_server . "'";
            $res3 = $db->sql_query($sql);

            $table = array();
            $table['mysql_server']['id'] = $id_server;
            $table['mysql_server']['error'] = '';
            $table['mysql_server']['date_refresh'] = date("Y-m-d H:i:s");

            $res10 = $db->sql_save($table);

            if (!$res10) {
                throw new \Exception('PMACTRL-159 : impossible to remove error !', 60);
            }

            $table = array();

            if ($db->sql_num_rows($res3) == 1) {
                $ob = $db->sql_fetch_object($res3);
                $table['mysql_replication_stats']['id'] = $ob->id;
            }

            $table['mysql_replication_stats']['id_mysql_server'] = $id_server;
            $table['mysql_replication_stats']['is_available'] = 1;
            $table['mysql_replication_stats']['date'] = date("Y-m-d H:i:s");
            $table['mysql_replication_stats']['ping'] = 1;
            $table['mysql_replication_stats']['version'] = $mysql_tested->getServerType() . " : " . $mysql_tested->getVersion();
            $table['mysql_replication_stats']['date'] = $date_time->date_time;
            $table['mysql_replication_stats']['is_master'] = ($master) ? 1 : 0;
            $table['mysql_replication_stats']['is_slave'] = ($slave) ? 1 : 0;
            $table['mysql_replication_stats']['uptime'] = ($mysql_tested->getStatus('Uptime')) ? $mysql_tested->getStatus('Uptime') : '-1';
            $table['mysql_replication_stats']['time_zone'] = ($mysql_tested->getVariables('system_time_zone')) ? $mysql_tested->getVariables('system_time_zone') : '-1';
            $table['mysql_replication_stats']['ping'] = 1;
            $table['mysql_replication_stats']['last_sql_error'] = '';
            $table['mysql_replication_stats']['binlog_format'] = ($mysql_tested->getVariables('binlog_format')) ? $mysql_tested->getVariables('binlog_format') : 'N/A';

            $id_mysql_replication_stats = $db->sql_save($table);

            if (!$id_mysql_replication_stats) {
                throw new \Exception('PMACTRL-059 : insert in mysql_replication_stats !', 60);
            }



            //get all id_mysql_database
            $id_mysql_server = [];
            $sql = "SELECT * FROM mysql_database WHERE id_mysql_server = '" . $id_server . "'";
            $res6 = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_array($res6)) {
                $id_mysql_server[$ob['name']] = $ob;
            }

            foreach ($schema as $database) {
                $mysql_database = [];

                if (!empty($id_mysql_server[$database['table_schema']])) {

                    $mysql_database['mysql_database']['id'] = $id_mysql_server[$database['table_schema']]['id'];
                    //remove DB updated or add
                    unset($id_mysql_server[$database['table_schema']]);
                } else {
                    // push event new DB add
                }

                $mysql_database['mysql_database']['id_mysql_server'] = $id_server;
                $mysql_database['mysql_database']['name'] = $database['table_schema'];
                $mysql_database['mysql_database']['tables'] = $database['tables'];
                $mysql_database['mysql_database']['rows'] = $database['rows'];
                $mysql_database['mysql_database']['data_length'] = $database['data'];
                $mysql_database['mysql_database']['data_free'] = $database['data_free'];
                $mysql_database['mysql_database']['index_length'] = $database['index'];
                $mysql_database['mysql_database']['character_set_name'] = $database['DEFAULT_CHARACTER_SET_NAME'];
                $mysql_database['mysql_database']['collation_name'] = $database['DEFAULT_COLLATION_NAME'];
                $mysql_database['mysql_database']['binlog_do_db'] = 0;
                $mysql_database['mysql_database']['binlog_ignore_db'] = 0;

                if ($master) {
                    $mysql_database['mysql_database']['binlog_do_db'] = 1;
                    $mysql_database['mysql_database']['binlog_ignore_db'] = 1;
                }

                $res7 = $db->sql_save($mysql_database);

                if (!$res7) {
                    throw new \Exception('PMACTRL-060 : insert in mysql_database !', 60);
                }
            }

            //delete DB deleted
            foreach ($id_mysql_server as $key => $tab) {
                $sql = "DELETE FROM mysql_database WHERE id = '" . $tab['id'] . "'";
                $db->sql_query($sql);

                // push event DB deleted
            }

            if ($slave) {


                foreach ($slave as $thread_slave) {


                    $mysql_replication_thread = array();
                    if (empty($thread_slave['Thread_name'])) {
                        $thread_slave['Thread_name'] = '';
                    }
                    $sql = "SELECT id from mysql_replication_thread where id_mysql_replication_stats = '" . $id_mysql_replication_stats . "' 
			AND thread_name = '" . $thread_slave['Thread_name'] . "'";

                    $res34 = $db->sql_query($sql);

                    while ($ob = $db->sql_fetch_object($res34)) {
                        $mysql_replication_thread['mysql_replication_thread']['id'] = $ob->id;
                    }

                    $mysql_replication_thread['mysql_replication_thread']['id_mysql_replication_stats'] = $id_mysql_replication_stats;
                    $mysql_replication_thread['mysql_replication_thread']['relay_master_log_file'] = $thread_slave['Relay_Master_Log_File'];
                    $mysql_replication_thread['mysql_replication_thread']['exec_master_log_pos'] = $thread_slave['Exec_Master_Log_Pos'];
                    $mysql_replication_thread['mysql_replication_thread']['thread_io'] = ($thread_slave['Slave_IO_Running'] === 'Yes') ? 1 : 0;
                    $mysql_replication_thread['mysql_replication_thread']['thread_sql'] = ($thread_slave['Slave_SQL_Running'] === 'Yes') ? 1 : 0;
                    $mysql_replication_thread['mysql_replication_thread']['thread_name'] = (empty($thread_slave['Thread_name'])) ? '' : $thread_slave['Thread_name'];
                    $mysql_replication_thread['mysql_replication_thread']['time_behind'] = $thread_slave['Seconds_Behind_Master'];
                    $mysql_replication_thread['mysql_replication_thread']['master_host'] = $thread_slave['Master_Host'];
                    $mysql_replication_thread['mysql_replication_thread']['master_port'] = $thread_slave['Master_Port'];
                    $mysql_replication_thread['mysql_replication_thread']['last_sql_error'] = (empty($thread_slave['Last_sql_Error'])) ? $thread_slave['Last_Error'] : $thread_slave['Last_sql_Error'];
                    $mysql_replication_thread['mysql_replication_thread']['last_sql_errno'] = (empty($thread_slave['Last_sql_Errno'])) ? $thread_slave['Last_Errno'] : $thread_slave['Last_sql_Errno'];
                    $mysql_replication_thread['mysql_replication_thread']['last_io_error'] = (empty($thread_slave['Last_Io_Error'])) ? $thread_slave['Last_Error'] : $thread_slave['Last_io_Error'];
                    $mysql_replication_thread['mysql_replication_thread']['last_io_errno'] = (empty($thread_slave['Last_io_Errno'])) ? $thread_slave['Last_Errno'] : $thread_slave['Last_io_Errno'];

                    $res8 = $db->sql_save($mysql_replication_thread);

                    if (!$res8) {
                        debug($db->sql_error());
                        throw new \Exception('PMACTRL-060 : insert in mysql_database !', 60);
                    }
                }
            }

            $db->sql_query("COMMIT;");
        } catch (\Exception $ex) {

            $db->sql_query("ROLLBACK");

            throw new \Exception('PMACTRL-058 : ROLLBACK made !', 60);
        }


        if (version_compare($mysql_tested->getVersion(), '5.0', '>=')) {
            $this->saveStatus($status, $id_server);
        }
        $db->sql_close();
        $mysql_tested->sql_close();

        if (count($err = error_get_last()) != 0) {
            throw new \Exception('PMACTRL-056 : ' . $err['message'] . ' in ' . $err['file'] . ' on line ' . $err['line'], 80);
        }
    }

    public function updateServerList()
    {
        $this->view = false;
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM `mysql_server`";
        $servers_mysql = $db->sql_fetch_yield($sql);
        $all_server = array();
        foreach ($servers_mysql as $mysql) {
            $all_server[$mysql['name']] = $mysql;
        }
        Crypt::$key = CRYPT_KEY;

        $all = array();
        foreach ($this->di['db']->getAll() as $server) {

            $all[] = $server;
            $info_server = $this->di['db']->getParam($server);
            $data = array();

            if (!empty($all_server[$server])) {
                $data['mysql_server']['id'] = $all_server[$server]['id'];

                unset($all_server[$server]);
            } else {
                echo "Add : " . $server . " to monitoring\n";
            }

            $data['mysql_server']['name'] = $server;
            $data['mysql_server']['ip'] = $info_server['hostname'];
            $data['mysql_server']['login'] = $info_server['user'];
            $data['mysql_server']['passwd'] = Crypt::encrypt($info_server['password']);
            $data['mysql_server']['port'] = empty($info_server['port']) ? 3306 : $info_server['port'];
            $data['mysql_server']['date_refresh'] = date('Y-m-d H:i:s');

            if (!empty($info_server['ssh_login'])) {
                $data['mysql_server']['ssh_login'] = Crypt::encrypt($info_server['ssh_login']);
            }
            if (!empty($info_server['ssh_password'])) {
                $data['mysql_server']['ssh_password'] = Crypt::encrypt($info_server['ssh_password']);
            }

            if (!$db->sql_save($data)) {
                debug($data);
                debug($db->sql_error());
                exit;
            } else {
                //echo $data['mysql_server']['name'] . PHP_EOL;
            }
        }

        foreach ($all_server as $to_delete) {
            $sql = "DELETE FROM `mysql_server` WHERE id=" . $to_delete['id'] . "";
            $db->sql_query($sql);

            echo "[Warning] Removed : " . $to_delete['name'] . " from monitoring\n";
        }
    }

    private function addServerToConfig($name, $ip = "")
    {
        
    }

    public function index()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * FROM `daemon` order by id";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['daemon'][] = $ob;
        }

        $this->set('data', $data);
    }

    private function isRunning($pid)
    {
        if (empty($pid)) {
            return false;
        }
        $cmd = "ps -p " . $pid;
        $alive = shell_exec($cmd);

        if (strpos($alive, $pid) !== false) {
            return true;
        }
        return false;
    }

    /*
     * This method can provide duplicate KEY, if daemon is started for first time with more than 2 servers, next run all will be fine
     *
     *
     * Type :
     *  - 1 => int
     *  - 2 => double
     *  - 3 => text
     */

    public function saveStatus($all_status, $id_mysql_server)
    {

        $default = $this->di['db']->sql(DB_DEFAULT);
        $all_name = array_keys($all_status);

        $sql = "SELECT * FROM mysql_status_name";

        $index = [];
        $data = [];

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {
            $index[] = $ob->name;
            $data[$ob->name]['id'] = $ob->id;
            $data[$ob->name]['type'] = $ob->type;
        }


        foreach ($all_status as $name => $status) {
            if (!in_array($name, $index)) {
                echo "add " . $name . "\n";


                $mysql_status_name['mysql_status_name']['name'] = $name;
                $mysql_status_name['mysql_status_name']['type'] = self::getTypeOfData($status);
                //$mysql_status_name['mysql_status_name']['value'] = $status;

                $id = $default->sql_save($mysql_status_name);
                if (!$id) {
                    debug($mysql_status_name);
                    debug($default->sql_error());

                    throw new Exception('PMACTRL : Impossible to save');
                }
            }
        }
        $this->saveValue($data, $all_status, $id_mysql_server);
    }

    public function saveValue($data, $all_status, $id_mysql_server)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $tables = array('mysql_status_value_int', 'mysql_status_value_double', 'mysql_status_value_text');

        $i = 0;
        foreach ($tables as $table) {
            $sql[$i] = "INSERT INTO `" . $table . "` (`id_mysql_server`, `id_mysql_status_name`,`date`, `value`) VALUES ";
            $i++;
        }

        $feed = array();
        $date = date('Y-m-d H:i:s');

        foreach ($all_status as $name => $status) {
            $feed[$data[$name]['type']][] = "(" . $id_mysql_server . "," . $data[$name]['id'] . ",'" . $date . "','" . $status . "')";
        }

        $i = 0;
        foreach ($feed as $tmp) {
            $req = $sql[$i] . implode(',', $tmp) . ";";
            //echo $req."\n";
            $db->sql_query($req);
            $i++;
        }
    }

    static private function isFloat($value)
    {
        // test before => must be numeric first
        if (strstr($value, ".")) {
            return true;
        }
        return ((int) $value != $value);
    }

    /* Type :
     *  - 1 => int
     *  - 2 => double
     *  - 3 => text
     */

    static private function getTypeOfData($value)
    {
        $val = 0;

        $is_numeric = is_numeric($value);

        if ($is_numeric === true) {
            //debug($is_numeric);
            $val = 1;

            $is_float = self::isFloat($value);

            if ($is_float) {
                $val = 2;
            }
        }

        return $val;
    }

    /*
     *
     * Move to test for PHPUnit
     */

    public function testData()
    {
        $nogood = 0;

        $tests = [1452, 0.125, 254.25, "0.0000", "0.254", "254.25", "15", "1e25", "ggg.ggg", "fghg"];
        $result = [1, 2, 2, 2, 2, 2, 1, 2, 0, 0];

        if (count($tests) !== count($result)) {
            throw new \Exception("PMACTRL : array not the same size");
        }

        $i = 0;
        foreach ($tests as $test) {
            $val = self::getTypeOfData($test);

            if ($val != $result[$i]) {
                echo "#" . $i . " -- " . $test . ":" . $val . ":" . $result[$i] . " no good \n";
                $nogood++;
            } else {
                echo "#" . $i . " -- " . $test . ":" . $val . ":" . $result[$i] . "GOOOOOOOOOOD \n";
            }
            $i++;
        }

        if (!empty($nogood)) {
            echo "##################### NO GOOD ################";
        }
    }

}
