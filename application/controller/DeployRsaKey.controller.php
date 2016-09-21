<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class DeployRsaKey extends Controller
{

    public function index()
    {
        $this->title  = '<i style="font-size: 32px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '
            .'<i style="font-size: 16px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['settings'])) {

                debug($_POST);

                $list_id = [];
                foreach ($_POST['id'] as $key => $value) {

                    if (!empty($_POST['mysql_server'][$key]["is_monitored"]) && $_POST['mysql_server'][$key]["is_monitored"] === "on")
                    {
                        $list_id[] = $value;
                    }
                }


                $ids = implode(",",$list_id);

                $sql = "SELECT * FROM mysql_server WHERE id IN (".$ids.")";
                $res = $db->sql_query($sql);


                shell_exec("mkdir ".DATA."keys/");

                while ($ob = $db->sql_fetch_object($res))
                {
                    $cmd ='ssh-keygen -t rsa -N "" -b 4096 -C "PmaControl@esysteme.com" -f '.DATA."/keys/".$ob->ip.'.key';
                    shell_exec($cmd);
                    
                }

                debug($list_id);

                //header("location: ".LINK."DeployRsaKey/index/");
                //exit;
            }
        }



        $this->title  = '<i class="fa fa-server"></i> '.__("Servers");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-cog" style="font-size:14px"></i> '
            .__("Settings").'</a> > <i class="fa fa-server"  style="font-size:14px"></i> '.__("Servers");



        $sql             = "SELECT * FROM mysql_server a WHERE 1=1 ".$this->getFilter()." ORDER by name";
        $data['servers'] = $db->sql_fetch_yield($sql);





        $this->set('data', $data);
    }

    //to mutualize
    private function getFilter()
    {

        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (".implode(',', json_decode($client, true)).")";
        }


        return $where;
    }


    private function deploy($ip, $login, $password, $path_public_key)
    {


        
    }
}