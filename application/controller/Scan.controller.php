<?php

use \Glial\Synapse\Controller;
use Glial\Cli\Table;

/*
 * this class is made to scan network and discover MySQL Server
 * to parse result of nmap
 */

class Scan extends Controller
{

    public function parse($filename)
    {
        //35 & 68
        // nmap -p -sO 3306 10.0.51.1-255
        //nmap -p 3306 -sV 10.0.51.1-254

        $this->view = false;
        $filename   = $filename[0];

        if (!file_exists($filename)) {
            throw new \Exception("Impossible to read this file : '".$filename."'",
            80);
        }

        $table = new Table(0);

        $table->addHeader(array("Hostname", "IP", "Address MAC", "Os", "Version"));

        $array = file($filename);

        $i           = 0;
        $j           = 0;
        $mysql_found = array();

        foreach ($array as $line) {
            if (strpos($line, "open")) {
                $hostname = trim(explode(' ', $array[$i - 3])[4]);

                preg_match_all("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/",
                    $array[$i - 3], $output_array);

                $ip = $output_array[0][0];

                $version                     = trim(str_replace("3306/tcp open  mysql",
                        "", $line));
                $mysql_found[$j]['version']  = $version;
                $mysql_found[$j]['hostname'] = $hostname;
                $mysql_found[$j]['ip']       = $ip;

                $count = count($array);


                //to prevent parse of end of array in no mac address
                for ($k = $i; $k < $i + 30; $k++) {

                    if ($count <= $k) {
                        break;
                    }

                    if (strstr($array[$k], 'MAC Address')) {
                        $brut = trim(str_replace("MAC Address:", "", $array[$k]));

                        $out        = explode(' ', $brut);
                        $mac_adress = $out[0];
                        $os         = trim(str_replace(array('(', ')'), '',
                                substr($brut, 17)));
                        break;
                    }
                }

                $mysql_found[$j]['os']  = $os;
                $mysql_found[$j]['mac'] = $mac_adress;

                $table->addLine(array($mysql_found[$j]['hostname'], $mysql_found[$j]['ip'],
                    $mysql_found[$j]['mac'], $mysql_found[$j]['os'], $mysql_found[$j]['version']));

                //echo $ip."\n";
                $j++;
            }
            $i++;
        }

        //print_r($array);
        //debug($mysql_found);
        echo $table->display();
        return $mysql_found;
    }

    public function autoDiscovering()
    {
        $this->view = false;
        $res        = shell_exec("nmap -p 3306,22 -sV 10.0.51.1-254");
        $lines      = explode("\n", $res);
        $server     = [];

        //remove header and footer nmap
        unset($lines[0]);
        $nb = count($lines);
        unset($lines[$nb - 1]);
        unset($lines[$nb - 2]);

        $i       = 0;
        $servers = [];
        foreach ($lines as $line) {

            if (empty(trim($line))) {
                $i++;
                continue;
            }
            $servers[$i][] = $line;
        }

        foreach ($servers as $server) {

            if (strstr($server[0], 'Nmap scan report for ')) {
                
            } else {
                
                continue;
            }
        }


        print_r($server);
    }
}