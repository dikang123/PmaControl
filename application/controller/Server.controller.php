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
        $this->view = false;

        $tab = new Table(1);

        $tab->addHeader(array("Top", "Server", "IP", "Os", "CPU", "Frequency", "Memory"));
        $i = 1;


        $list_server = array();

        
        Crypt::$key = CRYPT_KEY;
        
        $password = Crypt::decrypt(PMACONTROL_PASSWD);

        foreach ($this->di['db']->getAll() as $server) {

            $info_server = $this->di['db']->getParam($server);

            if (in_array($info_server['hostname'], $list_server)) {
                continue;
            }
            $list_server[] = $info_server['hostname'];

            $ssh = new Ssh($info_server['hostname'], 22, "pmacontrol", $password);
            //$ssh = new Ssh($info_server['hostname'], 22, "root", '/root/.ssh/id_rsa.pub', '/root/.ssh/id_rsa');

            if (!$ssh) {


                echo "Impossible to connect on ".$info_server['hostname']."\n";
                continue;
            }
            $nb_cpu = $ssh->exec("cat /proc/cpuinfo | grep processor | wc -l");

            $brut_memory = $ssh->exec("cat /proc/meminfo | grep MemTotal");
            preg_match("/[0-9]+/", $brut_memory, $memory);

            $mem = $memory[0];
            $memory = sprintf('%.2f', $memory[0] / 1024 / 1024) . " Go";

            $freq_brut = $ssh->exec("cat /proc/cpuinfo | grep 'cpu MHz'");
            preg_match("/[0-9]+\.[0-9]+/", $freq_brut, $freq);
            $frequency = sprintf('%.2f', ($freq[0] / 1000)) . " GHz";


            $os = trim($ssh->exec("lsb_release -ds"));

            if (empty($os))
            {
                $os = trim($ssh->exec("cat /etc/centos-release"));
            }
            
            $product_name = $ssh->exec("dmidecode -s system-product-name");
            $arch = $ssh->exec("uname -m");
            $kernel = $ssh->exec("uname -r");
            $hostname = $ssh->exec("hostname");
            
            
            $tab->addLine(array($i, $server, $info_server['hostname'], $os, trim($nb_cpu), $frequency, $memory));

            $db = $this->di['db']->sql(DB_DEFAULT);


            if (!empty($os)) {
                $sql = "UPDATE mysql_server SET operating_system='" . $db->sql_real_escape_string($os) . "',
                   processor='" . trim($nb_cpu) . "',
                   cpu_mhz='" . trim($freq[0]) . "',
                   product_name='" . trim($product_name) . "',
                   arch='" . trim($arch) . "',
                   kernel='" . trim($kernel) . "',
                   hostname='" . trim($hostname) . "',
                   memory_kb='" . trim($mem) . "' WHERE `name` = '" . $db->sql_real_escape_string($server) . "'";

                $db->sql_query($sql);
            }

            unset($ssh);

            $i++;
            //$ssh->disconnect();
        }

        echo $tab->display();

        //debug($tab);
    }

    public function before($param)
    {
        $this->layout_name = 'pmacontrol';
    }

    public function listing()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->title = __("Dashboard");
        $this->ariane = " > " .  $this->title;

        $sql = "SELECT a.*, b.version, b.is_available FROM mysql_server a "
                . " LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server"
                . " order by `name`;";

        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }
}
