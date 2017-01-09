<?php

use Glial\Synapse\Controller;

class Upr extends Controller
{
    public $module_group = "Other";


    function recolter_nombre_adherent()
    {
        $this->view = false;
        $val = file_get_contents("https://www.upr.fr/wp-content/plugins/uprcron/nb.php");


        return intval($val);
        
    }


    function sauvegarde_adherent()
    {
        $this->view = false;

        $db = $this->di['db']->sql('upr');


        $nouveau_nombre_adherent = $this->recolter_nombre_adherent();

        $sql ="SELECT * FROM `adherent` ORDER BY date DESC limit 1";
        $res  = $db->sql_query($sql);

        
        while ($ob = $db->sql_fetch_object($res))
        {
            if ($ob->nombre != $nouveau_nombre_adherent && !empty($nouveau_nombre_adherent))
            {
                $difference = $nouveau_nombre_adherent - $ob->nombre;

                $sql = "INSERT INTO `adherent` (`date`,`nombre`,`difference`) VALUES ('".date("Y-m-d H:i:s")."',".$nouveau_nombre_adherent.", ".$difference.")";
                $db->sql_query($sql);
            }

        }
        
    }
}