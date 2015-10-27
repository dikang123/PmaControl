<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;


/*
declare(ticks = 1);

// gestionnaire de signaux système
function sig_handler($signo)
{

    switch ($signo) {
        case SIGTERM:
            // gestion de l'extinction
            echo "Reçu le signe SIGTERM...\n";
            exit;
            break;
        case SIGHUP:
            echo "Reçu le signe SIGHUP...\n";
            // gestion du redémarrage
            break;
        case SIGUSR1:
            echo "Reçu le signe SIGUSR1...\n";
            break;

        case SIGALRM:
            echo "Reçu le signe SIGALRM...\n";
            break;
        default:
        // gestion des autres signaux
    }
}

pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP, "sig_handler");
pcntl_signal(SIGUSR1, "sig_handler");

//pcntl_signal(SIGALRM, "sig_handler");
*/

class Daemon extends Controller
{

    function start()
    {
        $this->layout_name = 'default';



        //$this->javascript = array("");
    }

    
    /*
    function my_background_exec($function_name, $params, $str_requires, $timeout = 600)
    {
        $map = array('"' => '\"', '$' => '\$', '`' => '\`', '\\' => '\\\\', '!' => '\!');
        $str_requires = strtr($str_requires, $map);
        $path_run = dirname($_SERVER['SCRIPT_FILENAME']);
        $my_target_exec = "/usr/bin/php -r \"chdir('{$path_run}');{$str_requires} \\\$params=json_decode(file_get_contents('php://stdin'),true);call_user_func_array('{$function_name}', \\\$params);\"";
        $my_target_exec = strtr(strtr($my_target_exec, $map), $map);
        $my_background_exec = "(/usr/bin/php -r \"chdir('{$path_run}');{$str_requires} my_timeout_exec(\\\"{$my_target_exec}\\\", file_get_contents('php://stdin'), {$timeout});\" <&3 &) 3<&0"; //php by default use "sh", and "sh" don't support "<&0"

        print_r($my_background_exec);
        echo "\n\n";
        print_r($params);

        $this->my_timeout_exec($my_background_exec, json_encode($params), $timeout);
    }

    function timeout_exec($cmd, $stdin = '', $timeout = 10)
    {
        $start = time();
        $stdout = '';
        $stderr = '';
        //file_put_contents('debug.txt', time().':cmd:'.$cmd."\n", FILE_APPEND);
        //file_put_contents('debug.txt', time().':stdin:'.$stdin."\n", FILE_APPEND);

        $process = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        if (!is_resource($process)) {
            return array('return' => '1', 'stdout' => $stdout, 'stderr' => $stderr);
        }
        $status = proc_get_status($process);
        
        posix_setpgid($status['pid'], $status['pid']);    
        //seperate pgid(process group id) from parent's pgid

        stream_set_blocking($pipes[0], 0);
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);

        while (1) {
            $stdout.=stream_get_contents($pipes[1]);
            $stderr.=stream_get_contents($pipes[2]);

            if (time() - $start > $timeout) {
                //proc_terminate($process, 9);    
                //only terminate subprocess, won't terminate sub-subprocess
                posix_kill(-$status['pid'], 9);
                ////sends SIGKILL to all processes inside group(negative means GPID, all subprocesses share the top process group, except nested my_timeout_exec)
                //file_put_contents('debug.txt', time().":kill group {$status['pid']}\n", FILE_APPEND);
                return array('return' => '1', 'stdout' => $stdout, 'stderr' => $stderr);
            }

            $status = proc_get_status($process);
            //file_put_contents('debug.txt', time().':status:'.var_export($status, true)."\n";
            if (!$status['running']) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return $status['exitcode'];
            }

            usleep(100000);
        }
    }

    function all()
    {

        $this->view = false;
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM  `mysql_server`";

        $servers = $db->sql_fetch_yield($sql);


        $func = array($this, "test");

        foreach ($servers as $server) {

            $this->my_background_exec("test", array($server['name']), '', 10);
        }
    }

    function test($link)
    {

        $db = $this->di['db']->sql($link);

        echo $link . " : ";
        if ($db) {
            echo "ONLINE\n";
        } else {
            echo "OFFLINE\n";
        }
    }

    function test2()
    {
        $this->view = false;
        $pid = pcntl_fork();

        if ($pid) {
            //long time process 
            $pid_pere = posix_getpid();

            pcntl_alarm(5);
            sleep(10);
        } else {
            $pid_fils = posix_getpid();

            sleep(10);
            //time-limit checker 
            //          posix_kill(posix_getpid(), SIGKILL);
        }

        //posix_kill(posix_getpid(), SIGTERM);
    }

    public function test3()
    {
        $this->view = false;
        $gg = $this->my_background_exec('\Glial\Synapse\FactoryController::addNode', array("Daemon", "test4"), '', 10);

        print_r($gg);
    }

    public function test4()
    {
        echo "ca marche";
        exit(125);
        sleep(10);
        $this->view = false;
        shell_exec("echo 'gg' > /tmp/camarche.txt");
        echo "ca marche";
    }

    public function test5()
    {
        $this->view = false;
        $gg = $this->timeout_exec("php /data/www/pmacontrol/application/webroot/index.php Daemon test4", '', 2);

        print_r($gg);
    }
     * 
     */

}
