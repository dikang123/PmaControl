<?php

use \Glial\Synapse\Controller;

/*
 * this class is made to scan network and discover MySQL Server
 * to parse result of nmap
 */

class Scan extends Controller {

    public function parse($filename) {

        $this->view = false;
        
        $filename = $filename[0];
        
        
        
        if (!file_exists($filename)) {
            throw new \Exception("Impossible to read this file : '" . $filename . "'", 80);
        }

        
        $reg = '^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$';
        
        
        $array = file($filename);
        
        $i = 0;
        $mysql_found = array();
        
        foreach($array as $line)
        {
            if (strpos($line, "open" ))
            {
                $ip = trim(explode(' ',$array[$i-3])[4]);
                
                $version = trim(str_replace("3306/tcp open  mysql", "",$line));
                
                $mysql_found[$ip] = $version;
                
                echo $ip."\n";
            }
            
            
            $i++;
        }
        
        debug($mysql_found);
        
        
/*
        $handle = fopen($filename, "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
            }
            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }*/
    }

}
