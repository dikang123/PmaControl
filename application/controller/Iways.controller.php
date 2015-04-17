<?php

use \Glial\Synapse\Controller;

class Iways extends Controller
{

    function syncroCluster()
    {
        $this->title = __("Check cluster synchronisation");
        $this->ariane = " > " . "Iways" . " > " . $this->title;


        $this->layout_name = 'pmacontrol';


        $servers = array("iways-db-node-au-01", "iways-db-node-au-02", "iways-db-node-au-03");


        $i = 1;
        foreach ($servers as $server) {
            $db = $this->di['db']->sql(str_replace('-', '_', $server));
            $sql = "select orders.STATUS, count(*) as cpt from iways_core.IWAYS_ORDER orders where orders.LAST_UPDATE_DATE > NOW() - INTERVAL 1 DAY group by 1 order by 1 asc";
            $res = $db->sql_query($sql);
            while ($ob = $db->sql_fetch_object($res)) {

                $data['tab'][$ob->STATUS][$i] = $ob->cpt;
            }


            $db->sql_close();
            $i++;
        }



        $this->set('data', $data);
    }

}
