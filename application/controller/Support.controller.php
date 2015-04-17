<?php

use \Glial\Synapse\Controller;

use \Glial\Form\Upload;

class Support extends Controller
{

    public function UpdateTriChrono()
    {

        $this->title = __("Update the sort of Chronopost");

        $this->ariane = " > " . __("Photobox") . " > " . $this->title;

        
        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            $upload = new Upload(TMP . 'UpdateTriChrono/');
            
            if ($upload->receive("file_csv"))
            {
                
            }
            else
            {
                debug($upload->getErrorMsg());
                
                
                throw new \Exception("GLI-067 : Impossible to upload");
            }
            
            $db = $this->di['db']->sql("iprod_test_sa_03");

            $sqls = array();
            $sqls[] = "CREATE TABLE IF NOT EXISTS `AFF_ZONES_CHRONO_TEST` (
 `ID_CHRONO` int(11) NOT NULL auto_increment,
 `CP_MIN` varchar(15) NOT NULL,
 `CP_MAX` varchar(15) NOT NULL,
 `ROUTE_1` varchar(32) NOT NULL,
 `ROUTE_2` varchar(32) NOT NULL,
 `ROUTE_3` varchar(32) NOT NULL,
 `ROUTE_4` varchar(32) NOT NULL,
 `ROUTE_5` varchar(32) NOT NULL,
 `ROUTE_6` varchar(32) NOT NULL,
 `PRODUIT_CHRONO` varchar(10) NOT NULL,
 `CODE_ISO` char(2) DEFAULT 'FR',
 PRIMARY KEY  (`ID_CHRONO`)
) ENGINE=InnoDB AUTO_INCREMENT=60443 DEFAULT CHARSET=utf8";

            $sqls[] = "DELETE FROM `AFF_ZONES_CHRONO_TEST`";
            
            foreach($sqls as $sql)
            {
                $db->sql_query($sql);   
            }

            $productToSelect = array('12H', 'SA12', 'DPD');

//$handle = fopen("ROUCHR_20140707_B3_avecDPD.csv", "r");
            
            
            $handle = fopen(TMP."UpdateTriChrono/".$_FILES['file_csv']['name'], "r");

            while ($data = fgetcsv($handle, 4096, ";")) {
                $num = count($data);
                if ($num < 10) { // Trop petite ligne
                    continue;
                }
                if ((!in_array($data[0], $productToSelect))) { // On ne garde que le produit 12H pour la france et DPD pour l'international
                    continue;
                }
                $code_iso = $data[1];
                $minCP = $data[2];
                $maxCP = $data[3];
                $routage1 = $data[4];
                $routage2 = $data[5];
                $routage3 = $data[6];
                $routage4 = $data[7];
                $routage5 = $data[8];
                $routage6 = $data[9];
                $produit_chrono = $data[0];

                $query = "INSERT INTO AFF_ZONES_CHRONO_TEST (ID_CHRONO, CP_MIN , CP_MAX , ROUTE_1 , ROUTE_2 , ROUTE_3 , ROUTE_4 , ROUTE_5 , ROUTE_6,PRODUIT_CHRONO,CODE_ISO) ";
                $query .= "VALUES (NULL, '$minCP', '$maxCP', '$routage1', '$routage2', '$routage3', '$routage4', '$routage5', '$routage6', '$produit_chrono','$code_iso')";
        
                
                $db->sql_query($query);   
                
            }
            fclose($handle);
   
        }
    }

}
