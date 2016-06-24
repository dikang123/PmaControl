<?php

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;

class Mysqlsys extends Controller
{

    public function index()
    {

        $this->title  = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> '."MySQL-sys";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '.$this->title;

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['mysql_server']['id'])) {

                $sql = "SELECT * FROM mysql_server where id='".$_POST['mysql_server']['id']."'";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    $id_mysql_server = $ob->id;
                    $url             = LINK.strtolower(__CLASS__).'/index/mysql_server:id:'.$id_mysql_server;
                    header('location: '.$url);
                }
            }
        } else {

            $data = [];

            // get server available
            $sql             = "SELECT * FROM mysql_server a WHERE error = '' ".$this->getFilter()." order by a.name ASC";
            $res             = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob              = $db->sql_fetch_object($res)) {
                $tmp               = [];
                $tmp['id']         = $ob->id;
                $tmp['libelle']    = $ob->name." (".$ob->ip.")";
                $data['servers'][] = $tmp;

                if (!empty($_GET['mysql_server']['id']) && $ob->id == $_GET['mysql_server']['id']) {
                    $link_name = $ob->name;
                }
            }

            if (!empty($link_name)) {

                $remote                 = $this->di['db']->sql($link_name);
                $sql                    = "select table_name from information_schema.tables WHERE table_schema = 'sys' and table_name not like 'x$%';";
                $res                    = $remote->sql_query($sql);
                $data['view_available'] = [];
                while ($ob                     = $remote->sql_fetch_object($res)) {
                    $data['view_available'][] = $ob->table_name;
                }


                //test if InnoDB activated
                $sql = "select * from information_schema.engines where engine = 'InnoDB';";
                $res = $remote->sql_query($sql);

                $data['innodb'] = 0;
                while ($ob             = $remote->sql_fetch_object($res)) {
                    if ($ob->SUPPORT == "YES" || $ob->SUPPORT == "DEFAULT") {
                        $data['innodb'] = 1;
                    }
                }


                if (!empty($_GET['mysqlsys']) && in_array($_GET['mysqlsys'],
                        $data['view_available'])) {
                    $sql           = "SELECT * FROM `sys`.`".$_GET['mysqlsys']."`";
                    $data['table'] = $remote->sql_fetch_yield($sql);
                }

                $data['variables'] = $remote->getVersion();
            }
        }
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
            $where .= " AND a.id_environment IN (".implode(',',
                    json_decode($environment, true)).")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (".implode(',',
                    json_decode($client, true)).")";
        }

        return $where;
    }

    public function install()
    {
        $this->title  = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> '."MySQL-sys";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '.$this->title.' > <i style="font-size: 16px" class="fa fa-upload"></i> Install';

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id='".$_GET['mysql_server']['id']."'";
        $res = $db->sql_query($sql);

        $data = [];
        while ($ob   = $db->sql_fetch_object($res)) {
            $cmd = 'cd '.ROOT.'/vendor/esysteme/mysql-sys ';
            $cmd .= '&& ./generate_sql_file.sh -v 100 -u "\''.$ob->login.'\'@\'localhost\'" 2>&1';
            $ret = shell_exec($cmd);

            $out               = explode("\n", $ret)[1];
            $data['file_name'] = trim(str_replace('Wrote file:', '', $out));

            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                Crypt::$key = CRYPT_KEY;

                $cmd = "mysql -h ".$ob->ip." -u ".$ob->login." -p".Crypt::decrypt($ob->passwd)." < ".$data['file_name']." 2>&1";
                $ret = shell_exec($cmd);

                if (!empty($ret)) {

                    header('location: '.LINK.'mysqlsys/install/mysql_server:id:'.$_GET['mysql_server']['id'].'/error_msg:'.base64_encode($ret).'/');
                } else {
                    header('location: '.LINK.'mysqlsys/index/mysql_server:id:'.$_GET['mysql_server']['id']);
                }
            } else {
                $data['file'] = file_get_contents($data['file_name']);
            }
        }

        $this->set('data', $data);
    }
}