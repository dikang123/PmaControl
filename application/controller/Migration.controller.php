<?php

use \Glial\Synapse\Controller;
use Glial\Cli\Table;

/*
 * this class is made to scan network and discover MySQL Server
 * to parse result of nmap
 * SET GLOBAL wsrep_provider_options='pc.bootstrap=true';
 */

class Migration extends Controller
{

    public function bcp()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'autonomie'";


        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res))
        {
            echo 'bcp Drupal_ACS.dbo.'.$ob->table_name.' out '.$ob->table_name.'.txt -n -SDBSQL-DRUPAL-DEV\PRGCL_DEV -T -C -t"||" -r"++" -c'."\n";
        }
    }


    public function import()
    {
        $path ="/data/client/humanis/test/*.txt";

        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;


        $files = glob($path);

        foreach($files as $file)
        {
            //echo $file."\n";

            $table_name = pathinfo($file)['filename'];

            echo 'LOAD DATA INFILE "'.$file.'" INTO TABLE `autonomie_mig`.`'.$table_name.'` COLUMNS TERMINATED BY "||" LINES TERMINATED BY "++";'."\n";
            //echo $table_name ."\n";
            //exit;
        }


    }


}