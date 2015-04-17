<?php

use \Glial\Synapse\Controller;

class Fix extends Controller
{

    public function index()
    {
        $this->layout_name = false;
        $this->view = false;

        $db = $this->di['db']->sql("itprod_dba_test_sa_02");

        $sql = "SELECT * FROM PRODUCTION.PROD_POIDS_INDEX";


        $data = $db->sql_fetch_yield($sql);


        $idx = 0;
        foreach ($data as $line) {


            $sql = "SELECT ID FROM tmp.PROD_POIDS_INDEX ";

            $where = " WHERE ID_PROD_COMMANDE = " . $line['ID_PROD_COMMANDE'] . " 
                        AND ID_PROD_ENVELOPPE = " . $line['ID_PROD_ENVELOPPE'] . "
                        AND ID_PRODUIT=" . $line['ID_PRODUIT'] . "
                        and NOMBRE_INDEX =" . $line['NOMBRE_INDEX'] . "
                        and DATE_PASSAGE = '" . $line['DATE_PASSAGE'] . "' ";

            $res = $db->sql_query($sql);

            if ($db->sql_num_rows($res) > 0) {

                $ob = $db->sql_fetch_object($res);

                if ($line['ID'] == $ob->ID) {
                    echo "ID " . $ob->ID . " : OK !" . PHP_EOL;
                } else {
                    $sql = "UPDATE PRODUCTION.PROD_POIDS_INDEX SET ID=" . $line['ID'] . " ";
                    $sql .= $where . "";


                    echo $sql . PHP_EOL;
                    $db->sql_query($sql);
                }
            } elseif ($db->sql_num_rows($res) == 0) {
                $sql = "UPDATE PRODUCTION.PROD_POIDS_INDEX SET ID=$idx ";
                $sql .= $where;

                echo $sql . PHP_EOL;
                $db->sql_query($sql);

                $idx--;
            } else {


                throw new Exception("PBX-001 : C'est la muerda !");
            }
        }
    }

    public function update()
    {
       
        
        $this->layout_name = false;
        $this->view = false;

        $db = $this->di['db']->sql("itprod_dba_test_sa_02");

        $sql = "SELECT * FROM PRODUCTION.PROD_POIDS_INDEX WHERE valid=0";


        $data = $db->sql_fetch_yield($sql);

        $idx = 200000;
        foreach ($data as $line) {

            $where = " WHERE ID_PROD_COMMANDE = " . $line['ID_PROD_COMMANDE'] . " 
                        AND ID_PROD_ENVELOPPE = " . $line['ID_PROD_ENVELOPPE'] . "
                        AND ID_PRODUIT=" . $line['ID_PRODUIT'] . "
                        and NOMBRE_INDEX =" . $line['NOMBRE_INDEX'] . "
                        and DATE_PASSAGE = '" . $line['DATE_PASSAGE'] . "' ";


            $sql = "UPDATE PRODUCTION.PROD_POIDS_INDEX SET ID=$idx ";
            $sql .= $where;

            echo $sql . PHP_EOL;
            $db->sql_query($sql);
            $idx++;
        }
    }

}
