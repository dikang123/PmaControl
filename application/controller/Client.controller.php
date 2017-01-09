<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \Glial\I18n\I18n;

use \Glial\Synapse\Controller;

class Client extends Controller
{

    public function index()
    {
        $db             = $this->di['db']->sql(DB_DEFAULT);
        $sql            = "SELECT * FROM client order by Libelle";
        $data['client'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['client']['libelle'])) {
                $db = $this->di['db']->sql(DB_DEFAULT);


                $client                      = [];
                $client['client']['libelle'] = $_POST['client']['libelle'];
                $client['client']['date']    = date('c');


                $res = $db->sql_save($client);

                if (!$res) {
                    debug($client);
                    debug($db->sql_error());
                    die();

                    $msg   = I18n::getTranslation(__("Impossible to find the daemon with the id : ")."'".$id_daemon."'");
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);
                    header("location: ".LINK."client/add");

                    exit;
                } else {
                    $msg   = I18n::getTranslation(__("Client add"));
                    $title = I18n::getTranslation(__("Success"));
                    set_flash("success", $title, $msg);
                    header("location: ".LINK.'client/index');
                }
            }
        }
    }
}
