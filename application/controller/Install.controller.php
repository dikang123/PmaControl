<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\Glial;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sql\FactorySql;

class Install extends Controller
{

    function index2()
    {
        $this->view = false;

//for display like putty in utf8
        shell_exec('echo -ne \'\\\\e%G\\\\e[?47h\\\\e%G\\\\e[?47l\\\'');

        echo PHP_EOL.Glial::header().PHP_EOL;

        echo $this->out("Installation in progress ...", "OK");


        $res = shell_exec("git --version");
        if (!preg_match("/^git version [1]\.[0-9]+\.[0-9]/i", $res, $gg)) {
            $errors['git'] = true;
        }

        if (version_compare(PHP_VERSION, '5.5.10', '<')) {
            $errors['php'] = PHP_VERSION;
        }

        if (!extension_loaded('gd')) {
            $errors['gd'] = true;
        }

        if (!extension_loaded('mysqli')) {
            $errors['mysqli'] = true;
        }

        if (!extension_loaded('curl')) {
            $errors['curl'] = true;
        }

        if (!extension_loaded('ssh2')) {
            $errors['ssh2'] = true;
        }


        if (!empty($errors)) {
            echo $this->out("Check dependencies ...", "KO");

            echo Color::getColoredString("Some settings on your machine make Glial unable to work properly.",
                "red").PHP_EOL;
            echo Color::getColoredString("Make sure that you fix the issues listed below and run this script again:",
                "red").PHP_EOL;




            foreach ($errors as $error => $current) {

                $displayIniMessage = false;

                switch ($error) {
                    case 'gd':
                        $text              = PHP_EOL."The gd extension is missing.".PHP_EOL;
                        $text .= "Install it (\# apt-get install php5-gd) or recompile php without --disable-gd";
                        $displayIniMessage = true;
                        break;

                    case 'git':
                        $text              = PHP_EOL."The git software is missing.".PHP_EOL;
                        $text .= "Install it (\# apt-get install git)";
                        $displayIniMessage = true;
                        break;

                    case 'mysqli':
                        $text              = PHP_EOL."The mysqli extension is missing.".PHP_EOL;
                        $text .= "Install it or recompile php without --disable-mysqli";
                        $displayIniMessage = true;
                        break;


                    case 'curl':
                        $text              = PHP_EOL."The curl extension is missing.".PHP_EOL;
                        $text .= "Install it or recompile php without --disable-curl";
                        $displayIniMessage = true;
                        break;

                    case 'phar':
                        $text              = PHP_EOL."The phar extension is missing.".PHP_EOL;
                        $text .= "Install it or recompile php without --disable-phar";
                        $displayIniMessage = true;
                        break;

                    case 'unicode':
                        $text              = PHP_EOL."The detect_unicode setting must be disabled.".PHP_EOL;
                        $text .= "Add the following to the end of your `php.ini`:".PHP_EOL;
                        $text .= "    detect_unicode = Off";
                        $displayIniMessage = true;
                        break;

                    case 'suhosin':
                        $text              = PHP_EOL."The suhosin.executor.include.whitelist setting is incorrect.".PHP_EOL;
                        $text .= "Add the following to the end of your `php.ini` or suhosin.ini (Example path [for Debian]: /etc/php5/cli/conf.d/suhosin.ini):".PHP_EOL;
                        $text .= "    suhosin.executor.include.whitelist = phar ".$current;
                        $displayIniMessage = true;
                        break;

                    case 'php':
                        $text              = PHP_EOL."Your PHP ({$current}) is too old, you must upgrade to PHP 5.5.10 or higher.";
                        $displayIniMessage = true;
                        break;

                    case 'allow_url_fopen':
                        $text              = PHP_EOL."The allow_url_fopen setting is incorrect.".PHP_EOL;
                        $text .= "Add the following to the end of your `php.ini`:".PHP_EOL;
                        $text .= "    allow_url_fopen = On";
                        $displayIniMessage = true;
                        break;

                    case 'ioncube':
                        $text              = PHP_EOL."Your ionCube Loader extension ($current) is incompatible with Phar files.".PHP_EOL;
                        $text .= "Upgrade to ionCube 4.0.9 or higher or remove this line (path may be different) from your `php.ini` to disable it:".PHP_EOL;
                        $text .= "    zend_extension = /usr/lib/php5/20090626+lfs/ioncube_loader_lin_5.3.so";
                        $displayIniMessage = true;
                        break;
                }
                if ($displayIniMessage) {
//$text .= $iniMessage;
                    echo Color::getColoredString($text, "yellow").PHP_EOL;
                }
            }

            $this->onError();
        } else {
            echo $this->out("Check dependencies ", "OK");
        }


//making tree directory

        $fct = function($msg) {
            $dirs = array("data", "data/img", "documentation", "tmp/crop", "tmp/documentation",
                "application/webroot/js",
                "application/webroot/css", "application/webroot/file", "application/webroot/video",
                "application/webroot/image");

            $error = array();
            foreach ($dirs as $dir) {

                $dir = $_SERVER['PWD']."/".$dir;

                if (!file_exists($dir)) {
                    if (!mkdir($dir)) {
//echo $this->out("Impossible to create this directory : " . $key . " ", "KO");
                    }
                }
            }

            return array(true, $msg);
        };
        $this->anonymous($fct, "Making tree directory");




        $fct = function ($msg) {


            $name   = "jquery-latest.min.js";
            $jQuery = $_SERVER['PWD']."/application/webroot/js/".$name;

            $old_version = "";
            if (file_exists($jQuery)) {
                $data = file_get_contents($jQuery);
                preg_match("/v[\d]+\.[\d]+\.[\d]+/", $data, $version);

                $old_version = $version[0]." => ";
                $this->cmd("rm ".$jQuery, "Delete old jQuery");
            }

            $this->cmd("cd ".$_SERVER['PWD']."/application/webroot/js && wget -q http://code.jquery.com/".$name,
                "Download lastest jQuery");

            if (file_exists($jQuery)) {
                $data = file_get_contents($jQuery);

                preg_match("/v[\d]+\.[\d]+\.[\d]+/", $data, $version);

                $msg = sprintf($msg,
                    $old_version.Color::getColoredString($version[0], "green"));

                return array(true, $msg);
            } else {
                $msg = sprintf($msg, "NOT INSTALLED");
                return array(false, $msg);
            }
        };


        $this->anonymous($fct, "jQuery installed (%s)");


        $this->cmd("chown www-data:www-data -R *",
            "Setting right to www-data:www-data");


        $this->cmd("php glial administration admin_index_unique",
            "Generating DDL cash for index");
        $this->cmd("php glial administration admin_table",
            "Generating DDL cash for databases");
        $this->cmd("php glial administration generate_model",
            "Making model with reverse engineering of databases");


        /*
          shell_exec("find " . $_SERVER['PWD'] . " -type f -exec chmod 740 {} \;;");
          echo $this->out("Setting chmod 440 to all files", "OK");

          shell_exec("find " . $_SERVER['PWD'] . " -type d -exec chmod 750 {} \;;");
          echo $this->out("Setting chmod 550 to all files", "OK");


          shell_exec("find " . $_SERVER['PWD'] . "/tmp -type f -exec chmod 770 {} \;;");
          echo $this->out("Setting chmod 660 to all files of /tmp", "OK");

          shell_exec("find " . $_SERVER['PWD'] . "/tmp -type d -exec chmod 770 {} \;;");
          echo $this->out("Setting chmod 660 to all directory of /tmp", "OK");

          shell_exec("chmod +x glial");
          echo $this->out("Setting chmod +x to executable 'glial'", "OK");
         */


        $fct = function ($msg) {
            $file = $_SERVER['PWD']."/glial";
            $data = file_get_contents($file);

            $new_data = str_replace("php application",
                "php ".$_SERVER['PWD']."/application", $data);
            if (!file_put_contents($file, $new_data)) {
                return array(false, $msg);
            }
            return array(true, $msg);
        };

        $this->anonymous($fct,
            "Replace relative path by full path in Glial exec");

        $fct = function ($msg) {

            $file        = $_SERVER['PWD']."/glial";
            $path_to_php = exec("which php", $res, $code);

            if ($code !== 0) {
                return array(false, $msg." $code:$path_to_php: can't find php");
            }

            $data    = file($file);
            $data[0] = "#!".$path_to_php.PHP_EOL;
            file_put_contents($file, implode("", $data));

            return array(true, $msg);
        };

        $this->anonymous($fct, "get full path of php");

        $this->cmd("chmod +x glial", "Setting chmod +x to executable 'glial'");
        $this->cmd("cp -a glial /usr/local/bin/glial",
            "Copy glial to /usr/local/bin/");

        echo PHP_EOL;
    }

    public function out($msg, $type)
    {
        switch ($type) {
            case 'OK':
            case true: $status = Color::getColoredString("OK", "green");
                break;

            case 'KO':
            case false:
                $status = Color::getColoredString("KO", "red");
                $msg    = Color::getColoredString($msg, "red");
                $err    = true;
                break;
            case 'NA': $status = Color::getColoredString("!!", "blue");
                break;
        }


        $msg .= " ";
        $ret = $msg.str_repeat(".", 76 - strlen(Color::strip($msg)))." [ ".$status." ]".PHP_EOL;


        if (!empty($err)) {
            echo $ret;
            $this->onError();
        }

        return $ret;
    }

    public function onError()
    {

        echo PHP_EOL."To understand what happen : ".Color::getColoredString("glial/tmp/log/error_php.log",
            "cyan").PHP_EOL;
        echo "To resume the setup : ".Color::getColoredString("php composer.phar update",
            "cyan").PHP_EOL;
        exit(10);
    }

    public function cmd($cmd, $msg)
    {
        $code_retour = 0;

        ob_start();
        passthru($cmd, $code_retour);

        if ($code_retour !== 0) {
            $fine = false;
            ob_end_flush();
        } else {
            $fine = true;
            ob_end_clean();
        }

        echo $this->out($msg, $fine." ");
    }

    public function anonymous($function, $msg)
    {
        list($fine, $message) = $function($msg);

        echo $this->out($message, $fine);
    }

    public function index()
    {
        $this->view = false;

echo ROOT;
        $this->cadre("Select MySQL server for PmaControl");
        $server = $this->testMysqlServer();

        $this->importData($server);
        $this->updateConfig($server);
        $this->updateCache();
    }

    private function prompt($test)
    {
        echo $test;
        $handle = fopen("php://stdin", "r");
        $line   = fgets($handle);

        return $line;
    }

    private function testMysqlServer()
    {

        $good = false;
        do {
            echo "Name of connection into configuration/db.config.ini.php : [pmacontrol]\n";
            $hostname = trim($this->prompt('Hostname/IP of MySQL [default : 127.0.0.1] : '));
            $port     = trim($this->prompt('Port of MySQL        [default : 3306]      : '));

            if (empty($port)) {
                $port = 3306;
            }
            if (empty($hostname)) {
                $hostname = "127.0.0.1";
            }

            $fp = @fsockopen($hostname, $port, $errno, $errstr, 30);
            if (!$fp) {
                echo Color::getColoredString("$errstr ($errno)", "grey", "red")."\n";
                echo "MySQL server : ".$hostname.":".$port." -> ".Color::getColoredString("KO",
                    "grey", "red")."\n";
            } else {
                echo "MySQL server : ".$hostname.":".$port." -> ".Color::getColoredString("OK",
                    "black", "green")."\n";
                fclose($fp);
                $good = true;
            }
            echo str_repeat("-", 80)."\n";
        } while ($good === false);



        //login & password mysql
        $good = false;
        do {
            echo "MySQL account on (".$hostname.":".$port.")\n";
            $user     = trim($this->prompt('User     [default : root]    : '));
            $password = trim($this->prompt('Password [default : (empty)] : '));

            if (empty($user)) {
                $user = "root";
            }

            $link = @mysqli_connect($hostname.":".$port, $user, $password);

            if ($link) {
                $good = true;
                echo Color::getColoredString('Login/password -> OK', "black",
                    "green")."\n";
            } else {
                echo Color::getColoredString('Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error(),
                    "grey", "red")."\n";
                echo "crednetial (".$user." // ".$password.")\n";
            }

            echo str_repeat("-", 80)."\n";
        } while ($good === false);

        // check database
        wrong_password:
        $good = false;
        do {
            echo "Name of database who will be used by PmaConrol\n";
            $database = trim($this->prompt('database [default : pmacontrol] : '));

            if (empty($database)) {
                $database = "pmacontrol";
            }

            $sql    = "SELECT count(1) as cpt FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".mysqli_real_escape_string($link,
                    $database)."'";
            $result = mysqli_query($link, $sql);

            $ob = mysqli_fetch_object($result);

            if ($ob->cpt == "1") {
                echo Color::getColoredString('Database -> KO (this database already exist)',
                    "grey", "red")."\n";
            } else {
                $good = true;
                echo Color::getColoredString('Database -> OK', "black", "green")."\n";
            }

            echo str_repeat("-", 80)."\n";
        } while ($good === false);

        //create database

        $sql = "CREATE DATABASE ".mysqli_real_escape_string($link, $database)."";
        $res = mysqli_query($link, $sql);

        if ($res) {
            echo Color::getColoredString('The database "'.mysqli_real_escape_string($link,
                    $database).'" has been created', "black", "green")."\n";
        } else {
            echo Color::getColoredString('The database "'.mysqli_real_escape_string($link,
                    $database).'" couldn\'t be created', "black", "red")."\n";
            goto wrong_password;
        }
        echo str_repeat("-", 80)."\n";

        $mysql['hostname'] = $hostname;
        $mysql['port']     = $port;
        $mysql['user']     = $user;
        $mysql['password'] = $password;
        $mysql['database'] = $database;


        print_r($mysql);

        return $mysql;
    }

    private function cadre($text, $elem = '#')
    {
        echo str_repeat($elem, 80)."\n";
        echo $elem.str_repeat(' ', ceil((80 - strlen($text) - 2) / 2)).$text.str_repeat(' ',
            ceil((80 - strlen($text) - 2) / 2)).$elem."\n";
        echo str_repeat($elem, 80)."\n";
    }

    private function importData($server)
    {
        $path = ROOT."/sql/*.sql";

        foreach (glob($path) as $filename) {
            echo "$filename size ".filesize($filename)."\n";

            $cmd = "mysql -h ".$server["hostname"]." -u ".$server['user']." -P ".$server['port']." -p".$server['password']." ".$server['database']." < ".$filename."";


            
            //echo $cmd."\n";
            //shell_exec($cmd);
        }
    }

    private function updateConfig($server)
    {
        //update DB config
        
        $config = "
;[name_of_connection] => will be acceded in framework with \$this->di['db']->sql('name_of_connection')->method()
;driver => list of SGBD avaible {mysql, pgsql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas

[pmacontrol]
driver=mysql
hostname=".$server["hostname"]."
user=".$server['user']."
password='".$server['password']."'
database=".$server['database']."";

        $fp = fopen(CONFIG."/db.config.ini.php", 'w');
        fwrite($fp, $config);
        fclose($fp);


    }

    private function updateCache()
    {
        $this->cmd("php glial administration admin_index_unique",
            "Generating DDL cash for index");
        $this->cmd("php glial administration admin_table",
            "Generating DDL cash for databases");
        $this->cmd("php glial administration generate_model",
            "Making model with reverse engineering of databases");
    }
}