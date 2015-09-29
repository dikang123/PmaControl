<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Html\Form\Upload;

class Reporting extends Controller
{

    public function after($param)
    {
        if (!IS_CLI) {
            $this->layout_name = 'pmacontrol';
            
        }
    }

    public function detail_order()
    {
        $this->title = __("Details order");
        $this->ariane = " > " . __("Reporting") . " > " . $this->title;

        $this->di['js']->addJavascript(array("jquery-latest.min.js"));

        $default = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (!empty($_POST['PROD_TRACES']['ID_COMMANDE'])) {

                $_POST['PROD_TRACES']['ID_COMMANDE'] = trim($_POST['PROD_TRACES']['ID_COMMANDE']);

                $full = array();
                $tab1 = explode("\n", $_POST['PROD_TRACES']['ID_COMMANDE']);

                foreach ($tab1 as $tab) {
                    $tab2 = explode(",", trim($tab));
                    $tab3 = explode(";", trim($tab));
                    
                    
                    $full = array_merge($tab2, $full, $tab3);
                }

                $id_in_error = array();
                foreach ($full as $elem) {
                    if (!is_numeric($elem)) {
                        $id_in_error[] = $elem;
                    }
                }

                if (count($id_in_error) > 0) {
                    set_flash("error", __("Error"), __("These ID are not valid :") . " " . "<br />" . implode(",", $id_in_error));
                } else {
                    set_flash("success", __("Command found !"), __("Result in the table"));
                }

                header("location: " . LINK . __CLASS__ . "/" . __FUNCTION__ . "/PROD_TRACES:ID_COMMANDE:" . implode(",", $full) . "/none:server:" . $_POST['none']['server']);

                //die("location: " . LINK . __CLASS__ . "/".__FUNCTION__."/PROD_TRACES:ID_COMMANDE:" . implode(",", $full) . "/none:server:" . $_POST['none']['server']);
                exit;
            }
        }




        if (!empty($_GET['none']['server']) && !empty($_GET['PROD_TRACES']['ID_COMMANDE'])) {

            $id_comandes = explode(',', $_GET['PROD_TRACES']['ID_COMMANDE']);



            foreach ($id_comandes as $id_commande) {
                if (!is_numeric($id_commande)) {

                    goto out;
                }
            }

            $ID_COMMANDES = implode(",", $id_comandes);

            $sql = "
(SELECT ID_COMMANDE, DATE_PASSAGE, LIGNE_PROD , COMMENTAIRES 
FROM PRODUCTION.PROD_TRACES b
WHERE b.ID_MODULE=151
AND b.ID_COMMANDE IN ($ID_COMMANDES))
UNION (
SELECT ID_COMMANDE, DATE_PASSAGE, LIGNE_PROD, COMMENTAIRES
FROM PRODUCTION.PROD_TRACES a
WHERE a.ID_MODULE=20
AND a.ID_COMMANDE IN ($ID_COMMANDES))
order by ID_COMMANDE,DATE_PASSAGE";


            $dblink = $this->di['db']->sql($_GET['none']['server']);

            $data['commandes'] = $dblink->sql_fetch_yield($sql);
        }


        out:


        $sql = "SELECT name FROM mysql_server order by name";
        $data['server'] = $default->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    private function convert($size)
    {
        $unit = array('o', 'Ko', 'Mb', 'Go', 'To', 'Po');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    public function getIdProdItem()
    {
        $this->title = __("Get ID_PROD_ITEM");
        $this->ariane = " > " . __("Reporting") . " > " . $this->title;




        if ($_SERVER['REQUEST_METHOD'] == "POST") {



            if (!empty($_POST['PROD_TRACES']['IPROD_INSTANCE_ID']) && !empty($_POST['PROD_TRACES']['IPROD_ITEM_ID'])) {
                header("location: " . LINK . __CLASS__ . "/" . __FUNCTION__
                        . "/PROD_TRACES:IPROD_INSTANCE_ID:" . $_POST['PROD_TRACES']['IPROD_INSTANCE_ID']
                        . "/PROD_TRACES:IPROD_ITEM_ID:" . $_POST['PROD_TRACES']['IPROD_ITEM_ID']);
            }

            if (!empty($_POST['excelidprod'])) {


                $tab = str_replace(";",",",str_replace("\t", ",", str_replace("\n", "|", str_replace("\r\n", '|', $_POST['excelidprod']))));
                $nbline = explode("|", $tab);
                array_splice($nbline, 200);
                $url = implode("|", $nbline);

                header("location: " . LINK . __CLASS__ . "/" . __FUNCTION__
                        . "/excelidprod:" . $url);
            }

            if (!empty($_FILES['filecsv'])) {

                $db = $this->di['db']->sql('bi_stage_sa');
                $db->sql_query("set global max_allowed_packet=356515840");

                $path = '/tmp';

                $up = new Upload($path);
                //$date = "get_id_prod_item_" . date("Y-m-d_his");

                if (!$up->receive('filecsv')) {
                    $error = $up->getErrorMsg();

                    debug($error);
                } else {

                    header("Content-type: text/csv");
                    header("Content-Disposition: attachment; filename=result.csv");
                    header("Pragma: no-cache");
                    header("Expires: 0");

                    $tab = $this->get200Lines($path . "/" . $_FILES['filecsv']['name']);

                    echo "ID_PROD_ITEM,IPROD_ITEM_ID,IPROD_INSTANCE_ID\n";

                    foreach ($tab as $line200) {

                        $sqls = [];



                        foreach ($line200 as $elem) {


                            $sqls[] = "select ID_PROD_ITEM,COMMENTAIRES from PROD_TRACES WHERE COMMENTAIRES ='insertion depuis Iways, iprodItemid=" . $elem['IPROD_ITEM_ID']
                                    . ", iprodIntsanceId=" . $elem['IPROD_INSTANCE_ID'] . "'";
                        }

                        $sql = "(" . implode(") UNION (", $sqls) . ");";
                        
                        
                        $data2 = $db->sql_fetch_yield($sql);
                        
                        

                        $this->view = false;
                        $this->layout_name = false;


                        foreach ($data2 as $elem) {

                            preg_match_all("/insertion depuis Iways, iprodItemid=([0-9]+), iprodIntsanceId=([0-9]+)/", $elem['COMMENTAIRES'], $output_array);

                            $line = $elem['ID_PROD_ITEM'] . "," . $output_array[1][0] . "," . $output_array[2][0];

                            echo $line . "\n";
                        }
                        
                        //echo $this->convert(memory_get_usage(true))."\n"; // 123 kb


                        /*
                          $file_name = $path . "/" . "result_" . $_FILES['filecsv']['name'];
                          unlink($file_name);
                          $fp = fopen($file_name, 'a');
                          if ($fp) {
                          foreach ($data2 as $elem) {

                          preg_match_all("/insertion depuis Iways, iprodItemid=([0-9]+), iprodIntsanceId=([0-9]+)/", $elem['COMMENTAIRES'], $output_array);

                          $line = $elem['ID_PROD_ITEM'] . "," . $output_array[1][0] . "," . $output_array[2][0];
                          fwrite($fp, $line."\n");
                          }
                          fclose($fp);
                          } */
                    }


                    exit;
                }
            }
        }

        if (!empty($_GET['PROD_TRACES']['IPROD_INSTANCE_ID']) && !empty($_GET['PROD_TRACES']['IPROD_ITEM_ID'])) {


            //debug($_GET);
            $db = $this->di['db']->sql('bi_stage_sa');

            //$sql = "select ID_PROD_ITEM from PROD_TRACES WHERE MATCH(COMMENTAIRES) AGAINST ('".$_GET['PROD_TRACES']['IPROD_INSTANCE_ID']."', '".$_GET['PROD_TRACES']['IPROD_ITEM_ID']."');";
            $sql = "select ID_PROD_ITEM from PROD_TRACES WHERE COMMENTAIRES ='insertion depuis Iways, iprodItemid=" . $_GET['PROD_TRACES']['IPROD_ITEM_ID'] . ", iprodIntsanceId=" . $_GET['PROD_TRACES']['IPROD_INSTANCE_ID'] . "';";

            $data['id_prod_item'] = $db->sql_fetch_yield($sql);

            $this->set('data', $data);
        }


        if (!empty($_GET['excelidprod'])) {
            //debug($_GET);
            $db = $this->di['db']->sql('bi_stage_sa');


            $_GET['excelidprod'] = trim($_GET['excelidprod']);

            $lines = explode("|", $_GET['excelidprod']);


            $sqls = [];
            foreach ($lines as $line) {

                $line = trim($line);
                $elems = explode(',', $line);


                if (!empty($line)) {
                    $sqls[] = "select ID_PROD_ITEM,COMMENTAIRES from PROD_TRACES WHERE COMMENTAIRES ='insertion depuis Iways, iprodItemid=" . $elems[0] . ", iprodIntsanceId=" . $elems[1] . "'";
                }
            }

            $sql = "(" . implode(") UNION (", $sqls) . ");";
            $db->sql_query("set global max_allowed_packet=356515840");

            $data['excelidprod'] = $db->sql_fetch_yield($sql);

            $this->set('data', $data);
        }
    }

    private function get200Lines($file)
    {

        $handle = fopen($file, "r");


        if ($handle) {

            $data = [];
            $i = 1;
            while (($buffer = fgets($handle, 4096)) !== false) {

                $buffer = str_replace("\t", ",", $buffer);


                $res = explode(',', $buffer);


                if (count($res) != 2) {
                    throw new Exception("Error : line invalide in file : '" . $buffer . "'");
                }

                $tmp = [];
                $tmp['IPROD_ITEM_ID'] = trim($res[0]);
                $tmp['IPROD_INSTANCE_ID'] = trim($res[1]);

                $data[] = $tmp;

                if ($i % 200 == 0) {
                    yield $data;

                    $data = [];
                }

                //echo $buffer;
                $i++;
            }

            yield $data;

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }

            fclose($handle);
        }
    }

}
