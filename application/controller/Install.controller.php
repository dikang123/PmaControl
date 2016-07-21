<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\Glial;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sql\FactorySql;
use \Glial\Security\Crypt\Crypt;

class Install extends Controller
{

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

        echo PHP_EOL."To understand what happen : ".Color::getColoredString("glial/tmp/log/error_php.log", "cyan").PHP_EOL;
        echo "To resume the setup : ".Color::getColoredString("php composer.phar update", "cyan").PHP_EOL;
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
        echo $this->out(Color::getColoredString("[".date("Y-m-d H:i:s")."] ", "purple").$msg, $fine);
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
        echo Color::getColoredString(SITE_NAME, "green")." version ".Color::getColoredString(SITE_VERSION, "yellow")." (".SITE_LAST_UPDATE.")\n";
        echo "Powered by Glial (https://github.com/Glial/Glial)\n";


        $this->generate_key();


        $this->cadre("Select MySQL server for PmaControl");
        $server = $this->testMysqlServer();


        usleep(1000);


        $this->installCrontab();
        $this->installLanguage();

        $this->updateConfig($server);

        $this->importData($server);

        $this->updateCache();

        $this->cmd("echo 1", "Testing system & configuration");

        echo Color::getColoredString("\nPmaControl 0.8-beta has been successfully installed !\n", "green");
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
                echo "MySQL server : ".$hostname.":".$port." -> ".Color::getColoredString("KO", "grey", "red")."\n";
                echo str_repeat("-", 80)."\n";
            } else {
                $this->cmd("echo 1", "MySQL server : ".$hostname.":".$port." available");

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
                echo Color::getColoredString('Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error(), "grey", "red")."\n";
                //echo "credential (".$user." // ".$password.")\n";
                echo str_repeat("-", 80)."\n";
            }

            sleep(1);
        } while ($good === false);


        //check TokuDB
        //

        $sql = "select count(1) as cpt from information_schema.engines where engine = 'TokuDB' and (SUPPORT = 'YES' OR SUPPORT = 'DEFAULT');";

        $res = mysqli_query($link, $sql);

        while ($ob = mysqli_fetch_object($res)) {

            if ($ob->cpt !== "1") {
                echo Color::getColoredString('Engine "TokuDB" is not installed yet', "grey", "red")."\n";


                echo "To install TokuDB :\n";
                echo "\t- Add : \"plugin-load-add=ha_tokudb.so\" in your my.cnf\n";
                echo "\t- Disable transparent_hugepage : \"echo never > /sys/kernel/mm/transparent_hugepage/enabled\" \n";
                echo "\t- Disable transparent_hugepage : \"echo never > /sys/kernel/mm/transparent_hugepage/defrag\" \n";
                echo "\t- Restart MySQL server\n";
                exit(2);
            }
        }

        /*
         *
         * On Redhat and Centos
         * Add line GRUB_CMDLINE_LINUX_DEFAULT="transparent_hugepage=never" to file /etc/default/grub

          Update grub (boot loader):

          grub2-mkconfig -o /boot/grub2/grub.cfg "$@"

          echo never > /sys/kernel/mm/transparent_hugepage/enabled
          echo never > /sys/kernel/mm/transparent_hugepage/defrag

         */



        //check Spider
        //

        $sql = "select count(1) as cpt from information_schema.engines where engine = 'SPIDER' and (SUPPORT = 'YES' OR SUPPORT = 'DEFAULT');";

        $res = mysqli_query($link, $sql);

        while ($ob = mysqli_fetch_object($res)) {

            if ($ob->cpt !== "1") {
                echo Color::getColoredString('Engine "SPIDER" is not installed yet', "grey", "red")."\n";

                echo "To install Spider, run the install_spider.sql script, located in the share directory, for example, from the command line:\n\n";
                echo "\tmysql -uroot -p < /usr/share/mysql/install_spider.sql\n";
                echo "\n";
                echo "or, from within mysql\n\n";
                echo "\tsource /usr/share/mysql/install_spider.sql\n\n";

                exit(2);
            }
        }



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
                echo Color::getColoredString('Database -> KO (this database already exist)', "grey", "red")."\n";
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


            $this->cmd("echo 1", 'The database "'.mysqli_real_escape_string($link, $database).'" has been created');
        } else {
            echo Color::getColoredString('The database "'.mysqli_real_escape_string($link, $database).'" couldn\'t be created', "black",
                "red")."\n";
            goto wrong_db;
            echo str_repeat("-", 80)."\n";
        }


        Crypt::$key = CRYPT_KEY;

        $passwd = Crypt::encrypt($password);




        $mysql['hostname'] = $hostname;
        $mysql['port']     = $port;
        $mysql['user']     = $user;
        $mysql['password'] = $passwd;
        $mysql['database'] = $database;
        return $mysql;
    }

    private function cadre($text, $elem = '#')
    {
        echo str_repeat($elem, 80)."\n";
        echo $elem.str_repeat(' ', ceil((80 - strlen($text) - 2) / 2)).$text.str_repeat(' ', floor((80 - strlen($text) - 2) / 2)).$elem."\n";
        echo str_repeat($elem, 80)."\n";
    }

    private function importData($server)
    {
        //$path = ROOT."/sql/*.sql";
        $path = ROOT."/sql/full/pmacontrol.sql";


        Crypt::$key         = CRYPT_KEY;
        $server['password'] = Crypt::decrypt($server['password']);

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
crypted='1'
database=".$server['database']."";

        $fp = fopen(CONFIG."/db.config.ini.php", 'w');
        fwrite($fp, $config);
        fclose($fp);

        $this->cmd("echo 1", "Generate config file for DB");
    }

    private function updateCache()
    {
        $this->cmd("php glial administration admin_index_unique", "Generating DDL cash for index");
        $this->cmd("php glial administration admin_table", "Generating DDL cash for databases");
        $this->cmd("php glial administration generate_model", "Making model with reverse engineering of databases");
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
                $this->displayResult("The passwords must be the same & not empty", "OK");
            } else {
                $this->displayResult("The passwords must be the same & not empty", "KO");
            }
        } while ($good !== true);


        $ip = trim(@file_get_contents("http://icanhazip.com"));

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = "127.0.0.1";
        }

        $data['user_main']['is_valid'] = 1;
        $data['user_main']['email']    = $email;
        $data['user_main']['login']    = $email;
        $data['user_main']['password'] = \Glial\Auth\Auth::hashPassword($email, $pwd);

        //to set uppercase to composed name like 'Jean-Louis'
        $firstname = str_replace("-", " - ", $firstname);
        $firstname = mb_convert_case($firstname, MB_CASE_TITLE, "UTF-8");

        $data['user_main']['firstname'] = str_replace(" - ", "-", $firstname);

        $data['user_main']['name']                       = mb_convert_case($lastname, MB_CASE_UPPER, "UTF-8");
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

        echo Color::getColoredString("\nAdministrator successfully created !\n", "green");

        $ip_list = shell_exec('ifconfig -a | grep "inet ad" | cut -d ":" -f 2 | cut -d " " -f 1');

        $ips = explode("\n", $ip_list);

        foreach ($ips as $ip) {
            if (empty($ip)) {
                continue;
            }

            echo "You can connect to the application on this url : ".Color::getColoredString("http://".$ip.WWW_ROOT, "yellow")."\n";
        }


        echo "You can connect to the application on this url : ".Color::getColoredString("http://".gethostname().WWW_ROOT, "yellow")."\n";
    }

    public function createOrganisation()
    {
        $this->view = false;
        $DB         = $this->di['db']->sql(DB_DEFAULT);

        createOragnisation:
        $this->cadre("create oraganisation");

        do {
            $rl            = new Hoa\Console\Readline\Readline();
            $oraganisation = $rl->readLine('Your Oraganisation : ');
        } while (strlen($oraganisation) < 3);


        $sql = "INSERT INTO client (`id`,`libelle`,`date`) VALUES (1,'".$oraganisation."', '".date('Y-m-d H:i:s')."')";
        $DB->sql_query($sql);
    }

    private function rand_char($length)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= chr(mt_rand(33, 126));
        }
        return $random;
    }

    private function generate_key()
    {

        $key = str_replace("'", "", $this->rand_char(256));


        $data = "<?php

if (! defined('CRYPT_KEY'))
{
    define('CRYPT_KEY', '".$key."');
}
";
        $path = "configuration/crypt.config.php";


        $msg = "Generate key for encryption";

        if (!file_exists($path)) {
            file_put_contents($path, $data);
            $this->displayResult($msg, "OK");
            require_once $path;
        } else {
            $this->displayResult($msg, "NA");
        }
    }


    private function installCrontab()
    {

        shell_exec("cat > /tmp/cron.txt << EOF
*      *       *       *       *       cd ".ROOT." && ./glial dot generateCache
EOF
");
        shell_exec("crontab /tmp/cron.txt");

    }


    public function installLanguage()
    {
        \Glial\I18n\I18n::install();
    }

}