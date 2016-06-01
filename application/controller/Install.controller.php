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
            case 'true':
                $status = Color::getColoredString("OK", "green");
                break;
            case 'KO':
            case 'false':
                $status = Color::getColoredString("KO", "red");
                break;
            case 'NA': $status = Color::getColoredString("!!", "blue");
                break;
        }

        $ret = $msg.str_repeat(".", 73 - strlen(Color::strip($msg)))." [ ".$status." ]".PHP_EOL;

        /*
          if (!empty($err)) {
          echo $ret;
          $this->onError();
          }
         */
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
            $fine = 'KO';
            ob_end_flush();
        } else {
            $fine = 'OK';
            ob_end_clean();
        }

        $this->displayResult($msg, $fine);
    }

    function displayResult($msg, $fine)
    {
        echo $this->out(Color::getColoredString("[".date("Y-m-d H:i:s")."] ",
                "purple").$msg, $fine);
    }

    public function anonymous($function, $msg)
    {
        list($fine, $message) = $function($msg);

        echo $this->out($message, $fine);
    }

    public function index()
    {
        $this->view = false;

        //$title = Hoa\Console\Window::getTitle();
        //Hoa\Console\Window::setTitle('Install of PmaControl 0.8');


        echo "\n";
        echo SITE_LOGO;
        echo Color::getColoredString(SITE_NAME, "green")." version ".Color::getColoredString(SITE_VERSION,
            "yellow")." (".SITE_LAST_UPDATE.")\n";
        echo "Powered by Glial (https://github.com/Esysteme/glial)\n";

        $this->cadre("Select MySQL server for PmaControl");
        $server = $this->testMysqlServer();

        sleep(1);
        $this->importData($server);
        $this->updateConfig($server);
        $this->updateCache();

        $this->cmd("echo 1", "Testing system & configuration");



        echo Color::getColoredString("\nPmaControl 0.8-beta has been successfully installed !\n",
            "green");
        //Hoa\Console\Window::setTitle($title);
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
                echo str_repeat("-", 80)."\n";
            } else {
                $this->cmd("echo 1",
                    "MySQL server : ".$hostname.":".$port." available");

                fclose($fp);
                $good = true;
            }
        } while ($good === false);



        //login & password mysql
        $good = false;



        do {


            echo "MySQL account on (".$hostname.":".$port.")\n";

            $rl   = new Hoa\Console\Readline\Readline ();
            $user = $rl->readLine('User     [default : root]    : ');

            $rl       = new Hoa\Console\Readline\Password();
            $password = $rl->readLine('Password [default : (empty)] : ');

            if (empty($user)) {
                $user = "root";
            }

            $link = mysqli_connect($hostname.":".$port, $user, trim($password));

            if ($link) {
                $good = true;
                $this->cmd("echo 1", "Login/password for MySQL's server");
            } else {
                echo Color::getColoredString('Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error(),
                    "grey", "red")."\n";
                //echo "credential (".$user." // ".$password.")\n";
                echo str_repeat("-", 80)."\n";
            }

            sleep(1);
        } while ($good === false);

        // check database
        wrong_db:
        $good = false;
        do {
            echo "Name of database who will be used by PmaConrol\n";


            $rl       = new Hoa\Console\Readline\Readline ();
            $database = $rl->readLine('Database     [default : pmacontrol]    : ');

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
                echo str_repeat("-", 80)."\n";
            } else {
                $good = true;
                $this->cmd("echo 1", "Database's name");
            }
        } while ($good === false);

        //create database

        $sql = "CREATE DATABASE ".mysqli_real_escape_string($link, $database)."";
        $res = mysqli_query($link, $sql);

        if ($res) {


            $this->cmd("echo 1",
                'The database "'.mysqli_real_escape_string($link, $database).'" has been created');
        } else {
            echo Color::getColoredString('The database "'.mysqli_real_escape_string($link,
                    $database).'" couldn\'t be created', "black", "red")."\n";
            goto wrong_db;
            echo str_repeat("-", 80)."\n";
        }

        $mysql['hostname'] = $hostname;
        $mysql['port']     = $port;
        $mysql['user']     = $user;
        $mysql['password'] = $password;
        $mysql['database'] = $database;
        return $mysql;
    }

    private function cadre($text, $elem = '#')
    {
        echo str_repeat($elem, 80)."\n";
        echo $elem.str_repeat(' ', ceil((80 - strlen($text) - 2) / 2)).$text.str_repeat(' ',
            floor((80 - strlen($text) - 2) / 2)).$elem."\n";
        echo str_repeat($elem, 80)."\n";
    }

    private function importData($server)
    {
        $path = ROOT."/sql/*.sql";

        foreach (glob($path) as $filename) {
            //echo "$filename size ".filesize($filename)."\n";
            $cmd = "mysql -h ".$server["hostname"]." -u ".$server['user']." -P ".$server['port']." -p".$server['password']." ".$server['database']." < ".$filename."";
            $this->cmd($cmd, "Loading ".pathinfo($filename)['basename']);
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

        $this->cmd("echo 1", "Generate config file for DB");
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

    public function createAdmin()
    {
        $this->view = false;

        /*
         * email
         * first name
         * last name
         * country
         * city
         * password
         * password repeat
         */

        createUser:
        $this->cadre("create administrator user");

        $email_is_valid = false;
        do {
            $rl    = new Hoa\Console\Readline\Readline ();
            $email = $rl->readLine('Your email [will be used as login] : ');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->displayResult("This email considered as valid !", "KO");
            } else {
                $this->displayResult("This email considered as valid !", "OK");
                $domain = explode('@', $email)[1];
                if (checkdnsrr($domain, 'MX')) {

                    $this->displayResult("This MX records exists !", "OK");
                    $email_is_valid = true;
                } else {
                    $this->displayResult("This MX records exists !", "KO");
                }
            }
        } while ($email_is_valid === false);


        //first name
        $firstname = $rl->readLine('Your firstname : ');

        //last name
        $lastname = $rl->readLine('Your lastname : ');


        //country
        $sql = "SELECT libelle FROM geolocalisation_country where libelle != '' ORDER BY libelle";
        $DB  = $this->di['db']->sql(DB_DEFAULT);

        $res     = $DB->sql_query($sql);
        $country = [];
        while ($ob      = $DB->sql_fetch_object($res)) {
            $country[] = $ob->libelle;
        }

        do {
            $rl       = new Hoa\Console\Readline\Readline ();
            $rl->setAutocompleter(new Hoa\Console\Readline\Autocompleter\Word($country));
            $country2 = $rl->readLine('Your country [First letter in upper case, then tab for help] : ');

            $sql = "select id from geolocalisation_country where libelle = '".$DB->sql_real_escape_string($country2)."'";
            $res = $DB->sql_query($sql);


            if ($DB->sql_num_rows($res) == 1) {
                $ob         = $DB->sql_fetch_object($res);
                $id_country = $ob->id;
                $this->displayResult("Country found in database !", "OK");
            } else {
                $this->displayResult("Country found in database !", "KO");
            }
        } while ($DB->sql_num_rows($res) != 1);


        //city
        $sql = "SELECT libelle FROM geolocalisation_city where id_geolocalisation_country = '".$id_country."' ORDER BY libelle";
        $DB  = $this->di['db']->sql(DB_DEFAULT);

        $res  = $DB->sql_query($sql);
        $city = [];
        while ($ob   = $DB->sql_fetch_object($res)) {
            $city[] = $ob->libelle;
        }

        do {
            $rl    = new Hoa\Console\Readline\Readline();
            $rl->setAutocompleter(new Hoa\Console\Readline\Autocompleter\Word($city));
            $city2 = $rl->readLine('Your city [First letter in upper case, then tab for help] : ');

            $sql = "select id from geolocalisation_city where libelle = '".$DB->sql_real_escape_string($city2)."'";
            $res = $DB->sql_query($sql);

            if ($DB->sql_num_rows($res) == 1) {
                $ob      = $DB->sql_fetch_object($res);
                $id_city = $ob->id;
                $this->displayResult("City found in database !", "OK");
            } else {
                $this->displayResult("City found in database !", "KO");
            }
        } while ($DB->sql_num_rows($res) != 1);




        //password
        $rl = new Hoa\Console\Readline\Password();

        $good = false;
        do {
            $pwd  = $rl->readLine('Password : ');
            $pwd2 = $rl->readLine('Password (repeat) : ');

            if (!empty($pwd) && $pwd === $pwd2) {
                $good = true;
                $this->displayResult("The passwords must be the same & not empty",
                    "OK");
            } else {
                $this->displayResult("The passwords must be the same & not empty",
                    "KO");
            }
        } while ($good !== true);


        $ip = trim(@file_get_contents("http://icanhazip.com"));

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = "127.0.0.1";
        }

        $data['user_main']['is_valid'] = 1;
        $data['user_main']['email']    = $email;
        $data['user_main']['login']    = $email;
        $data['user_main']['password'] = \Glial\Auth\Auth::hashPassword($email,
                $pwd);

        //to set uppercase to composed name like 'Jean-Louis'
        $firstname = str_replace("-", " - ", $firstname);
        $firstname = mb_convert_case($firstname, MB_CASE_TITLE, "UTF-8");

        $data['user_main']['firstname'] = str_replace(" - ", "-", $firstname);

        $data['user_main']['name']                       = mb_convert_case($lastname,
            MB_CASE_UPPER, "UTF-8");
        $data['user_main']['ip']                         = $ip;
        $data['user_main']['date_created']               = date('Y-m-d H:i:s');
        $data['user_main']['id_group']                   = 4; // 4 = super admin
        $data['user_main']['id_geolocalisation_country'] = $id_country;
        $data['user_main']['id_geolocalisation_city']    = $id_city;
        $data['user_main']['id_client']                  = 1;

        $id_user = $DB->sql_save($data);

        if ($id_user) {
            $this->displayResult("Admin account successfully created", "OK");
        } else {

            print_r($data);
            $error = $DB->sql_error();
            print_r($error);

            $this->displayResult("Admin account successfully created", "KO");

            goto createUser;
        }

        echo Color::getColoredString("\nAdministrator successfully created !\n",
            "green");

        $ip_list = shell_exec('ifconfig -a | grep "inet ad" | cut -d ":" -f 2 | cut -d " " -f 1');

        $ips = explode("\n", $ip_list);

        foreach ($ips as $ip) {
            if (empty($ip)) {
                continue;
            }

            echo "You can connect to the application on this url : ".Color::getColoredString("http://".$ip.WWW_ROOT,
                "yellow")."\n";
        }
    }

    public function createOrganisation()
    {
        $this->view = false;
        $DB  = $this->di['db']->sql(DB_DEFAULT);

        createOragnisation:
        $this->cadre("create oraganisation");

        do {
            $rl            = new Hoa\Console\Readline\Readline();
            $oraganisation = $rl->readLine('Your Oraganisation : ');
        } while (strlen($oraganisation) < 3);


        $sql = "INSERT INTO client (`id`,`libelle`,`date`) VALUES (1,'".$oraganisation."', '".date('Y-m-d H:i:s')."')";
        $DB->sql_query($sql);

        /*
        $data = [];
        $data['client']['id']      = 1;
        $data['client']['libelle'] = $oraganisation;
        $data['client']['date'] = date('Y-m-d H:i:s');

        $id_client = $DB->sql_save($data);


        print_r($id_client);

        if ($id_client) {
            $this->displayResult("Oraganisation successfully created", "OK");
        } else {

            print_r($data);
            $error = $DB->sql_error();
            print_r($error);

            $this->displayResult("Oraganisation successfully created", "KO");

            goto createOragnisation;
        }
         
         */
    }
}