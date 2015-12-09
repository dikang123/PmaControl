<?php

use \Glial\Synapse\Controller;
use Glial\Cli\Table;

/*
 * this class is made to scan network and discover MySQL Server
 * to parse result of nmap
 */

class Scan extends Controller
{
    public $data = array();

    var $port = array(22 => "SSH",3306 => "MySQL", 3307 => "MySQL load-balanced", 33306 => "MySQL load-balanced",
        9600 => "HAproxy stats", 4006=>"Round robin listener",4008=> "R/W split listener", 4442=> "Debug information",
        4567 => "Galera", 4444 => "SST Gaelera", 4568 => "IST Galera", 6033 => "MaxAdmin CLI");


    var $other = array(21 => "ftp", 23 => "telnet", 25 => "smtp", 80 => "http", 389 => "ldap",
        443 => "https", 445 => "microsfot-ds", 465=> "smtps", 2019 => "nfs");


    public function parse($input)
    {
        //35 & 68
        // nmap -p -sO 3306 10.0.51.1-255
        //nmap -p 3306 -sV 10.0.51.1-254
        // netstat -paunt

        $this->view = false;

        $data = [];
        foreach ($input as $server) {

            $tmp   = [];
            $array = explode("\n", $server);

            $output_array = [];
            preg_match_all("/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/", $array[0], $output_array);

            if (empty($output_array[0][0])) {
                throw new \Exception("PMACTRL-065 : Impossible to find the IP", 80);
            }

            $tmp['ip']       = $output_array[0][0];
            $tmp['hostname'] = trim(str_replace(array($tmp['ip'], "()"), array("", ""), $array[0]));

            if (empty($tmp['hostname'])) {
                $tmp['hostname'] = $tmp['ip'];
            }

            unset($array[0]);
            foreach ($array as $line) {

                if (preg_match("#[0-9]+/tcp.*#", $line)) {
                    $port = explode("/", $line)[0];
                    $line = trim(str_replace($port."/tcp", '', $line));  // upgrade with other protocol ?

                    $status = trim(explode(' ', $line)[0]);
                    $line   = trim(str_replace($status, '', $line));

                    $name = trim(explode(' ', $line)[0]);
                    $line = trim(str_replace($name, '', $line));

                    $version = trim($line);

                    $tmp['port'][$port]['status']  = $status;
                    $tmp['port'][$port]['name']    = $name;
                    $tmp['port'][$port]['version'] = trim(explode("\t", $line)[0]);
                }

                if (strstr($line, 'MAC Address: ')) {
                    $line       = trim(str_replace("MAC Address:", '', $line));
                    $tmp['MAC'] = explode(' ', $line)[0];
                }
            }

            $data[] = $tmp;
        }

        return $data;
    }

    public function autoDiscovering($param)
    {
        // arp -a -n => fastest way

        $this->view = false;

        $range = $param[0];
        $port  = $param[1];

        //$res = shell_exec("nmap ".$range);
        $res = shell_exec("nmap -F 10.0.51.0/24");

        $data = $this->extract($res);
        $json = $this->parse($data);

        $this->data = $json;
    }

    public function extract($data)
    {
        /*
         * remove these line when version of MySQL not know or cannot be mapped by nmap
         *
         * SF:0is\x20not\x20allowed\x20to\x20connect\x20to\x20this\x20MariaDB\x20serv
         * SF:er")%r(SMBProgNeg,4A,"F\0\0\0\xffj\x04Host\x20'10\.0\.51\.117'\x20is\x2
         */

        $res  = preg_replace("/^SF.*$/m", "", $data);
        $res2 = preg_replace("/^1 service unrecognized.*$/m", "", $res);
        $res3 = preg_replace("/\n+/", "\n", $res2);

        //split by server
        $lines = explode("Nmap scan report for", $res3);

        //remove header and footer
        unset($lines[count($lines) - 1]);
        unset($lines[0]);

        return $lines;
    }

    public function test2()
    {
        $xml  = new SimpleXMLElement(file_get_contents("/data/www/pmacontrol/ff.xml"));
        $json = json_encode($xml);
        $data = json_decode($json, true);
        debug($data);
    }

    public function index()
    {

        $this->title = '<span class="glyphicon glyphicon-search" aria-hidden="true"></span> '.__("Scan network");
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '.$this->title;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("Scan/index.js"));

        $sql ="SELECT `ip` from mysql_server";
        $res = $db->sql_query($sql);

        $data['ip'] = array();
        while ($ob = $db->sql_fetch_object($res))
        {
            $data['ip'][] = $ob->ip;
        }


        $data['scan'] = $this->getData();



        $this->set('data', $data);
    }

    public function insert($data)
    {
        
    }

    public function __sleep()
    {
        return array('data');
    }

    public function getData()
    {
        $path_to_acl_tmp = TMP."data/scan.ser";

        if (file_exists($path_to_acl_tmp)) {

            //unlink($path_to_acl_tmp);

            if (is_file($path_to_acl_tmp)) {
                $s          = file_get_contents($path_to_acl_tmp);
                $tmp        = unserialize($s);
                $this->data = $tmp->data;
                return $this->data;
            }
        }

        $data = $this->autoDiscovering(array("10.0.51.*", "T:22,T:3306,T:4567"));

        file_put_contents($path_to_acl_tmp, serialize($this));

        return $data;
    }
}